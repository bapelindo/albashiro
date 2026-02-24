package scraper

import (
	"bufio"
	"context"
	"fmt"
	"net/url"
	"os"
	"strings"
	"sync"

	"github.com/PuerkitoBio/goquery"
)

var (
	processedURLs     = make(map[string]bool)
	urlMutex          sync.Mutex
	processedURLsFile = "scraped_data/processed_urls.txt"
)

// LoadProcessedURLs loads previously processed URLs from disk
func LoadProcessedURLs() error {
	file, err := os.Open(processedURLsFile)
	if err != nil {
		if os.IsNotExist(err) {
			fmt.Println("   📂 No previous URL history found (starting fresh)")
			return nil
		}
		return err
	}
	defer file.Close()

	urlMutex.Lock()
	defer urlMutex.Unlock()

	scanner := bufio.NewScanner(file)
	count := 0
	for scanner.Scan() {
		url := strings.TrimSpace(scanner.Text())
		if url != "" {
			processedURLs[url] = true
			count++
		}
	}

	if err := scanner.Err(); err != nil {
		return err
	}

	fmt.Printf("   ✅ Loaded %d previously processed URLs (will skip these)\n", count)
	return nil
}

// SaveProcessedURL appends a URL to the persistent file
func SaveProcessedURL(url string) error {
	// Create directory if not exists
	os.MkdirAll("scraped_data", 0755)

	file, err := os.OpenFile(processedURLsFile, os.O_APPEND|os.O_CREATE|os.O_WRONLY, 0644)
	if err != nil {
		return err
	}
	defer file.Close()

	_, err = file.WriteString(url + "\n")
	return err
}

// crawlSeedPage fetches a seed page and extracts article links
func (s *Scraper) crawlSeedPage(ctx context.Context, seedURL string) {
	fmt.Printf("   🌱 Crawling seed: %s\n", seedURL)

	doc, err := s.fetchPage(ctx, seedURL)
	if err != nil {
		fmt.Printf("   ❌ Seed fetch failed: %v\n", err)
		return
	}

	// Extract links and queue them for parallel processing
	doc.Find("a[href]").Each(func(i int, sel *goquery.Selection) {
		// Panic recovery to prevent goroutine crashes
		defer func() {
			if r := recover(); r != nil {
				fmt.Printf("      ⚠️  Panic in seed crawling: %v\n", r)
			}
		}()

		// Check for cancellation explicitly to prevent hang
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
		baseURL, _ := url.Parse(seedURL)
		fullURL := baseURL.ResolveReference(absoluteURL).String()

		// SIMPLIFIED FILTERING: Only semantic matching (no domain/regex/blacklist)
		// Let AI judge handle final relevance check during processing
		linkText := strings.TrimSpace(sel.Text())

		// Skip if link text is too short (likely navigation/UI element)
		if len(linkText) < 10 {
			return
		}

		// Skip duplicates (check in-memory map)
		urlMutex.Lock()
		if processedURLs[fullURL] {
			urlMutex.Unlock()
			return
		}
		processedURLs[fullURL] = true
		urlMutex.Unlock()

		// Save to persistent storage (async, ignore errors)
		go SaveProcessedURL(fullURL)

		// PARALLEL PROCESSING: Queue URL for worker pool
		// Check if channel is still open before sending
		s.channelMu.RLock()
		closed := s.channelClosed
		s.channelMu.RUnlock()

		if closed {
			return // Channel already closed, skip
		}

		select {
		case <-ctx.Done():
			return
		case s.jobChan <- fullURL:
			fmt.Printf("      ⚡ Queued for parallel processing: %s\n", linkText[:min(50, len(linkText))])
		default:
			// Job queue full, skip
			fmt.Printf("      ⚠️  Queue full, skipping: %s\n", linkText[:min(30, len(linkText))])
		}
	})
}

func isNonArticleURL(urlStr string) bool {
	// Skip common non-article patterns
	skipPatterns := []string{
		"/tag/", "/category/", "/author/", "/page/",
		"/search/", "/login", "/register", "/contact",
		"/about", "/privacy", "/terms", "/sitemap",
		".pdf", ".jpg", ".png", ".gif", ".css", ".js",
		"#", "javascript:", "mailto:", "whatsapp:",
		"/cart", "/donate", "/volunteer", "/careers",
	}

	lower := strings.ToLower(urlStr)
	for _, pattern := range skipPatterns {
		if strings.Contains(lower, pattern) {
			return true
		}
	}

	return false
}
