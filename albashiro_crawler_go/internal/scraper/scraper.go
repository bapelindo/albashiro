package scraper

import (
	"albashiro_crawler/internal/config"
	"albashiro_crawler/internal/filters"
	"albashiro_crawler/internal/monitor"
	"albashiro_crawler/internal/ollama"
	"albashiro_crawler/internal/proxy"
	"context"
	"crypto/tls"
	"encoding/json"
	"fmt"
	"math/rand"
	"net/http"
	"net/url"
	"os"
	"path/filepath"
	"regexp"
	"strings"
	"sync"
	"sync/atomic"
	"time"

	"github.com/PuerkitoBio/goquery"
)

type Scraper struct {
	cfg          *config.Config
	proxyPool    *proxy.Pool
	ollamaClient *ollama.Client
	httpClient   *http.Client
	monitor      *monitor.Monitor

	// Statistics
	articlesProcessed   int64
	embeddingsGenerated int64
	backupFile          string
	lastSaveTime        time.Time
	startTime           time.Time
	latestTitles        []string
	titlesMu            sync.Mutex

	// Channels
	jobChan    chan string
	resultChan chan *Article

	// AI Filtering
	topicEmbedding []float64
}

// Article structure optimized for RAG context
type Article struct {
	ID             int          `json:"id"`
	URL            string       `json:"url"`
	OriginalTitle  string       `json:"original_title"`
	ProcessedTitle string       `json:"processed_title"`
	Content        string       `json:"content"`
	Summary        string       `json:"summary"` // AI-generated summary for preview
	ContentLength  int          `json:"content_length"`
	ChunksTotal    int          `json:"chunks_total"`    // Total chunks processed
	ChunksRelevant int          `json:"chunks_relevant"` // Relevant chunks (passed AI judge)
	Vectors        []VectorData `json:"vectors"`         // Embeddings for RAG retrieval
	Timestamp      string       `json:"timestamp"`
}

type VectorData struct {
	ChunkIndex int       `json:"chunk_index"` // Track chunk position
	ChunkText  string    `json:"chunk_text"`
	Embedding  []float64 `json:"embedding"`
}

type Stats struct {
	ArticlesProcessed   int
	EmbeddingsGenerated int
	BackupFile          string
}

func New(cfg *config.Config, proxyPool *proxy.Pool) *Scraper {
	return &Scraper{
		cfg:          cfg,
		proxyPool:    proxyPool,
		ollamaClient: ollama.NewClient(cfg.OllamaURL, cfg.OllamaModel, cfg.OllamaEmbeddingModel),
		httpClient: &http.Client{
			Timeout: 60 * time.Second, // Increased to 30s for slow university sites
		},
		monitor:      monitor.New(cfg.MonitorDir, cfg.MaxArticles, 11.0),
		lastSaveTime: time.Now(),
		startTime:    time.Now(),
		latestTitles: make([]string, 0, 5),
		jobChan:      make(chan string, 1000),
		resultChan:   make(chan *Article, 100),
	}
}

func (s *Scraper) Start(ctx context.Context, wg *sync.WaitGroup) error {
	// Test Ollama connection
	if err := s.ollamaClient.Ping(); err != nil {
		return fmt.Errorf("ollama not available: %w", err)
	}

	// Generate Topic Embedding for semantic link filtering
	// Added Indonesian keywords + English keywords for better cross-lingual matching (Strict Clinical Focus)
	fmt.Print("   🧠 Generating topic embedding (Strict Clinical / Kesehatan Mental)...")
	topicVec, err := s.ollamaClient.GenerateEmbedding(ctx, "depression anxiety bipolar schizophrenia mental health disorder therapy counseling psychologist psychiatrist psychology research journal study gangguan jiwa depresi kecemasan psikolog psikiater pemulihan batin jurnal penelitian riset psikologi")
	if err != nil {
		return fmt.Errorf("failed to generate topic embedding: %w", err)
	}
	s.topicEmbedding = topicVec
	fmt.Println(" Done!")

	fmt.Println("   ✅ Ollama connected (AI judge + embeddings enabled)")

	// Create output directories
	os.MkdirAll(s.cfg.BackupDir, 0755)
	os.MkdirAll(s.cfg.StreamDir, 0755)

	// Start workers
	for i := 0; i < s.cfg.WorkerCount; i++ {
		wg.Add(1)
		go s.worker(ctx, wg)
	}

	// Start result collector
	wg.Add(1)
	go s.resultCollector(ctx, wg)

	// Feed jobs (seed URLs)
	go func() {
		for _, seed := range s.cfg.Seeds {
			select {
			case <-ctx.Done():
				return
			default:
				// Crawl seed page to find article links
				s.crawlSeedPage(ctx, seed)
			}
		}
		close(s.jobChan)
	}()

	return nil
}

