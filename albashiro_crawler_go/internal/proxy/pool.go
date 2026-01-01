package proxy

import (
	"albashiro_crawler/internal/config"
	"bufio"
	"context"
	"crypto/tls"
	"encoding/json"
	"fmt"
	"math/rand"
	"net/http"
	"net/url"
	"os"
	"strings"
	"sync"
	"time"
)

type Pool struct {
	proxies []string
	mu      sync.RWMutex
	cfg     *config.Config
	client  *http.Client
}

func NewPool(cfg *config.Config) *Pool {
	return &Pool{
		proxies: make([]string, 0),
		cfg:     cfg,
		client: &http.Client{
			Timeout: time.Duration(cfg.ProxyTimeout) * time.Second,
		},
	}
}

func (p *Pool) Initialize() error {
	if !p.cfg.ProxyEnabled {
		return nil
	}

	fmt.Println("   🔄 Fetching proxies from external sources...")

	// Try loading from cache first
	if proxies, err := p.loadFromCache(); err == nil && len(proxies) > 5 {
		fmt.Printf("   📂 Loaded %d proxies from cache\n", len(proxies))
		p.proxies = proxies
		return nil
	}

	// Fetch from internet
	candidates := make([]string, 0)

	for _, source := range p.cfg.ProxySources {
		proxies, err := p.fetchFromSource(source)
		if err != nil {
			fmt.Printf("   ⚠️  Failed to fetch from %s: %v\n", source, err)
			continue
		}
		candidates = append(candidates, proxies...)
	}

	if len(candidates) == 0 {
		return fmt.Errorf("no proxies found from any source")
	}

	// Deduplicate
	uniqueMap := make(map[string]bool)
	for _, proxy := range candidates {
		uniqueMap[proxy] = true
	}
	candidates = make([]string, 0, len(uniqueMap))
	for proxy := range uniqueMap {
		candidates = append(candidates, proxy)
	}

	// LIMIT to 20000 proxies (Massive Scale for low yield)
	if len(candidates) > 20000 {
		// Shuffle and take first 5000
		rand.Shuffle(len(candidates), func(i, j int) {
			candidates[i], candidates[j] = candidates[j], candidates[i]
		})
		candidates = candidates[:5000]
	}

	fmt.Printf("   🧪 Validating %d proxies in parallel (timeout %ds)...\n", len(candidates), p.cfg.ProxyTimeout)

	// Parallel validation
	validProxies := p.validateParallel(candidates)

	if len(validProxies) == 0 {
		return fmt.Errorf("no valid proxies found after validation")
	}

	// Keep only top 100 fastest proxies
	if len(validProxies) > 100 {
		validProxies = validProxies[:100]
	}

	p.mu.Lock()
	p.proxies = validProxies
	p.mu.Unlock()

	yieldPercent := float64(len(validProxies)) / float64(len(candidates)) * 100
	fmt.Printf("   📡 SUCCESS! %d validated proxies ready (Yield: %.1f%%)\n", len(validProxies), yieldPercent)

	// Save to cache
	p.saveToCache(validProxies)

	return nil
}

func (p *Pool) validateParallel(candidates []string) []string {
	var wg sync.WaitGroup
	resultChan := make(chan string, len(candidates))
	semaphore := make(chan struct{}, 100) // Reduced to 100 to prevent CPU/Network choke

	for _, proxy := range candidates {
		wg.Add(1)
		go func(px string) {
			defer wg.Done()
			semaphore <- struct{}{}
			defer func() { <-semaphore }()

			if p.validateProxy(px) {
				resultChan <- px
			}
		}(proxy)
	}

	go func() {
		wg.Wait()
		close(resultChan)
	}()

	validProxies := make([]string, 0)
	for proxy := range resultChan {
		validProxies = append(validProxies, proxy)
	}

	return validProxies
}

