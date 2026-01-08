package main

import (
	"database/sql"
	"fmt"
	"log"

	_ "github.com/go-sql-driver/mysql"
)

type Config struct {
	Host     string
	Port     int
	User     string
	Password string
	Database string
}

func main() {
	fmt.Println("🔍 Testing TiDB Connection...")

	// Configuration (Copied from cmd/importer/main.go)
	cfg := Config{
		Host:     "gateway01.ap-northeast-1.prod.aws.tidbcloud.com",
		Port:     4000,
		User:     "4TnpUUxik5ZLHTT.root",
		Password: "hweuQGiW36RtoJLw",
		Database: "albashiro",
	}

	// Connect to TiDB
	db, err := connectTiDB(cfg)
	if err != nil {
		log.Fatalf("❌ Failed to connect to TiDB: %v", err)
	}
	defer db.Close()

	fmt.Println("✅ Connection Successful!")
	fmt.Println("📊 Running connectivity check (Ping)...")

	if err := db.Ping(); err != nil {
		log.Fatalf("❌ Ping failed: %v", err)
	}
	fmt.Println("✅ Ping Successful!")

	// Optional: Check if table exists
	_, err = db.Exec("SELECT 1 FROM knowledge_vectors LIMIT 1")
	if err != nil {
		fmt.Printf("⚠️  Warning: Could not query knowledge_vectors table (might be empty or missing): %v\n", err)
	} else {
		fmt.Println("✅ Table 'knowledge_vectors' is accessible.")
	}
}

func connectTiDB(cfg Config) (*sql.DB, error) {
	// Use skip-verify for TLS (simpler than custom cert)
	dsn := fmt.Sprintf("%s:%s@tcp(%s:%d)/%s?tls=skip-verify",
		cfg.User, cfg.Password, cfg.Host, cfg.Port, cfg.Database)

	db, err := sql.Open("mysql", dsn)
	if err != nil {
		return nil, err
	}

	return db, nil
}