func (s *Scraper) worker(ctx context.Context, wg *sync.WaitGroup) {
	defer wg.Done()

	for {
		select {
		case <-ctx.Done():
			return
		case targetURL, ok := <-s.jobChan:
			if !ok {
				return
			}
			s.processURL(ctx, targetURL)

			// 1 second delay REMOVED for maximum throughput
			// time.Sleep(1000 * time.Millisecond)
		}
	}
}

func (s *Scraper) processURL(ctx context.Context, targetURL string) {
	// Check if we've reached the limit
	if atomic.LoadInt64(&s.articlesProcessed) >= int64(s.cfg.MaxArticles) {
		return
	}

	// DEBUG: Log processing
	fmt.Printf("   🔍 Processing: %s\n", targetURL)

	// Fetch page with proxy
	doc, err := s.fetchPage(ctx, targetURL)
	if err != nil {
		fmt.Printf("   ❌ Fetch failed: %v\n", err)
		return // Silent fail, move to next
	}

	// Extract article content
	title, content := s.extractContent(doc)
	// Length validation: Ensure content fits 1024 token limit for AI judge
	// 1 token ≈ 4 chars, so 1024 tokens ≈ 4096 chars
	const maxContentChars = 4000 // Leave buffer for title + prompt

	if title == "" || len(content) < 500 {
		fmt.Printf("   ⚠️  Invalid article (title=%d chars, content=%d chars)\n", len(title), len(content))
		return // Not a valid article
	}

	// Truncate content to fit GPU context window (1024 tokens max)
	if len(content) > maxContentChars {
		content = content[:maxContentChars] + "..."
	}

	// Apply filters (using original title)
	if !filters.IsValidArticle(targetURL, title, content) {
		fmt.Printf("   ⛔ Filtered out: %s\n", title[:min(50, len(title))])
		return // Filtered out
	}

	// SMART CHUNKING: Split content into chunks preserving paragraphs
	chunks := s.chunkContent(content)
	fmt.Printf("   📑 Split into %d chunks\n", len(chunks))

	// FILTER REFERENCES: Remove reference-only chunks BEFORE AI judge
	filteredChunks := []string{}
	for i, chunk := range chunks {
		if s.isReferenceChunk(chunk) {
			fmt.Printf("      📚 Chunk %d: REFERENCE (skipped)\n", i+1)
			continue
		}
		filteredChunks = append(filteredChunks, chunk)
	}

	if len(filteredChunks) == 0 {
		fmt.Printf("   ⛔ All chunks are references - article rejected\n")
		return
	}

	fmt.Printf("   ✅ Kept %d/%d non-reference chunks\n", len(filteredChunks), len(chunks))

	// AI JUDGE PER CHUNK: Filter quality chunks (if enabled)
	relevantChunks := filteredChunks // Default: use all non-reference chunks

	if s.cfg.UseAIJudge {
		fmt.Printf("   🤖 AI JUDGING chunks...\n")
		relevantChunks = []string{}
		for i, chunk := range filteredChunks {
			fmt.Printf("      🤖 Judging chunk %d/%d...\n", i+1, len(filteredChunks))

			isRelevant, err := s.ollamaClient.CheckRelevance(ctx, title, chunk)
			if err != nil {
				fmt.Printf("      ⚠️  AI error on chunk %d: %v\n", i+1, err)
				continue
			}

			if isRelevant {
				relevantChunks = append(relevantChunks, chunk)
				fmt.Printf("      ✅ Chunk %d: RELEVANT\n", i+1)
			} else {
				fmt.Printf("      ❌ Chunk %d: NOT RELEVANT\n", i+1)
			}
		}

		// Reject if no relevant chunks found
		if len(relevantChunks) == 0 {
			fmt.Printf("   🤖 AI REJECTED: No relevant chunks found\n")
			return
		}

		fmt.Printf("   ✅ Kept %d/%d relevant chunks\n", len(relevantChunks), len(chunks))
	} else {
		fmt.Printf("   ⚡ AI Judge DISABLED - Using all chunks\n")
	}

	// Combine relevant chunks for final content
	content = strings.Join(relevantChunks, "\n\n")

	// Clean content
	content = filters.CleanContent(content)

	// OPSI 3 RAG STRATEGY: Summary for Embedding + Full Content for LLM
	// 1. Generate AI summary (200 words) → used for all-minilm embedding
	// 2. Store full content → used for albashiro LLM context

	var summary string
	if s.cfg.UseAISummary {
		fmt.Printf("   📝 Generating AI summary for embedding (200 words)...\n")
		var err error
		summary, err = s.ollamaClient.GenerateSummary(ctx, title, content)
		if err != nil {
			fmt.Printf("   ⚠️  AI summary failed: %v\n", err)
			// Fallback: use first 800 chars (~200 words)
			summary = content[:min(800, len(content))]
		}
	} else {
		// Fallback: first 800 chars (~200 words)
		summary = content[:min(800, len(content))]
		fmt.Printf("   ⚡ AI Summary DISABLED - Using first 800 chars\n")
	}

	// GENERATE EMBEDDING from SUMMARY (not title, not full content!)
	// This captures content semantics while keeping embedding fast
	fmt.Printf("   🧮 Generating summary embedding...\n")
	summaryEmbedding, err := s.ollamaClient.GenerateEmbedding(ctx, summary)
	if err != nil {
		fmt.Printf("   ⚠️  Summary embedding failed: %v\n", err)
		return
	}

	// Create single vector: summary embedding + FULL CONTENT
	// Vector search finds articles by summary similarity
	// LLM gets full content for detailed answers
	vectors := []VectorData{
		{
			ChunkText:  content,          // FULL content for LLM context
			ChunkIndex: 1,                // Single entry per article
			Embedding:  summaryEmbedding, // Embedding from SUMMARY
		},
	}

	fmt.Printf("   ✅ Summary embedded (%d chars) + full content stored (%d chars)\n", len(summary), len(content))

	// Create article object with RAG metadata
	articleID := int(atomic.AddInt64(&s.articlesProcessed, 1))
	article := &Article{
		ID:             articleID,
		URL:            targetURL,
		OriginalTitle:  title,
		ProcessedTitle: title,
		Content:        content,
		Summary:        summary, // AI-generated summary (used for embedding)
		ContentLength:  len(content),
		ChunksTotal:    len(chunks),         // Total chunks processed
		ChunksRelevant: len(relevantChunks), // Chunks that passed AI judge
		Vectors:        vectors,             // Embeddings for RAG retrieval
		Timestamp:      time.Now().Format(time.RFC3339),
	}

	// Send to result collector
	select {
	case <-ctx.Done():
		return
	case s.resultChan <- article:
	}

	fmt.Printf("   ✅ [%d/%d] %s\n", articleID, s.cfg.MaxArticles, title[:min(50, len(title))])

	// RECURSIVE CRAWLING: Extract links from saved article for further discovery
	go s.extractLinksFromArticle(ctx, doc, targetURL)
}

