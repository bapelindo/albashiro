package monitor

import (
	"fmt"
	"os"
	"path/filepath"
	"strings"
	"time"
)

type Monitor struct {
	monitorDir      string
	startTime       time.Time
	maxArticles     int
	maxRuntimeHours float64
}

type Stats struct {
	ArticlesProcessed int
	VectorsInserted   int
	LatestTitles      []string
}

func New(monitorDir string, maxArticles int, maxRuntimeHours float64) *Monitor {
	return &Monitor{
		monitorDir:      monitorDir,
		startTime:       time.Now(),
		maxArticles:     maxArticles,
		maxRuntimeHours: maxRuntimeHours,
	}
}

func (m *Monitor) Update(stats Stats) error {
	os.MkdirAll(m.monitorDir, 0755)
	
	monitorFile := filepath.Join(m.monitorDir, "crawler_status.txt")
	
	elapsed := time.Since(m.startTime).Hours()
	remaining := m.maxRuntimeHours - elapsed
	articlesPerHour := float64(stats.ArticlesProcessed) / elapsed
	var eta float64
	if articlesPerHour > 0 {
		eta = float64(m.maxArticles-stats.ArticlesProcessed) / articlesPerHour
	}
	
	// Progress bar
	progress := float64(stats.ArticlesProcessed) / float64(m.maxArticles)
	barLength := 50
	filled := int(progress * float64(barLength))
	bar := strings.Repeat("█", filled) + strings.Repeat("░", barLength-filled)
	
	content := fmt.Sprintf(`╔══════════════════════════════════════════════════════════════╗
║          ALBASHIRO CRAWLER - REAL-TIME STATUS               ║
╚══════════════════════════════════════════════════════════════╝

⏰ Last Updated: %s

📊 PROGRESS
  Articles: %d / %d (%.1f%%)
  Vectors:  %d
  %s

⏱️  TIMING
  Runtime:  %.2fh / %.1fh
  Speed:    %.1f articles/hour
  ETA:      %.1f hours
  Remaining: %.1f hours

📈 LATEST ARTICLES
`,
		time.Now().Format("2006-01-02 15:04:05"),
		stats.ArticlesProcessed, m.maxArticles, progress*100,
		stats.VectorsInserted,
		bar,
		elapsed, m.maxRuntimeHours,
		articlesPerHour,
		eta,
		remaining,
	)
	
	// Add latest titles
	for i, title := range stats.LatestTitles {
		if len(title) > 60 {
			title = title[:60] + "..."
		}
		content += fmt.Sprintf("  [%d] %s\n", stats.ArticlesProcessed-len(stats.LatestTitles)+i+1, title)
	}
	
	content += "\n" + strings.Repeat("=", 64) + "\n"
	
	return os.WriteFile(monitorFile, []byte(content), 0644)
}

func (m *Monitor) CheckTimeout() (bool, float64) {
	elapsed := time.Since(m.startTime).Hours()
	remaining := m.maxRuntimeHours - elapsed
	
	// Less than 10 minutes remaining
	if remaining < 0.167 { // 10 minutes = 0.167 hours
		return true, remaining
	}
	return false, remaining
}
