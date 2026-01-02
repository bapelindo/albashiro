package config

import (
	"os"
	"strconv"
)

type Config struct {
	// Scraping settings
	MaxArticles  int
	WorkerCount  int
	ChunkSize    int
	ChunkOverlap int

	// Proxy settings
	ProxyEnabled bool
	ProxyTimeout int // seconds
	ProxySources []string

	// Ollama settings
	OllamaURL            string
	OllamaModel          string
	OllamaEmbeddingModel string
	UseOllama            bool
	UseAIJudge           bool // Enable per-chunk AI judging
	UseAISummary         bool // Enable AI summary generation

	// Export settings
	BackupDir  string
	StreamDir  string
	MonitorDir string

	// Seeds
	Seeds []string
}

func Load() *Config {
	return &Config{
		MaxArticles:  getEnvInt("MAX_ARTICLES", 5000),
		WorkerCount:  getEnvInt("WORKER_COUNT", 1), // 1 worker to avoid Ollama bottleneck
		ChunkSize:    getEnvInt("CHUNK_SIZE", 1000),
		ChunkOverlap: getEnvInt("CHUNK_OVERLAP", 100),

		ProxyEnabled: getEnvBool("ENABLE_PROXY", false), // ENABLED - User insists on Proxy
		ProxyTimeout: getEnvInt("PROXY_TIMEOUT", 5),     // Increased to 20s (Free proxies are slow)
		ProxySources: []string{
			// === MASSIVE GLOBAL POOLS (QUANTITY OVER QUALITY) ===
			// TheSpeedX (Thousands of HTTP proxies)
			"https://raw.githubusercontent.com/TheSpeedX/SOCKS-List/master/http.txt",

			// Monosans (Frequently updated)
			"https://raw.githubusercontent.com/monosans/proxy-list/main/proxies/http.txt",

			// ShiftyTR (Aggregated list)
			"https://raw.githubusercontent.com/ShiftyTR/Proxy-List/master/http.txt",

			// PrxChk
			"https://raw.githubusercontent.com/prxchk/proxy-list/main/http.txt",

			// Sunny9577 (Mixed)
			"https://raw.githubusercontent.com/sunny9577/proxy-scraper/master/proxies.txt",

			// RoosterKid (HTTPS Raw)
			"https://raw.githubusercontent.com/roosterkid/openproxylist/main/HTTPS_RAW.txt",

			// Uproxy
			"https://raw.githubusercontent.com/uproxy/uproxy/main/http.txt",

			// Zloi-user
			"https://raw.githubusercontent.com/Zloi-user/hideip.me/main/http.txt",

			// ProxyScrape (SG Filtered - Backup)
			"https://api.proxyscrape.com/v2/?request=get&protocol=http&timeout=10000&country=SG&ssl=all&anonymity=all",
		},

		OllamaURL:            getEnv("OLLAMA_URL", "http://127.0.0.1:11434"),
		OllamaModel:          getEnv("OLLAMA_MODEL", "albashiro-crawler"),
		OllamaEmbeddingModel: getEnv("OLLAMA_EMBEDDING_MODEL", "all-minilm"), // 384-dim (matches chatbot)
		UseOllama:            getEnvBool("USE_OLLAMA", true),
		UseAIJudge:           getEnvBool("USE_AI_JUDGE", false),  // DISABLED - too strict, semantic is enough
		UseAISummary:         getEnvBool("USE_AI_SUMMARY", true), // ENABLED for embedding

		BackupDir:  getEnv("BACKUP_DIR", "c:\\apache\\htdocs\\albashiro\\scraped_data\\backup"),
		StreamDir:  getEnv("STREAM_DIR", "c:\\apache\\htdocs\\albashiro\\scraped_data\\backup\\stream"),
		MonitorDir: getEnv("MONITOR_DIR", "c:\\apache\\htdocs\\albashiro\\scraped_data\\monitor"),

		Seeds: loadSeeds(),
	}
}

func getEnv(key, defaultValue string) string {
	if value := os.Getenv(key); value != "" {
		return value
	}
	return defaultValue
}

func getEnvInt(key string, defaultValue int) int {
	if value := os.Getenv(key); value != "" {
		if intVal, err := strconv.Atoi(value); err == nil {
			return intVal
		}
	}
	return defaultValue
}

func getEnvBool(key string, defaultValue bool) bool {
	if value := os.Getenv(key); value != "" {
		if boolVal, err := strconv.ParseBool(value); err == nil {
			return boolVal
		}
	}
	return defaultValue
}

// loadSeeds is defined in seeds.go