// isReferenceChunk detects if a chunk is primarily bibliographic references
func (s *Scraper) isReferenceChunk(chunk string) bool {
	chunk = strings.ToLower(chunk)

	// Count reference indicators
	referenceScore := 0

	// 1. Check for DOI/URL patterns (strong indicator)
	doiCount := strings.Count(chunk, "doi.org/") + strings.Count(chunk, "https://doi")
	urlCount := strings.Count(chunk, "http://") + strings.Count(chunk, "https://")
	if doiCount > 2 || urlCount > 3 {
		referenceScore += 3
	}

	// 2. Check for year patterns in citations (e.g., "2020", "2021")
	yearPattern := regexp.MustCompile(`\(?\b(19|20)\d{2}\b\)?`)
	yearMatches := yearPattern.FindAllString(chunk, -1)
	if len(yearMatches) > 5 {
		referenceScore += 2
	}

	// 3. Check for author citation patterns (Name, A. B.)
	authorPattern := regexp.MustCompile(`[A-Z][a-z]+,\s+[A-Z]\.`)
	authorMatches := authorPattern.FindAllString(chunk, -1)
	if len(authorMatches) > 3 {
		referenceScore += 2
	}

	// 4. Check for reference section keywords
	refKeywords := []string{"references", "bibliography", "works cited", "daftar pustaka"}
	for _, keyword := range refKeywords {
		if strings.Contains(chunk, keyword) {
			referenceScore += 1
			break
		}
	}

	// 5. Check for journal/publisher patterns
	journalKeywords := []string{"journal of", "proceedings of", "international journal", "vol.", "pp."}
	journalCount := 0
	for _, keyword := range journalKeywords {
		if strings.Contains(chunk, keyword) {
			journalCount++
		}
	}
	if journalCount > 2 {
		referenceScore += 2
	}

	// If score >= 5, it's likely a reference chunk
	return referenceScore >= 5
}

