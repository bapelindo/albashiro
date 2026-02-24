package ollama

import (
	"bytes"
	"context"
	"encoding/json"
	"fmt"
	"net/http"
	"time"
)

type Client struct {
	baseURL        string
	model          string
	embeddingModel string
	httpClient     *http.Client
}

type EmbeddingRequest struct {
	Model   string                 `json:"model"`
	Prompt  string                 `json:"prompt"`
	Options map[string]interface{} `json:"options,omitempty"`
}

type EmbeddingResponse struct {
	Embedding []float64 `json:"embedding"`
}

func NewClient(baseURL, model, embeddingModel string) *Client {
	return &Client{
		baseURL:        baseURL,
		model:          model,
		embeddingModel: embeddingModel,
		httpClient: &http.Client{
			Timeout: 300 * time.Second, // 2 minutes
			Transport: &http.Transport{
				Proxy:             nil,  // Force NO PROXY
				DisableKeepAlives: true, // Prevent connection reuse hanging
			},
		},
	}
}

func (c *Client) GenerateEmbedding(ctx context.Context, text string) ([]float64, error) {
	// Limit text to ~400 chars for all-MiniLM
	if len(text) > 400 {
		text = text[:400]
	}

	reqBody := EmbeddingRequest{
		Model:  c.embeddingModel,
		Prompt: text,
		Options: map[string]interface{}{
			"num_gpu":  99,   // Use GPU for embeddings
			"use_mmap": true, // Memory mapping for efficiency
		},
	}

	jsonData, err := json.Marshal(reqBody)
	if err != nil {
		return nil, err
	}

	req, err := http.NewRequestWithContext(ctx, "POST", c.baseURL+"/api/embeddings", bytes.NewBuffer(jsonData))
	if err != nil {
		return nil, err
	}
	req.Header.Set("Content-Type", "application/json")

	resp, err := c.httpClient.Do(req)
	if err != nil {
		return nil, err
	}
	defer resp.Body.Close()

	if resp.StatusCode != 200 {
		return nil, fmt.Errorf("ollama API error: HTTP %d", resp.StatusCode)
	}

	var embResp EmbeddingResponse
	if err := json.NewDecoder(resp.Body).Decode(&embResp); err != nil {
		return nil, err
	}

	// Validate dimensions (all-MiniLM should be 384)
	if len(embResp.Embedding) != 384 {
		return nil, fmt.Errorf("unexpected embedding dimension: %d (expected 384)", len(embResp.Embedding))
	}

	return embResp.Embedding, nil
}

func (c *Client) Ping() error {
	resp, err := c.httpClient.Get(c.baseURL + "/api/tags")
	if err != nil {
		return err
	}
	defer resp.Body.Close()

	if resp.StatusCode != 200 {
		return fmt.Errorf("ollama not responding: HTTP %d", resp.StatusCode)
	}

	return nil
}
