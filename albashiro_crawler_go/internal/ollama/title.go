package ollama

import (
	"bytes"
	"context"
	"encoding/json"
	"fmt"
	"net/http"
	"strings"
)

// GenerateTitle creates a clean, relevant title from article content
// Eliminates clickbait and ensures title accurately represents content
func (c *Client) GenerateTitle(ctx context.Context, originalTitle, content string) (string, error) {
	// Truncate content if too long (max 2000 chars for title generation)
	if len(content) > 2000 {
		content = content[:2000] + "..."
	}

	prompt := fmt.Sprintf(`Buatlah judul artikel yang jelas, informatif, dan relevan berdasarkan konten berikut.
Judul harus:
- Menggambarkan inti artikel dengan akurat
- Tidak clickbait atau sensasional
- Maksimal 10-15 kata
- Dalam Bahasa Indonesia yang formal

Judul Asli: %s

Konten:
%s

Judul yang Diperbaiki (tanpa tanda kutip):`, originalTitle, content)

	reqBody := GenerateRequest{
		Model:  c.model,
		Prompt: prompt,
		Stream: false,
		Options: map[string]interface{}{
			"temperature": 0.5, // Moderate temp for creative but accurate titles
			"num_gpu":     99,  // Force GPU offloading
			"num_thread":  2,
			"num_ctx":     1024,
			"num_predict": 50, // Short output for title
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

	// Clean up the generated title
	generatedTitle := strings.TrimSpace(genResp.Response)
	generatedTitle = strings.Trim(generatedTitle, `"'`) // Remove quotes if present

	// Fallback to original if generation failed
	if generatedTitle == "" || len(generatedTitle) < 5 {
		return originalTitle, nil
	}

	return generatedTitle, nil
}
