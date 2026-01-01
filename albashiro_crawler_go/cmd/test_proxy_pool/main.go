package main

import (
	"albashiro_crawler/internal/config"
	"albashiro_crawler/internal/proxy"
	"fmt"
	"time"
)

func main() {
	fmt.Println("🔍 TESTING PROXY POOL LOGIC...")

	// Force Config: Enable Proxy, 5s Timeout (User Setting)
	cfg := config.Load()
	cfg.ProxyEnabled = true
	cfg.ProxyTimeout = 5

	fmt.Printf("   ⚙️  Config: Enabled=%v, Timeout=%ds\n", cfg.ProxyEnabled, cfg.ProxyTimeout)
	fmt.Println("   ... Initializing Pool (fetching & validating) ...")

	start := time.Now()
	pool := proxy.NewPool(cfg)
	err := pool.Initialize()
	duration := time.Since(start)

	fmt.Println("\n   🏁 RESULTS:")
	if err != nil {
		fmt.Printf("   ❌ Proxy Init Failed: %v\n", err)
		fmt.Println("   ⚠️  System automatically fell back to DIRECT connection.")
	} else {
		count := pool.Count()
		fmt.Printf("   ✅ Success! Found %d working proxies.\n", count)
	}
	fmt.Printf("   ⏱️  Time Taken: %.2fs\n", duration.Seconds())
}
