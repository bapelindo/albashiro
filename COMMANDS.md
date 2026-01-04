# CHEAT SHEET: DAFTAR PERINTAH ALBASHIRO AI SYSTEM

Berikut adalah kumpulan perintah penting untuk mengelola server Albashiro di komputer baru.

---

## 1. SETUP AWAL (INSTALASI)

Jalankan di folder project `albashiro`:

**A. Install Dependencies:**
```cmd
npm install
```

**B. Buat Otak AI:**
```cmd
ollama create albashiro -f Modelfile.albashiro
```

---

## 2. NODE.JS & PM2 (AUTO-START PRO)

Cara paling direkomendasikan untuk menjalankan server agar otomatis nyala.

**A. Install PM2:**
```cmd
npm install -g pm2 pm2-windows-startup
```

**B. Pasang Hook Startup:**
```cmd
pm2-startup install
```

**C. Jalankan Server:**
```cmd
pm2 start server.js --name "albashiro-ai"
```

**D. Simpan (Agar Permanen):**
```cmd
pm2 save
```

**E. Cek Status/Log:**
```cmd
pm2 status
pm2 logs albashiro-ai
```

---

## 3. OLLAMA (AI ENGINE)

Tools untuk mengelola model AI.

**A. Cek Model Tersedia:**
```cmd
ollama list
```

**B. Jalankan Model (Manual Chat):**
```cmd
ollama run albashiro
```

**C. Update Model (Pull dari Library):**
```cmd
ollama pull qwen
```

**D. Jalankan Server (Jika mati):**
```cmd
ollama serve
```

---

## 4. CLOUDFLARE TUNNEL (ONLINE ACCESS)

Agar website bisa dibuka dari HP / Internet.

**A. Login (Cuma sekali):**
```cmd
cloudflared tunnel login
```

**B. Jalankan Tunnel:**
```cmd
cloudflared tunnel run albashiro
```

### OPSI: SSH via Cloudflare Tunnel

Untuk SSH lewat domain, perlu update `config.yml` tunnel:

**1. Edit Config Tunnel (Di Server):**
Buka file config (biasanya di `C:\Users\USERNAME\.cloudflared\config.yml`):
```yaml
tunnel: <tunnel-id>
credentials-file: C:\Users\USERNAME\.cloudflared\<tunnel-id>.json

ingress:
  - hostname: ssh.albashiro.bapel.my.id
    service: ssh://localhost:22
  - hostname: albashiro.bapel.my.id
    service: http://localhost:80
  - service: http_status:404
```

**2. Restart Tunnel:**
```cmd
pm2 restart cloudflared
```

**3. Di Laptop Client:**
```cmd
pm2 start cloudflared --name "ssh-tunnel" -- access ssh --hostname ssh.bapel.my.id --url localhost:2222
pm2 save
```

**4. Connect dengan PuTTY:**
- **Host:** `localhost`
- **Port:** `2222`

**CATATAN:** Kalau tidak mau ribet config, lebih gampang pakai **IP lokal** atau **AnyDesk**.

---

## 5. GIT (VERSION CONTROL)

Mengelola update kode.

**A. Download Kode (Clone):**
```cmd
git clone https://github.com/username/albashiro.git
```

**B. Update Kode Terbaru (Pull):**
```cmd
git pull origin main
```

**C. Cek Status Perubahan:**
```cmd
git status
```

---

## 6. BACKEND MANUAL (APACHE / MARIADB / PHP)

Biasanya dijalankan lewat GUI, tapi ini versi command line-nya (Jika path folder `bin` sudah didaftarkan ke Environment Variable).

**A. Apache:**
*   Start: `httpd -k start`
*   Restart: `httpd -k restart`
*   Stop: `httpd -k stop`
*   Cek Config: `httpd -t`

**B. MariaDB / MySQL:**
*   Login Client: `mysql -u root -p`
*   Backup Database:
    ```cmd
    mysqldump -u root -p albashiro > backup_albashiro.sql
    ```
*   Restore Database:
    ```cmd
    mysql -u root -p albashiro < backup_albashiro.sql
    ```

**C. PHP:**
*   Cek Versi: `php -v`
*   Cek Module: `php -m`

---

## 7. TIDB CLOUD (BACKUP EXTERNAL)

