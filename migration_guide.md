# Panduan Migrasi Sistem Albashiro ke Komputer Baru
JANGAN LUPA CEK ENVIROMENT VARIABLES


Berikut adalah daftar lengkap hal-hal yang perlu disiapkan dan langkah-langkah untuk memindahkan sistem Albashiro ke komputer lain.

## 1. Persiapan Software (Wajib Install)
Pastikan komputer baru sudah terinstall software berikut:

6.  **Apache** (Web Server)
    *   Download: [apachelounge.com](https://www.apachelounge.com/download/)
7.  **MariaDB** (Database)
    *   Download: [mariadb.org](https://mariadb.org/download/)
8.  **PHP** (Versi 8.0+)
    *   Download: [windows.php.net](https://windows.php.net/download/)
    *   Pastikan extension `mysqli`, `curl`, `pdo_mysql` diaktifkan di `php.ini`.
2.  **Node.js** (Versi LTS terbaru, misal v18 atau v20)
    *   Download: [nodejs.org](https://nodejs.org/)
3.  **Ollama** (Untuk menjalankan AI)
    *   Download di: [ollama.com](https://ollama.com/)
4.  **Git** (Untuk clone repository)
    *   Download di: [git-scm.com](https://git-scm.com/)
5.  **PuTTY** (Untuk Remote/Key Generator)
    *   Download di: [putty.org](https://www.putty.org/)

---

## 2. Pemindahan File (Source Code)

Ada 2 cara:
*   **Cara A (Git - Disarankan):**
    Buka terminal di folder `htdocs`, lalu jalankan:
    ```bash
    git clone https://github.com/username-anda/albashiro.git
    ```
*   **Cara B (Manual):**
    Copy seluruh folder `c:\apache\htdocs\albashiro` dari komputer lama, lalu paste ke `c:\apache\htdocs\albashiro` di komputer baru.

---

## 3. Pemindahan Database

1.  **Di Komputer Lama (Export):**
    *   Buka **phpMyAdmin** (`http://localhost/phpmyadmin`).
    *   Pilih database `albashiro` (atau nama database yang Anda pakai).
    *   Klik menu **Export** -> **Go**.
    *   Simpan file `.sql` yang terdownload.

2.  **Di Komputer Baru (Import):**
    *   Buka **phpMyAdmin**.
    *   Buat database baru dengan nama yang sama (misal: `albashiro`).
    *   Klik menu **Import**.
    *   Upload file `.sql` dari komputer lama.
    *   Klik **Go**.

---

## 4. Setup Fonnte (WhatsApp Gateway)

Fonnte sudah terintegrasi di dalam kode.
*   Token Fonnte Anda tersimpan di file `config/config.php`.
*   Selama Anda meng-copy seluruh folder `albashiro` (termasuk folder config), **Token Anda aman**.
*   Tidak perlu setting ulang di website Fonnte, kecuali token expired.

---

## 5. Setup Ollama (AI Model)

Agar AI berjalan seperti semula, Anda perlu membuat ulang custom model `albashiro`.

1.  Pastikan file `Modelfile.albashiro` ada di folder proyek.
2.  Buka terminal/CMD, arahkan ke folder proyek.
3.  Jalankan perintah ini untuk membuat ulang model:
    ```bash
    ollama create albashiro -f Modelfile.albashiro
    ```
    *(Tunggu prosesnya sampai selesai. Ini akan mendownload base model jika belum ada)*.

---

## 5. Install Dependencies Node.js

Agar server Node.js bisa jalan, Anda perlu menginstall library-nya lagi.

1.  Buka terminal/CMD di folder `albashiro`.
2.  Jalankan:
    ```bash
    npm install
    ```

---

## 6. Konfigurasi Server (Penting!)

Agar tidak ada masalah timeout atau "terpotong" di komputer baru, atur konfigurasi ini:

### A. Konfigurasi Apache (`httpd.conf`)
*   Lokasi: `C:\xampp\apache\conf\httpd.conf`
*   Cari `Timeout`, ubah menjadi:
    ```apache
    Timeout 300
    ```
*   Pastikan module php aktif.

### B. Konfigurasi PHP (`php.ini`)
*   Lokasi: `C:\xampp\php\php.ini`
*   Cari dan ubah settingan berikut:
    ```ini
    max_execution_time = 300
    default_socket_timeout = 300
    memory_limit = 512M
    extension=curl  ; (Pastikan titik koma di depannya dihapus/uncomment)
    extension=mysqli
    extension=pdo_mysql
    ```
*   **Restart Apache** setelah mengubah ini.

---

## 7. Setup Cloudflare Tunnel (Agar Website Online)

Agar website bisa diakses publik (https://albashiro.bapel.my.id), Anda perlu menjalankan Cloudflare Tunnel.

1.  **Download Cloudflared:**
    *   Download file `cloudflared-windows-amd64.msi` dari [cloudflare/cloudflared](https://github.com/cloudflare/cloudflared/releases).
    *   Install seperti biasa.

2.  **Login & Setup (Hanya Pertama Kali):**
    Buka CMD, lalu ketik:
    ```bash
    cloudflared tunnel login
    ```
    *(Browser akan terbuka, login ke akun Cloudflare Anda -> Pilih domain albashiro)*.

3.  **Jalankan Tunnel:**
    Perintah ini harus selalu jalan di background (CMD terpisah).
    ```bash
    cloudflared tunnel run albashiro
    ```
    *(Ganti 'albashiro' dengan nama tunnel yang Anda buat di dashboard Cloudflare)*.

---

---

## 8. Setup Remote Access (Opsional)

Agar komputer server ini bisa dikendalikan dari jarak jauh tanpa monitor (Headless).

### A. Aktifkan Remote Desktop (RDP)
1.  Buka **Settings** -> **System** -> **Remote Desktop**.
2.  Set **Enable Remote Desktop** ke **On**.

### B. Install SSH Server (Manual / Portable)
Untuk akses Terminal jarak jauh yang ringan.
1.  Download **OpenSSH** terbaru dari [github.com/PowerShell/Win32-OpenSSH/releases](https://github.com/PowerShell/Win32-OpenSSH/releases).
2.  Extract ke `C:\Program Files\OpenSSH`.
3.  Buka CMD (Administrator), lalu jalankan:
    ```cmd
    cd "C:\Program Files\OpenSSH"
    powershell -ExecutionPolicy Bypass -File install-sshd.ps1
    sc config sshd start= auto
    net start sshd
    ```

---

## 9. Menjalankan Sistem

Setiap kali menyalakan komputer baru, lakukan urutan ini:

1.  **Start XAMPP** (Apache & MySQL).
2.  **Start Ollama** (Biasanya otomatis jalan di background, atau jalankan `ollama serve`).
3.  **Start Node.js Server**:
    *   Buka CMD di folder `albashiro`.
    *   Ketik:
        ```bash
        node server.js
        ```

Selesai! Sistem seharusnya berjalan sama persis dengan komputer lama.
