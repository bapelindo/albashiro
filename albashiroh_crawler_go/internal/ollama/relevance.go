package ollama

import (
	"bytes"
	"context"
	"encoding/json"
	"fmt"
	"net/http"
)

type RelevanceRequest struct {
	Model   string                 `json:"model"`
	Prompt  string                 `json:"prompt"`
	Stream  bool                   `json:"stream"`
	Options map[string]interface{} `json:"options"`
}

type RelevanceResponse struct {
	Response string `json:"response"`
}

func (c *Client) CheckRelevance(ctx context.Context, title, content string) (bool, error) {
	// Limit content preview
	contentPreview := content
	if len(contentPreview) > 800 {
		contentPreview = contentPreview[:800]
	}

	prompt := fmt.Sprintf(`TITLE: "%s"
CONTENT PREVIEW: "%s"

Is this article RELEVANT to mental health? (YES/NO)`, title, contentPreview)

	reqBody := RelevanceRequest{
		Model:  c.model,
		Prompt: prompt,
		Stream: false,
		Options: map[string]interface{}{
			"num_gpu":     99, // Force FULL GPU offloading
			"temperature": 0.1,
			"num_predict": 10,
			"num_ctx":     1024,
		},
	}

	jsonData, err := json.Marshal(reqBody)
	if err != nil {
		return true, err // Default to accept on error
	}

	req, err := http.NewRequestWithContext(ctx, "POST", c.baseURL+"/api/generate", bytes.NewBuffer(jsonData))
	if err != nil {
		return true, err
	}
	req.Header.Set("Content-Type", "application/json")

	// Use shared client to reuse connections (Keep-Alive)
	resp, err := c.httpClient.Do(req)
	if err != nil {
		return true, err // Default to accept on timeout
	}
	defer resp.Body.Close()

	if resp.StatusCode != 200 {
		return true, fmt.Errorf("ollama API error: HTTP %d", resp.StatusCode)
	}

	var relResp RelevanceResponse
	if err := json.NewDecoder(resp.Body).Decode(&relResp); err != nil {
		return true, err
	}

	// Parse response
	answer := relResp.Response
	if len(answer) > 0 {
		firstWord := answer[:min(10, len(answer))]
		if contains(firstWord, "YES") || contains(firstWord, "yes") {
			return true, nil
		}
		if contains(firstWord, "NO") || contains(firstWord, "no") {
			return false, nil
		}
	}

	return true, nil // Default to accept if unclear
}

func contains(s, substr string) bool {
	return len(s) >= len(substr) && s[:len(substr)] == substr
}

func min(a, b int) int {
	if a < b {
		return a
	}
	return b
}
