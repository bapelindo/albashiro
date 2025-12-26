# Setting Up OpenRouter API Key di Vercel

## Step 1: Generate API Key Baru

1. Go to https://openrouter.ai/keys
2. Login atau Sign up
3. Click **"Create Key"**
4. Copy API key yang baru (format: `sk-or-v1-...`)

## Step 2: Set Environment Variable di Vercel

### Via Vercel Dashboard (Recommended):

1. Go to https://vercel.com/dashboard
2. Pilih project **albashiro**
3. Click tab **"Settings"**
4. Click **"Environment Variables"** di sidebar
5. Add new variable:
   - **Name**: `OPENROUTER_API_KEY`
   - **Value**: `sk-or-v1-YOUR_NEW_KEY_HERE` (paste key dari Step 1)
   - **Environment**: Pilih **Production**, **Preview**, dan **Development**
6. Click **"Save"**

### Via Vercel CLI:

```bash
vercel env add OPENROUTER_API_KEY
# Paste your API key when prompted
# Select: Production, Preview, Development
```

## Step 3: Redeploy

Setelah set environment variable, **redeploy** project:

### Option 1: Auto Redeploy
Push commit baru ke GitHub (Vercel auto-deploy):
```bash
git commit --allow-empty -m "Trigger redeploy for env vars"
git push
```

### Option 2: Manual Redeploy
Di Vercel Dashboard:
1. Go to **Deployments** tab
2. Click **"..."** pada deployment terakhir
3. Click **"Redeploy"**

## Step 4: Verify

Setelah deployment selesai:
1. Buka chatbot di website Vercel
2. Test dengan pertanyaan
3. Check Vercel logs untuk konfirmasi API key terpakai

## Optional: Set Model via Environment Variable

Kalau mau ganti model tanpa edit code:

```bash
vercel env add OPENROUTER_MODEL
# Value: google/gemini-2.0-flash-exp:free
# atau model lain seperti: meta-llama/llama-3.2-1b-instruct:free
```

## Keuntungan Environment Variables:

✅ **Security**: API key tidak di-commit ke GitHub
✅ **Flexibility**: Ganti key tanpa edit code
✅ **Multi-environment**: Beda key untuk production/preview/dev

## Troubleshooting

**Error "User not found"**:
- API key salah atau expired
- Generate key baru di https://openrouter.ai/keys

**Env var tidak terpakai**:
- Pastikan sudah redeploy setelah set env var
- Check typo di nama variable (case-sensitive)