// chunkContent splits content into chunks preserving paragraph boundaries
// Each chunk is ~3500 chars (~875 tokens) to fit GPU context window
func (s *Scraper) chunkContent(content string) []string {
	const maxChunkSize = 3500 // ~875 tokens (leave buffer for prompt)

	// Split by paragraphs first
	paragraphs := strings.Split(content, "\n\n")

	chunks := []string{}
	currentChunk := ""

	for _, para := range paragraphs {
		// If adding this paragraph exceeds limit, save current chunk
		if len(currentChunk)+len(para) > maxChunkSize {
			if currentChunk != "" {
				chunks = append(chunks, strings.TrimSpace(currentChunk))
				currentChunk = ""
			}

			// If single paragraph is too large, force split
			if len(para) > maxChunkSize {
				chunks = append(chunks, para[:maxChunkSize])
				continue
			}
		}

		currentChunk += para + "\n\n"
	}

	// Add remaining chunk
	if currentChunk != "" {
		chunks = append(chunks, strings.TrimSpace(currentChunk))
	}

	return chunks
}

// extractLinksFromArticle performs recursive crawling by finding new links in saved articles
func (s *Scraper) extractLinksFromArticle(ctx context.Context, doc *goquery.Document, baseURL string) {
	// Extract all links from the article
	doc.Find("a[href]").Each(func(i int, sel *goquery.Selection) {
		// Check for cancellation
		if ctx.Err() != nil {
			return
		}

		href, exists := sel.Attr("href")
		if !exists {
			return
		}

		// Convert relative URLs to absolute
		absoluteURL, err := url.Parse(href)
		if err != nil {
			return
		}
		base, _ := url.Parse(baseURL)
		fullURL := base.ResolveReference(absoluteURL).String()

		// Get link text for semantic matching
		linkText := strings.TrimSpace(sel.Text())
		if len(linkText) < 10 {
			return // Skip short link text (likely UI elements)
		}

		// Semantic matching: Only queue if link text is relevant
		linkVec, err := s.ollamaClient.GenerateEmbedding(ctx, linkText)
		if err != nil {
			return
		}

		score := cosineSimilarity(linkVec, s.topicEmbedding)
		if score < 0.20 {
			return // Not semantically relevant
		}

		// Check if already processed
		urlMutex.Lock()
		if processedURLs[fullURL] {
			urlMutex.Unlock()
			return
		}
		processedURLs[fullURL] = true
		urlMutex.Unlock()

		// Queue for processing
		select {
		case <-ctx.Done():
			return
		case s.jobChan <- fullURL:
			fmt.Printf("      🔗 Recursive link: [%.2f] %s\n", score, linkText[:min(50, len(linkText))])
		default:
			// Job queue full, skip
		}
	})
}

