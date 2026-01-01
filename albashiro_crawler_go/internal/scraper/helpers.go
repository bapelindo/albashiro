package scraper

import (
	"albashiro_crawler/internal/monitor"
	"encoding/json"
	"fmt"
	"os"
	"path/filepath"
	"sync/atomic"
	"time"
)

func (s *Scraper) updateMonitor() {
	s.titlesMu.Lock()
	titles := make([]string, len(s.latestTitles))
	copy(titles, s.latestTitles)
	s.titlesMu.Unlock()

	stats := monitor.Stats{
		ArticlesProcessed: int(atomic.LoadInt64(&s.articlesProcessed)),
		VectorsInserted:   0, // No vectors in ultra-fast mode
		LatestTitles:      titles,
	}

	if err := s.monitor.Update(stats); err != nil {
		// Silent fail - monitoring is not critical
	}
}

func (s *Scraper) autoSave(articles []*Article) {
	elapsed := time.Since(s.startTime).Hours()
	timestamp := time.Now().Format("20060102_150405")
	filename := fmt.Sprintf("autosave_%s_%darticles.json", timestamp, len(articles))

	s.saveBackupWithName(articles, filename)

	fmt.Printf("\n   ⏰ AUTO-SAVE: %s\n", filename)
	fmt.Printf("      Progress: %d articles\n", atomic.LoadInt64(&s.articlesProcessed))
	fmt.Printf("      Runtime: %.1fh / 11.0h\n\n", elapsed)

	s.lastSaveTime = time.Now()
}

func (s *Scraper) saveBackupWithName(articles []*Article, filename string) {
	path := filepath.Join(s.cfg.BackupDir, filename)
	data, err := json.MarshalIndent(articles, "", "  ")
	if err != nil {
		return
	}
	os.WriteFile(path, data, 0644)
	s.backupFile = path
}