Jika menggunakan TiDB Cloud.

**---

## 8. SETTING PATH (Agar Command Dikenali)

Agar perintah `php`, `mysql`, `httpd` bisa jalan di CMD mana saja, Anda harus mendaftarkan foldernya ke **Environment Variables**.

**Cara Cepat (CMD Administrator):**
Ganti path di bawah sesuai lokasi install Anda.

```cmd
setx PATH "%PATH%;C:\apache\php;C:\apache\sql\bin;C:\apache\bin"
```
*(Setelah run ini, tutup CMD dan buka lagi).*

**Cara Manual (GUI):**
1.  Tekan `Win + R`, ketik `sysdm.cpl`, Enter.
2.  Tab **Advanced** -> Tombol **Environment Variables**.
3.  Di bagian **System variables**, cari **Path** -> **Edit**.
4.  **New** -> Masukkan path folder bin:
    *   `C:\apache\php`
    *   `C:\apache\sql\bin`
    *   `C:\apache\bin`
---

## 9. CURL (TEST KONEKSI)

Untuk mengecek apakah server merespon dengan benar.

**Cek Koneksi Node.js:**
```cmd
curl http://localhost:3000
```
*(Harus muncul respon HTML/JSON, bukan error connection refused)*.

**Cek Koneksi Ollama:**
---

## 10. CRON JOB (WINDOWS TASK SCHEDULER)

Di Windows, kita menggunakan **Task Scheduler** pengganti Cron Job.
Berikut cara mendaftarkannya agar script `send_reminders.php` jalan setiap 5 menit.

**Perintah Auto-Register (Jalankan di CMD Admin):**
```cmd
schtasks /create /tn "Albashiro Reminders" /tr "C:\apache\php\php.exe C:\apache\htdocs\albashiro\app\cron\send_reminders.php" /sc minute /mo 5
```

**Penjelasan:**
*   `/tn`: Nama Task ("Albashiro Reminders")
*   `/tr`: Target Program (PHP + File Script)
*   `/sc`: Jadwal (Minute = Menit)
*   `/mo`: Interval (Setiap 5 menit)

**Cek Apakah Sudah Jalan:**
```cmd
schtasks /query /tn "Albashiro Reminders"
```

**Hapus Cron Job:**
```cmd
schtasks /delete /tn "Albashiro Reminders" /f
```

**OPSI 2: PM2 CRON (Lebih Praktis):**
Kalau Anda sudah pakai PM2, bisa juga pakai PM2 buat cron job.
Perintahnya:

```cmd
pm2 start C:\apache\htdocs\albashiro\app\cron\send_reminders.php --name "reminder-cron" --interpreter "C:\apache\php\php.exe" --cron "*/5 * * * *" --no-autorestart
```

*   `--interpreter "..."`: Menunjuk ke file `php.exe` manual Anda.
*   `--cron "*/5 * * * *"`: Artinya jalan setiap 5 menit.
*   `--no-autorestart`: Penting! Agar tidak restart terus-menerus.

---

---

## 11. REMOTE DESKTOP (RDP)

Agar komputer server bisa dikendalikan jarak jauh (Remote) dari laptop lain.

**Cara Aktifkan (Via Settings App):**
1.  Klik **Start Menu**, ketik: *"Remote Desktop settings"*.
2.  Pilih menu yang muncul (Pengaturan).
3.  Ubah tombol **"Enable Remote Desktop"** menjadi **ON**.
4.  Klik **Confirm**.

**Cek IP Address Komputer Ini:**
```cmd
ipconfig
```
*(Cari IPv4 Address, misal: 192.168.1.XX. Gunakan IP ini untuk connect dari komputer lain).*

### OPSI LAIN (APP PIHAK KETIGA)
Jika RDP ribet karena harus setting IP/Tunnel, gunakan aplikasi ini (Download & Install):

