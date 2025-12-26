# Ollama Deployment for Albashiro

This directory contains files for deploying Ollama LLM to Google Cloud Run.

## Quick Start

### Prerequisites
1. Google Cloud account (free tier available)
2. Install Google Cloud SDK:
   ```bash
   # Windows
   https://cloud.google.com/sdk/docs/install
   
   # After install, initialize:
   gcloud init
   ```

### Deploy to Cloud Run

```bash
cd ollama
chmod +x deploy.sh
./deploy.sh
```

The script will:
1. Create GCP project (if needed)
2. Enable required APIs
3. Build Docker image with gemma2:2b model
4. Deploy to Cloud Run (Singapore region)
5. Output service URL

### Configuration

**Model**: gemma2:2b (2B parameters)
- Lightweight & fast (~500ms-2s response)
- Good Indonesian language support
- Low memory usage (4GB)

**Cloud Run Settings**:
- Memory: 4Gi
- CPU: 2
- Timeout: 300s
- Min instances: 0 (no idle cost)
- Max instances: 3 (cost control)
- Region: asia-southeast1 (Singapore)

### Cost Estimate

**Free Tier**: 2 million requests/month

**After Free Tier** (100 requests/day):
- ~$3-5/month
- $0 when idle (min-instances=0)

### Testing

```bash
# Test endpoint
curl https://your-service-url/api/tags

# Test chat
curl https://your-service-url/api/chat -d '{
  "model": "gemma2:2b",
  "messages": [
    {"role": "user", "content": "Halo, apa kabar?"}
  ]
}'
```

### Environment Variables

Add to Vercel:
```
OLLAMA_API_URL=https://ollama-api-xxxxx-uc.a.run.app
```

Add to `.env.local`:
```
OLLAMA_API_URL=https://ollama-api-xxxxx-uc.a.run.app
```

### Troubleshooting

**Build fails**:
- Check Docker is installed
- Verify gcloud authentication: `gcloud auth login`

**Deployment fails**:
- Enable billing in GCP console
- Check API quotas

**Slow responses**:
- First request after idle takes ~10-20s (cold start)
- Subsequent requests: ~500ms-2s
- Consider min-instances=1 for faster response (costs ~$20/month)

### Model Options

To use different model, edit `Dockerfile`:

```dockerfile
# Lighter (faster, less accurate)
RUN ollama pull gemma2:2b

# Heavier (slower, more accurate)
RUN ollama pull llama3.2:3b
RUN ollama pull qwen2.5:3b
```

### Monitoring

View logs:
```bash
gcloud run services logs read ollama-api --region asia-southeast1
```

View metrics:
```bash
gcloud run services describe ollama-api --region asia-southeast1
```
