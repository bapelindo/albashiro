package ollama

import (
	"bytes"
	"context"
	"encoding/json"
	"fmt"
	"net/http"
	"strings"
	"time"
)

type GenerateRequest struct {
	Model   string                 `json:"model"`
	Prompt  string                 `json:"prompt"`
	Stream  bool                   `json:"stream"`
	Options map[string]interface{} `json:"options"`
}

type GenerateResponse struct {
	Response string `json:"response"`
	Done     bool   `json:"done"`
}

// GenerateSummary creates a concise summary of an article using the AI model
// Summary is used for preview/search and RAG context
func (c *Client) GenerateSummary(ctx context.Context, title, content string) (string, error) {
	// Truncate content if too long (max 2000 chars to fit in 1024 ctx window)
	if len(content) > 2000 {
		content = content[:2000] + "..."
	}

	prompt := fmt.Sprintf(`Buatlah ringkasan artikel psikologi berikut dalam 150-200 kata dalam Bahasa Indonesia:

Judul: %s

Konten:
%s

Ringkasan (150-200 kata):`, title, content)

	// Debug log: Start
	// fmt.Printf("      [DEBUG] Summary Start: %s\n", title[:min(20, len(title))])

	// Debug log: Start
	fmt.Printf("      [DEBUG] Summary Start: %s\n", title[:min(20, len(title))])

	var reqBody GenerateRequest
	reqBody = GenerateRequest{
		Model:  c.model,
		Prompt: prompt,
		Stream: false, // Disable streaming to detect DONE signal immediately
		Options: map[string]interface{}{
			"temperature": 0.3,
			"num_gpu":     99,
			"num_thread":  1,
			"num_ctx":     4096, // Increased to avoid KV cache shift crashes
		},
	}

	jsonData, err := json.Marshal(reqBody)
	if err != nil {
		return "", err
	}

	req, err := http.NewRequestWithContext(ctx, "POST", c.baseURL+"/api/generate", bytes.NewBuffer(jsonData))
	if err != nil {
		return "", err
	}
	req.Header.Set("Content-Type", "application/json")
	req.Close = true // Force close connection

	startTime := time.Now()
	fmt.Printf("      [DEBUG] Sent Request... (T: %s)\n", time.Since(startTime))
	resp, err := c.httpClient.Do(req)
	if err != nil {
		fmt.Printf("      [DEBUG] Fail: %v (T: %s)\n", err, time.Since(startTime))
		return "", err
	}
	defer resp.Body.Close()

	fmt.Printf("      [DEBUG] Headers Recv (T: %s)\n", time.Since(startTime))

	if resp.StatusCode != 200 {
		return "", fmt.Errorf("ollama API error: HTTP %d", resp.StatusCode)
	}

	// Parse non-streaming response
	var genResp GenerateResponse
	if err := json.NewDecoder(resp.Body).Decode(&genResp); err != nil {
		return "", err
	}

	result := strings.TrimSpace(genResp.Response)
	fmt.Printf("      [DEBUG] Generated (Total Length: %d chars): %s...\n", len(result), result[:min(100, len(result))]) // Show start of content
	return result, nil
}
