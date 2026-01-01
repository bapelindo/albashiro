package scraper

import (
	"context"
	"fmt"
	"net/url"
	"strings"
	"sync"

	"github.com/PuerkitoBio/goquery"
)

var (
	processedURLs = make(map[string]bool)
	urlMutex      sync.Mutex
)

// crawlSeedPage fetches a seed page and extracts article links
func (s *Scraper) crawlSeedPage(ctx context.Context, seedURL string) {
	fmt.Printf("   🌱 Crawling seed: %s\n", seedURL)

	doc, err := s.fetchPage(ctx, seedURL)
	if err != nil {
		fmt.Printf("   ❌ Seed fetch failed: %v\n", err)
		return
	}

	// Extract all links from the page
	links := make([]string, 0)
	doc.Find("a[href]").Each(func(i int, sel *goquery.Selection) {
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

		// Semantic Check: Does link text match our topic?
		linkVec, err := s.ollamaClient.GenerateEmbedding(ctx, linkText)
		if err != nil {
			return // Skip if embedding fails
		}

		score := cosineSimilarity(linkVec, s.topicEmbedding)
		// Threshold 0.20 determined by testing (Indonesian content scores ~0.21-0.57)
		if score < 0.20 {
			return // Not semantically relevant
		}

		fmt.Printf("      🧠 Semantic Match: [%.2f] %s (%s)\n", score, linkText, fullURL)

		// Skip duplicates
		urlMutex.Lock()
		if processedURLs[fullURL] {
			urlMutex.Unlock()
			return
		}
		processedURLs[fullURL] = true
		urlMutex.Unlock()

		links = append(links, fullURL)
	})

	fmt.Printf("   📄 Found %d potential article links\n", len(links))

	// Queue article links for processing
	for _, link := range links {
		select {
		case <-ctx.Done():
			return
		case s.jobChan <- link:
		}
	}
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