1.  **AnyDesk** (Paling Ringan)
    *   Download: [anydesk.com](https://anydesk.com)
    *   Cukup catat "Your Address" (9 digit angka), lalu connect dari HP/Laptop lain.

2.  **TeamViewer** (Populer)
    *   Download: [teamviewer.com](https://www.teamviewer.com)

3.  **Chrome Remote Desktop** (Paling Gampang via Google)
    *   Install extension di Chrome, login akun Google.
    *   Bisa akses langsung dari website `remotedesktop.google.com`.

---

## 12. SSH (CMD REMOTE - JAGA-JAGA)

Jika RDP & TeamViewer error, gunakan **SSH** (Layar Hitam/Terminal only).

**1. Download Manual (Portable):**
*   Download versi terbaru di: [github.com/PowerShell/Win32-OpenSSH/releases](https://github.com/PowerShell/Win32-OpenSSH/releases)
*   Pilih file `.zip` (misal: `OpenSSH-Win64.zip`).
*   Extract folder `OpenSSH-Win64` ke `C:\Program Files\OpenSSH`.

**2. Install Service (Run CMD as Admin):**
Masuk ke folder tadi dan install service:
```cmd
cd "C:\Program Files\OpenSSH"
sshd.exe install
```

**3. Jalankan Service:**
```cmd
net start sshd
sc.exe config sshd start= auto
```
*(Pakai `sc.exe` bukan `sc` agar berfungsi di PowerShell)*

**3a. Cek Username Windows (Penting untuk PuTTY!):**
```cmd
echo %USERNAME%
```
*(Catat username ini, nanti dipakai untuk login SSH).*

**4. Cara Connect (Pakai PuTTY):**
1.  Download & Buka **PuTTY** di laptop lain.
2.  **Host Name:** Masukkan IP Server (misal `192.168.1.5`).
3.  **PENTING!** Menu **Connection** → **Data** → **Auto-login username:** Isi username Windows server (misal: `ahnri`).
4.  Kembali ke **Session**, klik **Save** (biar tidak perlu setting ulang).
5.  Klik **Open** → Langsung masuk tanpa ketik username/password!

**5. Login Tanpa Password (SSH Key di PuTTY):**
Agar otomatis masuk tanpa ketik password:

1.  **Generate Key (Di Laptop Remote):**
    *   Buka **PuTTYgen** (Bawaan installer PuTTY).
    *   Klik **Generate** (Gerak-gerakkan mouse pada area kosong).
    *   **Copy** teks di kolom "Public key for pasting..." (paling atas).
    *   Klik **Save private key** (Simpan sebagai `mykey.ppk`).

2.  **Pasang di Server (Manual CMD):**
    Buka CMD di server, jalankan:
    ```cmd
    mkdir %USERPROFILE%\.ssh
    notepad %USERPROFILE%\.ssh\authorized_keys
    ```
    Paste Public Key dari PuTTYgen (kotak atas), Save, tutup Notepad.
    
    Lalu fix permission:
    ```cmd
    icacls %USERPROFILE%\.ssh\authorized_keys /inheritance:r /grant "Administrators:F" "SYSTEM:F" "%USERNAME%:F"
    ```

3.  **Setting PuTTY:**
    *   Buka PuTTY -> Menu **Connection** -> **SSH** -> **Auth** -> **Credentials**.
    *   **Private key file:** Browse file `mykey.ppk` tadi.
    *   Kembali ke **Session**, lalu **Save** supaya tidak perlu setting ulang.

---

### TROUBLESHOOTING: "Server refused our key"

Kalau masih ditolak, kemungkinan private key tidak match. **Generate ulang key pair:**

1.  **Hapus key lama di server:**
    ```cmd
    del %USERPROFILE%\.ssh\authorized_keys
    ```

2.  **Generate key BARU di PuTTYgen:**
    - Buka PuTTYgen → Generate (gerakkan mouse)
    - **Save private key** → `albashiro_new.ppk`
    - **COPY** public key dari kotak atas (jangan klik "Save public key"!)

3.  **Install ke server:**
    ```cmd
    mkdir %USERPROFILE%\.ssh
    notepad %USERPROFILE%\.ssh\authorized_keys
    ```
    Paste public key, Save.
    
    Fix permission:
    ```cmd
    icacls %USERPROFILE%\.ssh\authorized_keys /inheritance:r /grant "Administrators:F" "SYSTEM:F" "%USERNAME%:F"
    ```

4.  **Load di PuTTY:**
    - Connection → SSH → Auth → Credentials → Browse `albashiro_new.ppk`
    - Save session

Sekarang coba login lagi. Harusnya langsung masuk!