func (p *Pool) validateProxy(proxyURL string) bool {
	// Test against Google (HTTPS) to ensure proxy supports CONNECT
	testURL := "https://www.google.com"

	proxyURLParsed, err := url.Parse(proxyURL)
	if err != nil {
		return false
	}

	client := &http.Client{
		Timeout: time.Duration(p.cfg.ProxyTimeout) * time.Second,
		Transport: &http.Transport{
			Proxy: http.ProxyURL(proxyURLParsed),
			TLSClientConfig: &tls.Config{
				InsecureSkipVerify: true, // Important for some proxies
			},
			DisableKeepAlives: true,
		},
	}

	ctx, cancel := context.WithTimeout(context.Background(), time.Duration(p.cfg.ProxyTimeout)*time.Second)
	defer cancel()

	req, err := http.NewRequestWithContext(ctx, "GET", testURL, nil)
	if err != nil {
		return false
	}
	req.Header.Set("User-Agent", "Mozilla/5.0")

	resp, err := client.Do(req)
	if err != nil {
		return false
	}
	defer resp.Body.Close()

	return resp.StatusCode == 200
}

func (p *Pool) fetchFromSource(source string) ([]string, error) {
	resp, err := p.client.Get(source)
	if err != nil {
		return nil, err
	}
	defer resp.Body.Close()

	if resp.StatusCode != 200 {
		return nil, fmt.Errorf("HTTP %d", resp.StatusCode)
	}

	proxies := make([]string, 0)

	// Check if JSON (GeoNode)
	if strings.Contains(source, "geonode") {
		var data struct {
			Data []struct {
				IP        string   `json:"ip"`
				Port      string   `json:"port"`
				Protocols []string `json:"protocols"`
			} `json:"data"`
		}
		if err := json.NewDecoder(resp.Body).Decode(&data); err == nil {
			for _, p := range data.Data {
				proto := "http"
				if len(p.Protocols) > 0 {
					proto = p.Protocols[0]
				}
				proxies = append(proxies, fmt.Sprintf("%s://%s:%s", proto, p.IP, p.Port))
			}
		}
	} else {
		// Plain text format
		scanner := bufio.NewScanner(resp.Body)
		for scanner.Scan() {
			line := strings.TrimSpace(scanner.Text())
			if line != "" && strings.Contains(line, ":") {
				if !strings.Contains(line, "://") {
					line = "http://" + line
				}
				proxies = append(proxies, line)
			}
		}
	}

	return proxies, nil
}

func (p *Pool) Get() string {
	p.mu.RLock()
	defer p.mu.RUnlock()

	if len(p.proxies) == 0 {
		return ""
	}

	return p.proxies[rand.Intn(len(p.proxies))]
}

func (p *Pool) Remove(proxy string) {
	p.mu.Lock()
	defer p.mu.Unlock()

	for i, px := range p.proxies {
		if px == proxy {
			p.proxies = append(p.proxies[:i], p.proxies[i+1:]...)
			break
		}
	}
}

func (p *Pool) Count() int {
	p.mu.RLock()
	defer p.mu.RUnlock()
	return len(p.proxies)
}

func (p *Pool) loadFromCache() ([]string, error) {
	cacheFile := "c:\\apache\\htdocs\\albashiro\\scraped_data\\valid_proxies.txt"
	file, err := os.Open(cacheFile)
	if err != nil {
		return nil, err
	}
	defer file.Close()

	proxies := make([]string, 0)
	scanner := bufio.NewScanner(file)
	for scanner.Scan() {
		if line := strings.TrimSpace(scanner.Text()); line != "" {
			proxies = append(proxies, line)
		}
	}

	return proxies, scanner.Err()
}

func (p *Pool) saveToCache(proxies []string) {
	cacheFile := "c:\\apache\\htdocs\\albashiro\\scraped_data\\valid_proxies.txt"
	file, err := os.Create(cacheFile)
	if err != nil {
		return
	}
	defer file.Close()

	for _, proxy := range proxies {
		fmt.Fprintln(file, proxy)
	}
}
