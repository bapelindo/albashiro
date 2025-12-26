# Cloudflare Tunnel Setup - Permanent URL untuk Ollama

## Quick Start (Temporary URL)

### 1. Install Cloudflared (sedang running...)
```powershell
winget install --id Cloudflare.cloudflared
```

### 2. Start Quick Tunnel
```powershell
# Tutup PowerShell, buka PowerShell BARU
cloudflared tunnel --url http://localhost:11434
```

**Output**:
```
Your quick Tunnel has been created! Visit it at:
https://random-name-1234.trycloudflare.com
```

### 3. Copy URL ke Vercel
1. Copy URL (contoh: `https://random-name-1234.trycloudflare.com`)
2. Buka: https://vercel.com/dashboard
3. Project `albashiro` → Settings → Environment Variables
4. Add/Update:
   - **Name**: `OLLAMA_API_URL`
   - **Value**: `https://random-name-1234.trycloudflare.com`
5. Save & Redeploy

---

## Permanent URL Setup (Recommended)

### Step 1: Login to Cloudflare
```powershell
cloudflared tunnel login
```
- Browser akan terbuka
- Login dengan Cloudflare account
- Pilih domain (atau buat account baru - gratis)

### Step 2: Create Named Tunnel
```powershell
cloudflared tunnel create albashiro-ollama
```

Output:
```
Created tunnel albashiro-ollama with id abc123-def456
```

### Step 3: Create Config File
Buat file: `C:\Users\<USER>\.cloudflared\config.yml`

```yaml
tunnel: abc123-def456
credentials-file: C:\Users\<USER>\.cloudflared\abc123-def456.json

ingress:
  - hostname: ollama-albashiro.yourdomain.com
    service: http://localhost:11434
  - service: http_status:404
```

### Step 4: Create DNS Record
```powershell
cloudflared tunnel route dns albashiro-ollama ollama-albashiro.yourdomain.com
```

### Step 5: Run Tunnel
```powershell
cloudflared tunnel run albashiro-ollama
```

**Permanent URL**: `https://ollama-albashiro.yourdomain.com`

---

## Auto-Start on Windows Boot

### Create Scheduled Task
```powershell
# Run as Administrator
$action = New-ScheduledTaskAction -Execute "cloudflared.exe" -Argument "tunnel run albashiro-ollama"
$trigger = New-ScheduledTaskTrigger -AtStartup
$principal = New-ScheduledTaskPrincipal -UserId "$env:USERNAME" -LogonType Interactive
Register-ScheduledTask -TaskName "Cloudflare Tunnel - Ollama" -Action $action -Trigger $trigger -Principal $principal
```

---

## Vercel Configuration

### Environment Variable
```
OLLAMA_API_URL=https://ollama-albashiro.yourdomain.com
```

### Test
1. Buka: https://albashiro.vercel.app
2. Chatbot → "berapa harga paket?"
3. Response dari Ollama di laptop Anda!

---

## Monitoring

### Check Tunnel Status
```powershell
# List tunnels
cloudflared tunnel list

# Check if running
Get-Process cloudflared -ErrorAction SilentlyContinue
```

### Cloudflare Dashboard
- https://dash.cloudflare.com
- Zero Trust → Access → Tunnels
- View traffic, logs, status

---

## Troubleshooting

**"cloudflared not found"**:
- Tutup PowerShell
- Buka PowerShell BARU
- Coba lagi

**Tunnel disconnected**:
- Check internet connection
- Restart: `cloudflared tunnel run albashiro-ollama`

**Vercel timeout**:
- Check Ollama running: `ollama list`
- Check tunnel running: `Get-Process cloudflared`
- Test URL directly in browser

---

## Comparison: Quick vs Named Tunnel

| Feature | Quick Tunnel | Named Tunnel |
|---------|--------------|--------------|
| Setup | 1 command | 5 steps |
| URL | Random | Custom |
| Persistence | Temporary | Permanent |
| Best for | Testing | Production |

---

## Benefits

✅ **Permanent URL** - Tidak berubah setiap restart  
✅ **Unlimited** - No rate limits  
✅ **Secure** - HTTPS automatic  
✅ **Fast** - Cloudflare CDN  
✅ **Free** - Gratis selamanya  
✅ **Monitoring** - Dashboard lengkap  

---

## Next Steps

1. ⏳ Wait for cloudflared install
2. 🚀 Run quick tunnel: `cloudflared tunnel --url http://localhost:11434`
3. 📋 Copy URL
4. ⚙️ Update Vercel env vars
5. 🧪 Test production!

**Optional**: Setup named tunnel untuk permanent URL
