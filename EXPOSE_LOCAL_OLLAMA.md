# Expose Local Ollama to Vercel Production

## Problem
Vercel (cloud) tidak bisa akses Ollama di laptop Anda (localhost:11434)

## Solution: Cloudflare Tunnel (GRATIS!)

### Step 1: Install Cloudflared
```powershell
# Download cloudflared
winget install --id Cloudflare.cloudflared
```

### Step 2: Expose Ollama
```powershell
# Expose localhost:11434 to internet
cloudflared tunnel --url http://localhost:11434
```

Output akan seperti:
```
Your quick Tunnel has been created! Visit it at:
https://random-name-1234.trycloudflare.com
```

### Step 3: Copy URL ke Vercel
1. Copy URL dari cloudflared (contoh: `https://random-name-1234.trycloudflare.com`)
2. Buka Vercel dashboard: https://vercel.com/dashboard
3. Project `albashiro` → Settings → Environment Variables
4. Add/Update:
   - Name: `OLLAMA_API_URL`
   - Value: `https://random-name-1234.trycloudflare.com`
5. Save & Redeploy

### Step 4: Test
1. Buka: https://albashiro.vercel.app
2. Klik chatbot
3. Ketik: "berapa harga paket?"
4. **Vercel akan call Ollama di laptop Anda!**

---

## Important Notes

⚠️ **Laptop harus NYALA & Ollama running**:
- Cloudflared tunnel harus running
- Ollama harus running
- Laptop harus online

⚠️ **URL berubah setiap restart**:
- Setiap restart cloudflared, URL baru
- Harus update Vercel env vars lagi
- Solusi: Pakai cloudflared named tunnel (permanent URL)

---

## Alternative: ngrok (Juga Gratis)

### Install ngrok
```powershell
winget install ngrok
```

### Expose Ollama
```powershell
ngrok http 11434
```

Copy URL → Update Vercel env vars

---

## Permanent Solution: Named Tunnel

### Setup (One-time)
```powershell
# Login
cloudflared tunnel login

# Create tunnel
cloudflared tunnel create albashiro-ollama

# Configure
# Create config.yml:
tunnel: <TUNNEL-ID>
credentials-file: C:\Users\<USER>\.cloudflared\<TUNNEL-ID>.json

ingress:
  - hostname: ollama.yourdomain.com
    service: http://localhost:11434
  - service: http_status:404
```

### Run
```powershell
cloudflared tunnel run albashiro-ollama
```

**Permanent URL**: `https://ollama.yourdomain.com`

---

## Pros & Cons

### Cloudflare Tunnel
✅ Gratis selamanya
✅ Secure (HTTPS)
✅ No port forwarding
❌ Laptop harus nyala

### LocalAI (Current)
✅ Always available
✅ Super fast (0-2ms)
✅ No dependencies
❌ Rule-based (not true AI)

---

**Recommendation**: 
- **Development**: Cloudflare tunnel + Ollama lokal
- **Production**: Deploy Ollama ke Railway/HF (always on)

Mau saya guide setup cloudflared sekarang?
