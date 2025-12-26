# Ollama gemma3:4b - Advanced Configuration

## Performance Tuning

### CPU Threads
Edit `OllamaService.php` line ~88:
```php
'num_thread' => 4,  // Adjust based on your CPU cores
```

**Recommendations**:
- 4 cores CPU: `num_thread => 2`
- 8 cores CPU: `num_thread => 4`
- 16+ cores CPU: `num_thread => 8`

### Context Window
```php
'num_ctx' => 2048,  // Context window size
```
- Larger = better memory, slower
- Smaller = faster, less context
- Default 2048 is balanced

### Temperature (Creativity)
```php
'temperature' => 0.7,  // 0.0-1.0
```
- **0.0-0.3**: Very factual, deterministic (good for prices/data)
- **0.4-0.7**: Balanced (RECOMMENDED)
- **0.8-1.0**: Creative, varied responses

### Max Tokens
```php
'num_predict' => 512,  // Max response length
```
- 256: Short responses
- 512: Medium (RECOMMENDED)
- 1024: Long, detailed responses

---

## System Prompt Optimization

### Current Prompt Structure
1. **Persona** - Islamic, empathetic
2. **Data** - Services, therapists, prices from database
3. **Rules** - Anti-hallucination, empathy for complaints
4. **Format** - Concise, max 3 paragraphs

### Tips for Better Responses
- ✅ Be specific in rules
- ✅ Give examples (good vs bad)
- ✅ Use bullet points for clarity
- ✅ Emphasize "JANGAN MENGARANG"

---

## Testing Scenarios

### 1. Factual Questions (No Creativity)
```
"berapa harga paket hipnoterapi anak?"
```
**Expected**: Exact price from database

### 2. Empathy Required
```
"saya cemas dan susah tidur, takut keluar rumah"
```
**Expected**: 
- Empathy first
- Validate feelings
- Recommend service
- Don't hard sell

### 3. Complex Questions
```
"saya punya trauma masa kecil dan sekarang susah percaya orang, terapi apa yang cocok?"
```
**Expected**:
- Understand multiple issues
- Recommend "Trauma & Luka Batin"
- Show empathy
- Explain why it's suitable

### 4. Hallucination Test
```
"ada paket keluarga atau paket premium?"
```
**Expected**: 
- "Tidak ada paket khusus seperti itu"
- Show available services only
- Suggest contact admin for custom packages

---

## Monitoring Performance

### Response Time Targets
- **gemma3:4b**: 1-3 seconds (normal)
- **First request**: 5-10 seconds (model loading)
- **Fallback to LocalAI**: < 100ms

### Quality Metrics
- ✅ No hallucination (100% accuracy)
- ✅ Natural Bahasa Indonesia
- ✅ Empathy in responses
- ✅ Concise (max 3 paragraphs)

### Check Ollama Status
```powershell
# List models
ollama list

# Check if Ollama is running
Get-Process ollama -ErrorAction SilentlyContinue

# View Ollama logs (if needed)
# Check Task Manager → Ollama
```

---

## Troubleshooting

### Slow Responses (> 5s)
1. Reduce `num_predict` to 256
2. Reduce `num_ctx` to 1024
3. Increase `num_thread` (if you have more CPU cores)

### Poor Quality Responses
1. Increase `temperature` to 0.8
2. Increase `num_predict` to 1024
3. Check system prompt clarity

### Hallucination Issues
1. Lower `temperature` to 0.5
2. Strengthen system prompt rules
3. Add more examples in prompt

### Out of Memory
1. Close other applications
2. Reduce `num_ctx` to 1024
3. Consider using gemma2:2b instead

---

## Advanced: Custom Model Parameters

Create `.env.local`:
```env
OLLAMA_API_URL=http://localhost:11434
OLLAMA_MODEL=gemma3:4b
OLLAMA_TEMPERATURE=0.7
OLLAMA_MAX_TOKENS=512
OLLAMA_THREADS=4
```

Then update `OllamaService.php` to read from env.

---

## Comparison: gemma2:2b vs gemma3:4b

| Metric | gemma2:2b | gemma3:4b |
|--------|-----------|-----------|
| Size | 1.6GB | 2.5GB |
| RAM | 4GB | 6-8GB |
| Speed | 500ms-2s | 1-3s |
| Quality | Good | **Excellent** |
| Indonesian | Good | **Better** |
| Context | 2048 | 2048 |
| Best For | Fast responses | **Quality responses** |

**Recommendation**: Use gemma3:4b for production (better quality worth the extra 1-2s)
