package main

import (
	"albashiro_crawler/internal/config"
	"albashiro_crawler/internal/proxy"
	"albashiro_crawler/internal/scraper"
	"context"
	"fmt"
	"log"
	"os"
	"os/signal"
	"sync"
	"syscall"
	"time"
)

func main() {
	// Load configuration
	cfg := config.Load()

	fmt.Println("=============================================================")
	fmt.Println("🕷️  ALBASHIRO KNOWLEDGE CRAWLER - GOLANG EDITION")
	fmt.Println("=============================================================")
	fmt.Printf("   Target Articles: %d\n", cfg.MaxArticles)
	fmt.Printf("   Worker Pool: %d goroutines\n", cfg.WorkerCount)
	status := "Disabled"
	if cfg.ProxyEnabled {
		status = fmt.Sprintf("Enabled (%ds timeout)", cfg.ProxyTimeout)
	}
	fmt.Printf("   Proxy Validation: %s\n", status)
	fmt.Println("=============================================================\n")

	// Initialize proxy pool with validation
	var proxyPool *proxy.Pool
	if cfg.ProxyEnabled {
		fmt.Println("🌐 Initializing High-Speed Proxy Pool...")
		proxyPool = proxy.NewPool(cfg)
		if err := proxyPool.Initialize(); err != nil {
			log.Printf("⚠️  Proxy initialization failed: %v (continuing without proxies)\n", err)
		}
	} else {
		fmt.Println("🌐 Direct Connection Mode (No Proxies)")
	}

	// Create context with cancellation
	ctx, cancel := context.WithCancel(context.Background())
	defer cancel()

	// Handle graceful shutdown
	sigChan := make(chan os.Signal, 1)
	signal.Notify(sigChan, os.Interrupt, syscall.SIGTERM)
	go func() {
		<-sigChan
		fmt.Println("\n\n⚠️  Interrupt signal received, shutting down gracefully...")
		cancel()
	}()

	// Create scraper
	s := scraper.New(cfg, proxyPool)

	// Start scraping with worker pool
	var wg sync.WaitGroup
	startTime := time.Now()

	fmt.Printf("\n🚀 Starting %d concurrent workers...\n\n", cfg.WorkerCount)

	if err := s.Start(ctx, &wg); err != nil {
		log.Fatalf("❌ Scraper failed: %v", err)
	}

	// Wait for completion
	wg.Wait()

	// Print summary
	elapsed := time.Since(startTime)
	stats := s.GetStats()

	fmt.Println("\n" + "=============================================================")
	fmt.Println("🏁 CRAWLER FINISHED!")
	fmt.Println("=============================================================")
	fmt.Printf("   ✅ Articles Scraped: %d\n", stats.ArticlesProcessed)
	fmt.Printf("   ✅ Total Runtime: %.2f seconds\n", elapsed.Seconds())
	fmt.Printf("   ✅ Speed: %.2f articles/sec\n", float64(stats.ArticlesProcessed)/elapsed.Seconds())
	fmt.Printf("   ✅ Backup: %s\n", stats.BackupFile)
	fmt.Println("=============================================================")
}
