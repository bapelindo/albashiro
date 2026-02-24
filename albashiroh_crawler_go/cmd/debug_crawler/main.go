package main

import (
	"crypto/tls"
	"fmt"
	"net/http"
	"net/url"
	"regexp"
	"strings"
	"time"

	"github.com/PuerkitoBio/goquery"
)

func main() {
	targetURL := "https://psikologi.ui.ac.id/berita"
	fmt.Printf("🔍 Debugging Crawler Logic on: %s\n", targetURL)

	// 1. Fetch Page
	client := &http.Client{
		Timeout: 30 * time.Second,
		Transport: &http.Transport{
			TLSClientConfig: &tls.Config{InsecureSkipVerify: true},
		},
	}

	req, _ := http.NewRequest("GET", targetURL, nil)
	// Use same headers as crawler
	req.Header.Set("User-Agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36")

	resp, err := client.Do(req)
	if err != nil {
		fmt.Printf("❌ Catch failed: %v\n", err)
		return
	}
	defer resp.Body.Close()

	fmt.Printf("✅ Status Code: %d\n", resp.StatusCode)

	doc, err := goquery.NewDocumentFromReader(resp.Body)
	if err != nil {
		fmt.Printf("❌ GoQuery failed: %v\n", err)
		return
	}

	// 2. Analyze Links
	linksFound := 0
	domainMatch := 0
	notBlacklisted := 0

	baseURL, _ := url.Parse(targetURL)

	fmt.Println("\n--- Analyzing Links ---")
	doc.Find("a[href]").Each(func(i int, sel *goquery.Selection) {
		href, _ := sel.Attr("href")
		linksFound++

		// Convert relative URLs to absolute
		absoluteURL, _ := url.Parse(href)
		fullURL := baseURL.ResolveReference(absoluteURL).String()

		// Logic from filters.go
		isDomain := strings.Contains(fullURL, baseURL.Host)
		isValidArticle := isArticleURL(fullURL)

		status := "✅ ACCEPTED"
		if !isDomain {
			status = "❌ DIFFERENT DOMAIN"
		} else if !isValidArticle {
			status = "⛔ REJECTED (Pattern)"
		} else {
			domainMatch++
			notBlacklisted++
		}

		if i < 20 { // Print first 20 links
			fmt.Printf("[%s] %s\n", status, fullURL)
		}
	})

	fmt.Printf("\n--- Summary ---\n")
	fmt.Printf("Total HREFs found: %d\n", linksFound)
	fmt.Printf("Same Domain: %d\n", domainMatch)
	fmt.Printf("Accepted: %d\n", notBlacklisted)
}

// Valid article URL regex patterns
var ArticleUrlRegex = []*regexp.Regexp{
	regexp.MustCompile(`/\d{4}/\d{2}/\d{2}/`), // Year/Month/Day
	regexp.MustCompile(`/\d{4}/\d{2}/`),       // Year/Month
	regexp.MustCompile(`/\d{4}/`),             // Year (weak but useful)
}

var ArticlePatterns = []string{
	"/artikel/", "/article/", "/post/", "/blog/",
	"/news/", "/berita/", "/story/", "/read/",
	// "-" removed
}

func isArticleURL(urlStr string) bool {
	lower := strings.ToLower(urlStr)

	// 1. Check strict keywords in path
	hasArticlePattern := false
	for _, pattern := range ArticlePatterns {
		if strings.Contains(lower, pattern) {
			hasArticlePattern = true
			break
		}
	}

	// 2. Check regex patterns (Dates)
	if !hasArticlePattern {
		for _, re := range ArticleUrlRegex {
			if re.MatchString(lower) {
				hasArticlePattern = true
				break
			}
		}
	}

	// 3. Fallback: If it has at least 3 dashes AND path depth > 1
	if !hasArticlePattern {
		dashCount := strings.Count(lower, "-")
		slashCount := strings.Count(lower, "/") - 2
		if dashCount >= 3 && slashCount >= 1 {
			hasArticlePattern = true
		}
	}

	return hasArticlePattern
}
