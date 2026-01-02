package main

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"io/ioutil"
	"log"
	"os"
	"path/filepath"
	"strings"
	"time"

	_ "github.com/go-sql-driver/mysql"
)

type Article struct {
	ID             int          `json:"id"`
	URL            string       `json:"url"`
	OriginalTitle  string       `json:"original_title"`
	ProcessedTitle string       `json:"processed_title"`
	Content        string       `json:"content"`
	Summary        string       `json:"summary"` // NEW: AI-generated summary
	ContentLength  int          `json:"content_length"`
	ChunksTotal    int          `json:"chunks_total"`    // NEW: Total chunks
	ChunksRelevant int          `json:"chunks_relevant"` // NEW: Relevant chunks
	Vectors        []VectorData `json:"vectors"`
	Timestamp      string       `json:"timestamp"`
}

type VectorData struct {
	ChunkIndex int       `json:"chunk_index"` // NEW: Track chunk position
	ChunkText  string    `json:"chunk_text"`
	Embedding  []float64 `json:"embedding"`
}

type Config struct {
	Host     string
	Port     int
	User     string
	Password string
	Database string
}

func main() {
	fmt.Println("=============================================================")
	fmt.Println("📥 ALBASHIRO TIDB IMPORTER - GOLANG EDITION")
	fmt.Println("=============================================================\n")

	// Configuration
	cfg := Config{
		Host:     "gateway01.ap-northeast-1.prod.aws.tidbcloud.com",
		Port:     4000,
		User:     "4TnpUUxik5ZLHTT.root",
		Password: "LuCuUyXhfEy3EJVI",
		Database: "albashiro",
	}

	streamDir := "c:\\apache\\htdocs\\albashiro\\scraped_data\\backup\\stream"

	// Connect to TiDB
	db, err := connectTiDB(cfg)
	if err != nil {
		log.Fatalf("❌ Failed to connect to TiDB: %v", err)
	}
	defer db.Close()

	fmt.Println("✅ Connected to TiDB")
	fmt.Printf("📂 Watching directory: %s\n\n", streamDir)

	// Process existing files first
	processExistingFiles(db, streamDir)

	// Watch for new files
	fmt.Println("👀 Watching for new files... (Ctrl+C to stop)")
	watchDirectory(db, streamDir)
}

func connectTiDB(cfg Config) (*sql.DB, error) {
	// EXACT COPY FROM test_tidb_connection/main.go (Verified Working)
	dsn := fmt.Sprintf("%s:%s@tcp(%s:%d)/%s?tls=skip-verify",
		cfg.User, cfg.Password, cfg.Host, cfg.Port, cfg.Database)

	db, err := sql.Open("mysql", dsn)
	if err != nil {
		return nil, err
	}

	// Test connection
	if err := db.Ping(); err != nil {
		return nil, err
	}

	return db, nil
}

func processExistingFiles(db *sql.DB, dir string) {
	files, err := ioutil.ReadDir(dir)
	if err != nil {
		log.Printf("⚠️  Failed to read directory: %v", err)
		return
	}

	for _, file := range files {
		if !file.IsDir() && strings.HasSuffix(file.Name(), ".json") {
			filePath := filepath.Join(dir, file.Name())
			processFile(db, filePath)
		}
	}
}

func watchDirectory(db *sql.DB, dir string) {
	processed := make(map[string]bool)

	for {
		files, err := ioutil.ReadDir(dir)
		if err != nil {
			log.Printf("⚠️  Failed to read directory: %v", err)
			time.Sleep(5 * time.Second)
			continue
		}

		for _, file := range files {
			if file.IsDir() || !strings.HasSuffix(file.Name(), ".json") {
				continue
			}

			filePath := filepath.Join(dir, file.Name())
			if !processed[filePath] {
				if processFile(db, filePath) {
					processed[filePath] = true
				}
			}
		}

		time.Sleep(2 * time.Second)
	}
}

func processFile(db *sql.DB, filePath string) bool {
	fmt.Printf("⚡ Processing: %s\n", filepath.Base(filePath))

	// Read file
	data, err := ioutil.ReadFile(filePath)
	if err != nil {
		fmt.Printf("   ❌ Failed to read file: %v\n", err)
		return false
	}

	// Parse JSON
	var articles []Article

	// Try unmarshalling as array first (legacy format)
	if err := json.Unmarshal(data, &articles); err != nil {
		// If array fails, try unmarshalling as single object (stream format)
		var singleArticle Article
		if errSingle := json.Unmarshal(data, &singleArticle); errSingle == nil {
			articles = []Article{singleArticle}
		} else {
			// Both failed
			fmt.Printf("   ❌ Failed to parse JSON (Array: %v | Object: %v)\n", err, errSingle)
			return false
		}
	}

	if len(articles) == 0 {
		fmt.Println("   ⚠️  No articles in file")
		return false
	}

	// Import to TiDB
	totalInserted := 0
	for _, article := range articles {
		inserted := importArticle(db, article)
		totalInserted += inserted
	}

	fmt.Printf("   ✅ Imported %d vectors\n", totalInserted)

	// Move file to trash/processed instead of deleting (safety measure)
	archiveDir := filepath.Join(filepath.Dir(filePath), "trash", "processed")
	os.MkdirAll(archiveDir, 0755)

	archivePath := filepath.Join(archiveDir, filepath.Base(filePath))
	if err := os.Rename(filePath, archivePath); err != nil {
		fmt.Printf("   ⚠️  Failed to move file to trash: %v\n", err)
	} else {
		fmt.Printf("   🗑️  Moved to trash: %s → trash/processed/%s\n", filepath.Base(filePath), filepath.Base(filePath))
	}

	return true
}

func importArticle(db *sql.DB, article Article) int {
	inserted := 0

	// Prepare statement
	stmt, err := db.Prepare(`
		INSERT INTO knowledge_vectors 
		(source_table, source_id, article_id, content_text, embedding)
		VALUES (?, ?, ?, ?, ?)
	`)
	if err != nil {
		fmt.Printf("   ❌ Failed to prepare statement: %v\n", err)
		return 0
	}
	defer stmt.Close()

	// Insert each vector
	for _, vector := range article.Vectors {
		// Convert embedding to JSON string
		embeddingJSON, err := json.Marshal(vector.Embedding)
		if err != nil {
			continue
		}

		// Truncate title to 500 chars (VARCHAR limit)
		title := article.ProcessedTitle
		if len(title) > 500 {
			title = title[:500]
		}

		// Execute insert
		_, err = stmt.Exec(
			title,                 // source_table (article title)
			vector.ChunkIndex+1,   // source_id (use ChunkIndex from vector)
			article.ID,            // article_id
			vector.ChunkText,      // content_text
			string(embeddingJSON), // embedding
		)

		if err != nil {
			fmt.Printf("   ⚠️  Failed to insert chunk %d: %v\n", vector.ChunkIndex+1, err)
			continue
		}

		inserted++
	}

	return inserted
}
