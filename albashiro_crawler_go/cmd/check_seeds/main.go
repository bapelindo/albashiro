package main

import (
	"albashiro_crawler/internal/config"
	"crypto/tls"
	"fmt"
	"math/rand"
	"net/http"
	"sync"
	"time"
)

func main() {
	cfg := config.Load()
	seeds := cfg.Seeds

	fmt.Printf("🔍 Checking %d seeds for accessibility (Timeout 10s)...\n\n", len(seeds))

	verificationChan := make(chan string, len(seeds))
	var wg sync.WaitGroup
	semaphore := make(chan struct{}, 20) // 20 concurrent checks

	successCount := 0
	failCount := 0
	var mu sync.Mutex

	for _, seed := range seeds {
		wg.Add(1)
		go func(urlStr string) {
			defer wg.Done()
			semaphore <- struct{}{}
			defer func() { <-semaphore }()

			status, err := checkURL(urlStr)

			mu.Lock()
			if err == nil && status == 200 {
				successCount++
				fmt.Printf("✅ [200] %s\n", urlStr)
			} else {
				failCount++
				msg := ""
				if err != nil {
					msg = err.Error()
				} else {
					msg = fmt.Sprintf("HTTP %d", status)
				}
				fmt.Printf("❌ [%s] %s\n", msg, urlStr)
			}
			mu.Unlock()
		}(seed)
	}

	wg.Wait()
	close(verificationChan)

	fmt.Printf("\n📊 Summary:\n")
	fmt.Printf("✅ Accessible: %d\n", successCount)
	fmt.Printf("❌ Inaccessible: %d\n", failCount)
	fmt.Printf("📈 Success Rate: %.1f%%\n", float64(successCount)/float64(len(seeds))*100)
}

func checkURL(targetURL string) (int, error) {
	client := &http.Client{
		Timeout: 10 * time.Second,
		Transport: &http.Transport{
			TLSClientConfig: &tls.Config{InsecureSkipVerify: true},
		},
	}

	req, err := http.NewRequest("GET", targetURL, nil)
	if err != nil {
		return 0, err
	}

	req.Header.Set("User-Agent", getRandomUserAgent())
	req.Header.Set("Accept", "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7")

	resp, err := client.Do(req)
	if err != nil {
		return 0, err
	}
	defer resp.Body.Close()

	return resp.StatusCode, nil
}

func getRandomUserAgent() string {
	userAgents := []string{
		"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
		"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
		"Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0",
	}
	return userAgents[rand.Intn(len(userAgents))]
}
