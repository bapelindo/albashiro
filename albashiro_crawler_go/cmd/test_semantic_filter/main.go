package main

import (
	"albashiro_crawler/internal/ollama"
	"context"
	"fmt"
	"math"
	"time"
)

func main() {
	fmt.Println("🧠 Testing Semantic Link Filtering (Ollama + All-MiniLM)...")

	// 1. Setup Ollama
	cfgURL := "http://localhost:11434"
	cfgModel := "albashiro-crawler"
	cfgEmbed := "all-minilm"

	client := ollama.NewClient(cfgURL, cfgModel, cfgEmbed)

	if err := client.Ping(); err != nil {
		fmt.Printf("❌ Ollama not connected: %v\n", err)
		return
	}
	fmt.Println("✅ Ollama Connected")

	// 2. Generate Topic Embedding (Strict Clinical Focus)
	// Removed generic terms like "news", "article", "berita" to avoid matching "Pengumuman", "Jadwal". Added research/journal terms.
	topicText := "depression anxiety bipolar schizophrenia mental health disorder therapy counseling psychologist psychiatrist psychology research journal study gangguan jiwa depresi kecemasan psikolog psikiater pemulihan batin jurnal penelitian riset psikologi"
	fmt.Printf("📊 Generating Topic Embedding for: '%s'...\n", topicText)

	topicVec, err := client.GenerateEmbedding(context.Background(), topicText)
	if err != nil {
		fmt.Printf("❌ Failed to generate topic embedding: %v\n", err)
		return
	}

	// 3. Test Cases (Real-world examples)
	testLinks := []string{
		// 🇮🇩 INDONESIAN - RELEVANT (Real from UI/UGM/Pijar)
		"Mencegah Meningkatnya Kasus Gangguan Jiwa",
		"Peran Psikologi dalam Penanggulangan Bencana",
		"Mengapa Gen Z Lebih Rentan Terkena Depresi?",
		"Psikologi Islam: Solusi Ketenangan Jiwa Modern",
		"Pentingnya Self-Care untuk Kesehatan Mental Ibu",

		// User Reported False Negatives (Should be Accepted)
		"Psychology of Traffic, Urban Society, and Psycholo",
		"Mindfulness and Contemplative Psychology Laborator",
		"Laboratorium Psikologi Politik",
		"Jurnal Ilmiah",
		"Jurnal Psikologi Sosial",

		// 🇮🇩 INDONESIAN - JUNK (Real from UI/UGM)
		"Struktur Organisasi Fakultas",
		"Visi dan Misi Program Studi",
		"Jadwal Ujian Akhir Semester",
		"Sistem Informasi Akademik (SIAK)",
		"Pengumuman Hasil Seleksi Masuk",

		// 🇬🇧 ENGLISH - RELEVANT (Real from PsychToday/WHO)
		"The Link Between Anxiety and Sleep Disorders",
		"Understanding Bipolar Disorder Symptoms",
		"How Cognitive Behavioral Therapy Works",
		"World Mental Health Day 2024 Theme",
		"The Impact of Social Media on Teen Mental Health",

		// 🇬🇧 ENGLISH - JUNK (Real from General Sites)
		"Privacy Policy and Terms of Service",
		"Login to your Account",
		"Subscribe to our Newsletter",
		"Contact Us for More Information",
		"Site Map and Navigation",
	}

	fmt.Println("\n⚖️  SCORING RESULTS (Threshold > 0.20 = ACCEPT):")
	fmt.Println("---------------------------------------------------")

	for _, text := range testLinks {
		vec, err := client.GenerateEmbedding(context.Background(), text)
		if err != nil {
			fmt.Printf("Error: %v\n", err)
			continue
		}

		score := cosineSimilarity(topicVec, vec)

		status := "⛔ REJECT"
		if score > 0.26 {
			status = "✅ ACCEPT"
		}

		fmt.Printf("[%s] Score: %.4f | %s\n", status, score, text)
		time.Sleep(50 * time.Millisecond) // Prevent output interleaving
	}
}

func cosineSimilarity(a, b []float64) float64 {
	if len(a) != len(b) {
		return 0
	}

	dotProduct := 0.0
	normA := 0.0
	normB := 0.0

	for i := 0; i < len(a); i++ {
		dotProduct += a[i] * b[i]
		normA += a[i] * a[i]
		normB += b[i] * b[i]
	}

	if normA == 0 || normB == 0 {
		return 0
	}

	return dotProduct / (math.Sqrt(normA) * math.Sqrt(normB))
}