func extractDomain(urlStr string) string {
	u, err := url.Parse(urlStr)
	if err != nil {
		return "unknown"
	}
	return u.Host
}

func (s *Scraper) fetchPage(ctx context.Context, targetURL string) (*goquery.Document, error) {
	maxRetries := 1
	if s.cfg.ProxyEnabled {
		maxRetries = 3
	}

	var lastErr error

	for i := 0; i < maxRetries; i++ {
		if i > 0 {
			time.Sleep(2 * time.Second) // Wait before retry
		} else {
			time.Sleep(500 * time.Millisecond) // Rate limiting: appear human-like
		}

		var proxyURL string
		if s.cfg.ProxyEnabled {
			proxyURL = s.proxyPool.Get()
		}

		client := s.httpClient
		if proxyURL != "" {
			proxyURLParsed, _ := url.Parse(proxyURL)
			client = &http.Client{
				Timeout: 30 * time.Second,
				Transport: &http.Transport{
					Proxy: http.ProxyURL(proxyURLParsed),
					TLSClientConfig: &tls.Config{
						InsecureSkipVerify: true,
					},
					DisableKeepAlives: true, // Prevent "too many open connections"
				},
			}
		}

		req, err := http.NewRequestWithContext(ctx, "GET", targetURL, nil)
		if err != nil {
			return nil, err
		}

		// Set realistic browser headers to bypass 403s
		req.Header.Set("User-Agent", getRandomUserAgent())
		req.Header.Set("Accept", "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7")
		req.Header.Set("Accept-Language", "en-US,en;q=0.9,id;q=0.8")
		// req.Header.Set("Accept-Encoding", "gzip, deflate, br") // LET GO HANDLE GZIP AUTOMATICALLY
		req.Header.Set("Connection", "keep-alive")
		req.Header.Set("Upgrade-Insecure-Requests", "1")
		req.Header.Set("Sec-Fetch-Dest", "document")
		req.Header.Set("Sec-Fetch-Mode", "navigate")
		req.Header.Set("Sec-Fetch-Site", "none")
		req.Header.Set("Sec-Fetch-User", "?1")
		req.Header.Set("Cache-Control", "max-age=0")

		resp, err := client.Do(req)
		if err != nil {
			if proxyURL != "" {
				s.proxyPool.Remove(proxyURL)
			}
			lastErr = err
			logMsg := "Fetch retry"
			if proxyURL != "" {
				logMsg = "Proxy retry"
			}
			fmt.Printf("   ⚠️  %s %d/%d failed: %v\n", logMsg, i+1, maxRetries, err)
			continue
		}
		defer resp.Body.Close()

		if resp.StatusCode != 200 {
			if proxyURL != "" {
				s.proxyPool.Remove(proxyURL)
			}
			lastErr = fmt.Errorf("HTTP %d", resp.StatusCode)
			fmt.Printf("   ⚠️  Proxy retry %d/%d status error: %d\n", i+1, maxRetries, resp.StatusCode)
			continue
		}

		return goquery.NewDocumentFromReader(resp.Body)
	}

	return nil, lastErr
}

func (s *Scraper) extractContent(doc *goquery.Document) (string, string) {
	// Extract title
	title := doc.Find("h1").First().Text()
	if title == "" {
		title = doc.Find("title").First().Text()
	}
	title = strings.TrimSpace(title)

	// Extract content
	var contentParts []string
	doc.Find("p").Each(func(i int, sel *goquery.Selection) {
		text := strings.TrimSpace(sel.Text())
		if len(text) > 40 {
			contentParts = append(contentParts, text)
		}
	})

	content := strings.Join(contentParts, "\n\n")
	return title, content
}

