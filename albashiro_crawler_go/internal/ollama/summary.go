package ollama

import (
	"bytes"
	"context"
	"encoding/json"
	"fmt"
	"net/http"
	"strings"
)

type GenerateRequest struct {
	Model   string                 `json:"model"`
	Prompt  string                 `json:"prompt"`
	Stream  bool                   `json:"stream"`
	Options map[string]interface{} `json:"options"`
}

type GenerateResponse struct {
	Response string `json:"response"`
}

// GenerateSummary creates a concise summary of an article using the AI model
// Summary is used for preview/search and RAG context
func (c *Client) GenerateSummary(ctx context.Context, title, content string) (string, error) {
	// Truncate content if too long (max 3000 chars for summary)
	if len(content) > 3000 {
		content = content[:3000] + "..."
	}

	prompt := fmt.Sprintf(`Buatlah ringkasan artikel psikologi berikut dalam 200-300 kata dalam Bahasa Indonesia:

Judul: %s

Konten:
%s

Ringkasan (200-300 kata):`, title, content)

	reqBody := GenerateRequest{
		Model:  c.model,
		Prompt: prompt,
		Stream: false,
		Options: map[string]interface{}{
			"temperature": 0.3, // Low temp for consistent summaries
			"num_gpu":     99,  // Force GPU offloading
			"num_thread":  2,
			"num_ctx":     1024,
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

	resp, err := c.httpClient.Do(req)
	if err != nil {
		return "", err
	}
	defer resp.Body.Close()

	if resp.StatusCode != 200 {
		return "", fmt.Errorf("ollama API error: HTTP %d", resp.StatusCode)
	}

	var genResp GenerateResponse
	if err := json.NewDecoder(resp.Body).Decode(&genResp); err != nil {
		return "", err
	}

	return strings.TrimSpace(genResp.Response), nil
}
