# Google Cloud Run + Ollama Deployment Guide

## Overview
This guide will help you deploy Ollama LLM to Google Cloud Run for the Albashiro chatbot.

**Benefits**:
- ✅ True AI with natural language understanding
- ✅ Bahasa Indonesia support (gemma2:2b model)
- ✅ Auto-scaling (pay per request)
- ✅ ~$3-5/month cost (free tier: 2M requests)
- ✅ Auto-fallback to LocalAI if down

---

## Prerequisites

### 1. Google Cloud Account
- Sign up: https://cloud.google.com/free
- Free tier includes: 2 million requests/month
- Credit card required (won't be charged unless you exceed free tier)

### 2. Install Google Cloud SDK

**Windows**:
```powershell
# Download installer
https://cloud.google.com/sdk/docs/install

# After install, open new terminal and initialize
gcloud init
```

**Mac/Linux**:
```bash
curl https://sdk.cloud.google.com | bash
exec -l $SHELL
gcloud init
```

### 3. Authenticate
```bash
gcloud auth login
gcloud auth application-default login
```

---

## Deployment Steps

### Step 1: Navigate to Ollama Directory
```bash
cd c:\apache\htdocs\albashiro\ollama
```

### Step 2: Make Deploy Script Executable (Mac/Linux)
```bash
chmod +x deploy.sh
```

### Step 3: Run Deployment
```bash
# Windows (Git Bash or WSL)
bash deploy.sh

# Mac/Linux
./deploy.sh
```

The script will:
1. Create GCP project `albashiro-ai`
2. Enable required APIs (Cloud Run, Cloud Build)
3. Build Docker image with gemma2:2b model (~5-10 minutes)
4. Deploy to Cloud Run in Singapore region
5. Output service URL

### Step 4: Copy Service URL
After deployment, you'll see:
```
Service URL: https://ollama-api-xxxxx-uc.a.run.app
```

**Copy this URL** - you'll need it for environment variables.

---

## Configuration

### For Vercel (Production)

1. Go to: https://vercel.com/dashboard
2. Select `albashiro` project
3. Go to **Settings** → **Environment Variables**
4. Add new variable:
   - **Name**: `OLLAMA_API_URL`
   - **Value**: `https://ollama-api-xxxxx-uc.a.run.app`
   - **Environment**: Production, Preview, Development
5. Click **Save**
6. Redeploy: **Deployments** → **...** → **Redeploy**

### For Local Development

Add to `.env.local`:
```env
OLLAMA_API_URL=https://ollama-api-xxxxx-uc.a.run.app
```

---

## Testing

### Test 1: Verify Ollama is Running
```bash
curl https://ollama-api-xxxxx-uc.a.run.app/api/tags
```

Expected response:
```json
{
  "models": [
    {
      "name": "gemma2:2b",
      "modified_at": "2025-12-26T...",
      "size": 1678447520
    }
  ]
}
```

### Test 2: Test Chat Endpoint
```bash
curl https://ollama-api-xxxxx-uc.a.run.app/api/chat -d '{
  "model": "gemma2:2b",
  "messages": [
    {"role": "user", "content": "Halo, apa kabar?"}
  ],
  "stream": false
}'
```

### Test 3: Test from PHP (Localhost)
```bash
cd c:\apache\htdocs\albashiro
php test_ollama.php
```

Expected: Should show responses from Ollama or fallback to LocalAI.

### Test 4: Test from Website
1. Open: http://localhost/albashiro
2. Open chatbot
3. Send: "berapa harga paket?"
4. Verify: Should get natural response with real prices

---

## Monitoring

### View Logs
```bash
gcloud run services logs read ollama-api --region asia-southeast1 --limit 50
```

### View Metrics
```bash
gcloud run services describe ollama-api --region asia-southeast1
```

### Cloud Console
https://console.cloud.google.com/run?project=albashiro-ai

---

## Cost Management

### Current Configuration
- **Min instances**: 0 (no idle cost)
- **Max instances**: 3 (prevent runaway costs)
- **Memory**: 4Gi
- **CPU**: 2

### Estimated Costs

**Free Tier** (2M requests/month):
- First 2 million requests: **$0**

**After Free Tier** (100 requests/day = 3,000/month):
- Requests: ~$0.60/month
- CPU time: ~$2-3/month
- Memory: ~$1-2/month
- **Total**: ~$3-5/month

**Cold Start Note**:
- First request after idle: ~10-20 seconds
- Subsequent requests: ~500ms-2s
- To eliminate cold starts, set `min-instances=1` (costs ~$20/month)

### Set Budget Alert
```bash
# Set budget alert at $10/month
gcloud billing budgets create \
  --billing-account=YOUR_BILLING_ACCOUNT_ID \
  --display-name="Ollama Budget" \
  --budget-amount=10USD
```

---

## Troubleshooting

### Build Fails
**Error**: "Docker not found"
- Install Docker Desktop: https://www.docker.com/products/docker-desktop

**Error**: "Permission denied"
- Run: `gcloud auth login`
- Run: `gcloud auth application-default login`

### Deployment Fails
**Error**: "Billing not enabled"
- Enable billing: https://console.cloud.google.com/billing
- Note: Won't be charged unless you exceed free tier

**Error**: "Quota exceeded"
- Wait a few minutes and retry
- Or request quota increase in GCP console

### Slow Responses
**Issue**: First request takes 10-20 seconds
- This is "cold start" - normal for min-instances=0
- Subsequent requests are fast (~500ms-2s)
- To fix: Set min-instances=1 (costs ~$20/month)

### Fallback to LocalAI
**Issue**: Always using LocalAI instead of Ollama
- Check OLLAMA_API_URL is set correctly
- Test endpoint: `curl $OLLAMA_API_URL/api/tags`
- Check Vercel logs for errors

---

## Advanced Configuration

### Use Different Model

Edit `ollama/Dockerfile`:
```dockerfile
# Lighter (faster, less accurate)
RUN ollama pull gemma2:2b

# Heavier (slower, more accurate)
RUN ollama pull llama3.2:3b
RUN ollama pull qwen2.5:3b
```

Then redeploy:
```bash
cd ollama
./deploy.sh
```

### Increase Performance

Edit `ollama/deploy.sh`:
```bash
# Faster responses (but costs more)
--min-instances 1 \
--memory 8Gi \
--cpu 4 \
```

### Enable CORS (if needed)

Add to `ollama/Dockerfile`:
```dockerfile
ENV OLLAMA_ORIGINS="*"
```

---

## Cleanup (if needed)

### Delete Service
```bash
gcloud run services delete ollama-api --region asia-southeast1
```

### Delete Project
```bash
gcloud projects delete albashiro-ai
```

---

## Support

**Issues**:
- Check logs: `gcloud run services logs read ollama-api --region asia-southeast1`
- Check status: `gcloud run services describe ollama-api --region asia-southeast1`

**Questions**:
- Google Cloud Run docs: https://cloud.google.com/run/docs
- Ollama docs: https://ollama.ai/docs

---

**Next Steps**: After successful deployment, test the chatbot on your website and verify it's using Ollama instead of LocalAI!