func (s *Scraper) generateVectors(ctx context.Context, content string) ([]VectorData, error) {
	chunks := s.chunkText(content)
	vectors := make([]VectorData, 0, len(chunks))

	// Print progress for user feedback
	if len(chunks) > 1 {
		fmt.Printf("      Generating %d embeddings...", len(chunks))
	}

	for i, chunk := range chunks {
		embedding, err := s.ollamaClient.GenerateEmbedding(ctx, chunk)
		if err != nil {
			continue // Skip failed chunks
		}

		vectors = append(vectors, VectorData{
			ChunkText: chunk,
			Embedding: embedding,
		})
		atomic.AddInt64(&s.embeddingsGenerated, 1)

		// Show progress
		if len(chunks) > 1 && (i+1)%3 == 0 {
			fmt.Printf(".")
		}
	}

	if len(chunks) > 1 {
		fmt.Printf(" Done!\n")
	}

	return vectors, nil
}

func (s *Scraper) chunkText(text string) []string {
	chunkSize := s.cfg.ChunkSize
	overlap := s.cfg.ChunkOverlap

	if len(text) <= chunkSize {
		return []string{text}
	}

	chunks := make([]string, 0)
	start := 0

	for start < len(text) {
		end := start + chunkSize
		if end > len(text) {
			end = len(text)
		}

		chunk := text[start:end]
		chunks = append(chunks, strings.TrimSpace(chunk))

		start = end - overlap
		if start <= 0 {
			start = end
		}
	}

	return chunks
}

func (s *Scraper) resultCollector(ctx context.Context, wg *sync.WaitGroup) {
	defer wg.Done()

	// ZERO MEMORY FOOTPRINT: No article accumulation
	monitorTicker := time.NewTicker(5 * time.Second)
	defer monitorTicker.Stop()

	for {
		select {
		case <-ctx.Done():
			return
		case article, ok := <-s.resultChan:
			if !ok {
				return
			}

			// Update latest titles for monitoring
			s.titlesMu.Lock()
			s.latestTitles = append(s.latestTitles, article.ProcessedTitle)
			if len(s.latestTitles) > 5 {
				s.latestTitles = s.latestTitles[1:]
			}
			s.titlesMu.Unlock()

			// IMMEDIATE DISK WRITE (No buffering)
			s.saveToStream(article)

		case <-monitorTicker.C:
			// Update monitor file
			s.updateMonitor()
		}
	}
}

func (s *Scraper) saveBackup(articles []*Article) {
	if len(articles) == 0 {
		return
	}

	filename := fmt.Sprintf("batch_%d_to_%d.json", articles[0].ID, articles[len(articles)-1].ID)
	filepath := filepath.Join(s.cfg.BackupDir, filename)

	data, err := json.MarshalIndent(articles, "", "  ")
	if err != nil {
		return
	}

	os.WriteFile(filepath, data, 0644)
	s.backupFile = filepath
}

func (s *Scraper) saveToStream(article *Article) {
	filename := fmt.Sprintf("article_%d.json", article.ID)
	filepath := filepath.Join(s.cfg.StreamDir, filename)

	data, err := json.MarshalIndent(article, "", "  ")
	if err != nil {
		return
	}

	os.WriteFile(filepath, data, 0644)
}

func (s *Scraper) GetStats() Stats {
	return Stats{
		ArticlesProcessed:   int(atomic.LoadInt64(&s.articlesProcessed)),
		EmbeddingsGenerated: int(atomic.LoadInt64(&s.embeddingsGenerated)),
		BackupFile:          s.backupFile,
	}
}

func min(a, b int) int {
	if a < b {
		return a
	}
	return b
}

func getRandomUserAgent() string {
	userAgents := []string{
		"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
		"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
		"Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0",
		"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
		"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Safari/605.1.15",
		"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36 Edg/119.0.0.0",
	}
	return userAgents[rand.Intn(len(userAgents))]
}
