package main

import (
	"context"
	"database/sql"
	"encoding/json"
	"flag"
	"fmt"
	"log"
	"strings"
	"time"

	"albashiro_crawler/internal/ollama"

	_ "github.com/go-sql-driver/mysql"
)

// Config holds the database connection details
type Config struct {
	Host     string
	Port     int
	User     string
	Password string
	Database string
}

func main() {
	// 0. Parse Flags
	queryPtr := flag.String("q", "", "Search query for vector search (e.g. 'harga terapi')")
	dumpPtr := flag.Bool("dump", true, "Dump distinct source tables and recent content")
	flag.Parse()

	fmt.Println("🔍 Albashiro Vector Verification Tool")
	fmt.Println("========================================")

	// 1. Configuration (Matching cmd/test_tidb_connection/main.go)
	cfg := Config{
		Host:     "gateway01.ap-northeast-1.prod.aws.tidbcloud.com",
		Port:     4000,
		User:     "4TnpUUxik5ZLHTT.root",
		Password: "hweuQGiW36RtoJLw",
		Database: "albashiro",
	}

	// 2. Connect to TiDB
	fmt.Printf("🔌 Connecting to TiDB (%s)...\n", cfg.Host)
	db, err := connectTiDB(cfg)
	if err != nil {
		log.Fatalf("❌ Failed to connect: %v", err)
	}
	defer db.Close()
	fmt.Println("✅ Connected!")

	// 3. Content Dump Mode
	if *dumpPtr {
		fmt.Println("\n📊 [MODE] CONTENT ANALYSIS")
		fmt.Println("----------------------------------------")
		analyzeContent(db)
	}

	// 4. Vector Search Mode
	if *queryPtr != "" {
		fmt.Println("\n🔎 [MODE] VECTOR SEARCH")
		fmt.Println("----------------------------------------")
		performVectorSearch(db, *queryPtr)
	} else if !*dumpPtr { // Default if no flags
		fmt.Println("\n🔎 [MODE] DEFAULT SEARCH TEST (Query: 'kepribadian')")
		performVectorSearch(db, "kepribadian")
	}
}

func connectTiDB(cfg Config) (*sql.DB, error) {
	dsn := fmt.Sprintf("%s:%s@tcp(%s:%d)/%s?tls=skip-verify",
		cfg.User, cfg.Password, cfg.Host, cfg.Port, cfg.Database)
	return sql.Open("mysql", dsn)
}

func analyzeContent(db *sql.DB) {
	// A. Check Source Tables
	fmt.Println("1️⃣  Distinct Source Tables:")
	rows, err := db.Query("SELECT DISTINCT source_table, count(*) FROM knowledge_vectors GROUP BY source_table")
	if err != nil {
		log.Printf("   ❌ Error querying sources: %v", err)
	} else {
		defer rows.Close()
		hasRows := false
		for rows.Next() {
			hasRows = true
			var source string
			var count int
			if err := rows.Scan(&source, &count); err != nil {
				log.Printf("   ⚠️ Scan error: %v", err)
				continue
			}
			fmt.Printf("   • %-15s : %d vectors\n", source, count)
		}
		if !hasRows {
			fmt.Println("   ⚠️ No data found in knowledge_vectors table.")
		}
	}

	// B. Dump Recent Entries (General)
	fmt.Println("\n2️⃣  Latest 5 Entries (Any Type):")
	dumpEntries(db, "SELECT source_table, source_id, left(content_text, 100) FROM knowledge_vectors ORDER BY id DESC LIMIT 5")

	// C. Single Vector Inspection (User Request)
	fmt.Println("\n3️⃣  Single Vector Inspection (Detail):")

	var source, id, content, embeddingStr string
	err = db.QueryRow("SELECT source_table, source_id, content_text, embedding FROM knowledge_vectors LIMIT 1").Scan(&source, &id, &content, &embeddingStr)
	if err != nil {
		log.Printf("   ❌ Error: %v", err)
	} else {
		fmt.Printf("   📌 Source  : %s\n", source)
		fmt.Printf("   🆔 ID      : %s\n", id)
		fmt.Printf("   📄 Content : %s\n", content) // Full content

		// Parse vector to show length/preview
		var vec []float64
		if err := json.Unmarshal([]byte(embeddingStr), &vec); err == nil {
			fmt.Printf("   🔢 Vector  : [%d dimensions] %.4f, %.4f, %.4f ...\n", len(vec), vec[0], vec[1], vec[2])
		} else {
			fmt.Printf("   🔢 Vector  : (Raw String) %s...\n", embeddingStr[:50])
		}
	}
}

func dumpEntries(db *sql.DB, query string) {
	rows, err := db.Query(query)
	if err != nil {
		log.Printf("   ❌ Error: %v", err)
		return
	}
	defer rows.Close()

	count := 0
	for rows.Next() {
		count++
		var source, id, content string
		if err := rows.Scan(&source, &id, &content); err != nil {
			log.Printf("   ⚠️ Scan error: %v", err)
			continue
		}
		// Clean newlines for display
		content = strings.ReplaceAll(content, "\n", " ")
		if len(content) > 80 {
			content = content[:80] + "..."
		}
		fmt.Printf("   [%s] ID:%-5s | %s\n", source, id, content)
	}
	if count == 0 {
		fmt.Println("   (No entries found)")
	}
}

func performVectorSearch(db *sql.DB, queryText string) {
	fmt.Printf("   ❓ Query: \"%s\"\n", queryText)

	// A. Initialize Ollama
	client := ollama.NewClient("http://127.0.0.1:11434", "albashiro", "all-minilm")

	// Fast ping
	if err := client.Ping(); err != nil {
		log.Printf("   ❌ Ollama not reachable: %v", err)
		return
	}

	// B. Generate Embedding
	fmt.Print("   🧠 Generating embedding... ")
	start := time.Now()
	embedding, err := client.GenerateEmbedding(context.Background(), queryText)
	if err != nil {
		fmt.Printf("❌ Failed: %v\n", err)
		return
	}
	fmt.Printf("✅ Done (%dms)\n", time.Since(start).Milliseconds())

	// C. Search DB
	fmt.Println("   🔍 Searching TiDB...")

	embeddingJSON, _ := json.Marshal(embedding)
	vecStr := string(embeddingJSON)

	// SQL for Cosine Distance
	sqlQuery := `SELECT source_table, source_id, content_text, 1 - VEC_COSINE_DISTANCE(embedding, ?) AS score 
	             FROM knowledge_vectors 
	             ORDER BY score DESC 
	             LIMIT 5`

	rows, err := db.Query(sqlQuery, vecStr)
	if err != nil {
		log.Fatalf("   ❌ Search failed: %v", err)
	}
	defer rows.Close()

	count := 0
	for rows.Next() {
		count++
		var source, id, content string
		var score float64
		if err := rows.Scan(&source, &id, &content, &score); err != nil {
			log.Printf("   ⚠️ Scan error: %v", err)
			continue
		}

		fmt.Printf("\n   🏆 Rank #%d (Score: %.4f)\n", count, score)
		fmt.Printf("      Source: %s (ID: %s)\n", source, id)
		fmt.Printf("      Content: %s\n", limitText(content, 200))
	}

	if count == 0 {
		fmt.Println("   ⚠️ No matches found.")
	}
}

func limitText(text string, max int) string {
	if len(text) <= max {
		return text
	}
	return text[:max] + "..."
}
