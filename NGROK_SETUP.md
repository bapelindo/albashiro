# Setup Ngrok untuk Expose Local Ollama ke Vercel

## Quick Start (5 menit!)

### 1. Install Ngrok (sudah running...)
```powershell
winget install ngrok
```

### 2. Download Ollama Model (jika belum)
```powershell
ollama pull gemma3:4b
```

### 3. Start Ollama (pastikan running)
```powershell
# Cek apakah Ollama sudah running
Get-Process ollama -ErrorAction SilentlyContinue

# Jika belum, Ollama biasanya auto-start
# Atau run: ollama serve
```

### 4. Expose dengan Ngrok
```powershell
ngrok http 11434
```

**Output akan seperti**:
```
Forwarding  https://abc123.ngrok.io -> http://localhost:11434
```

### 5. Copy URL ke Vercel
1. Copy URL ngrok (contoh: `https://abc123.ngrok.io`)
2. Buka: https://vercel.com/dashboard
3. Project `albashiro` → Settings → Environment Variables
4. Add/Update variable:
   - **Name**: `OLLAMA_API_URL`
   - **Value**: `https://abc123.ngrok.io` (paste URL dari ngrok)
5. Save
6. Redeploy: Deployments → Latest → ... → Redeploy

### 6. Test Production
1. Buka: https://albashiro.vercel.app
2. Klik chatbot icon
3. Ketik: "berapa harga paket?"
4. **Vercel akan call Ollama di laptop Anda!** 🚀

---

## Important Notes

⚠️ **Laptop harus NYALA**:
- Ngrok tunnel harus running
- Ollama harus running
- Laptop harus online

⚠️ **URL berubah setiap restart**:
- Free ngrok: URL baru setiap restart
- Harus update Vercel env vars lagi
- Solusi: Upgrade ngrok ($8/month) untuk static URL

⚠️ **Ngrok Free Limits**:
- 40 connections/minute
- 1 online ngrok process
- Random URL setiap restart

---

## Ngrok Commands

```powershell
# Start tunnel
ngrok http 11434

# Start with custom subdomain (paid only)
ngrok http 11434 --subdomain=albashiro-ollama

# View web interface
# Open browser: http://localhost:4040
```

---

## Troubleshooting

**"ngrok not found"**:
- Tutup PowerShell
- Buka PowerShell BARU
- Coba lagi

**Ollama tidak response**:
- Cek Ollama running: `Get-Process ollama`
- Test lokal: `ollama run gemma3:4b "test"`
- Cek ngrok dashboard: http://localhost:4040

**Vercel masih pakai LocalAI**:
- Pastikan `OLLAMA_API_URL` sudah di-set di Vercel
- Redeploy Vercel
- Check Vercel logs untuk error

---

## Alternative: Keep Laptop Always On

### Option 1: Ngrok Static URL ($8/month)
- Permanent URL
- No need update Vercel every restart

### Option 2: Cloudflare Tunnel (Gratis)
- Permanent URL (with domain)
- More stable than ngrok free

### Option 3: Deploy Ollama to Cloud
- Railway.app ($5 free credit)
- Always on, no laptop needed

---

## Recommended Workflow

**Development** (localhost):
```
Ollama lokal → Test true AI responses
```

**Production** (Vercel):
```
Option A: Ngrok + Laptop always on
Option B: LocalAI (instant, reliable, always available)
```

**My Recommendation**: 
- Use **LocalAI for production** (reliable, fast, always on)
- Use **Ollama + ngrok for testing** (when you want to test true AI)

---

**Next Steps**:
1. Wait for ngrok install to complete
2. Run: `ngrok http 11434`
3. Copy URL
4. Update Vercel env vars
5. Test!
