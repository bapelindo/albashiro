# 🚀 Setup GitHub Actions Cron (GRATIS)

## ✅ File yang Sudah Dibuat

1. ✅ `app/cron/send_reminders.php` - Script cron untuk kirim reminder (sudah ada)
2. ✅ `.github/workflows/send-reminders.yml` - GitHub Actions workflow
3. ✅ `config/config.php` - Semua konfigurasi sudah ada di sini

## 📝 Cara Setup (2 Menit)

### 1. Push ke GitHub

```bash
git add .
git commit -m "Add GitHub Actions cron for WhatsApp reminders"
git push origin main
```

### 2. Done! 🎉

Tidak perlu setup secrets! Semua konfigurasi sudah ada di `config/config.php`:
- Database credentials
- Fonnte API token
- WhatsApp numbers

**Cara Kerja:**
- Cron berjalan otomatis setiap jam (00:00, 01:00, 02:00, dst.)
- Mencari appointment yang akan dimulai dalam **30-60 menit**
- Kirim reminder ke klien dan terapis
- Reminder dikirim **30 menit sebelum** jadwal appointment

## 🧪 Testing

### Test Manual di GitHub Actions

1. Buka repository di GitHub
2. Klik tab **Actions**
3. Pilih workflow **"Send WhatsApp Reminders"**
4. Klik **"Run workflow"** → **"Run workflow"**
5. Tunggu beberapa detik dan lihat hasilnya

✅ Jika berhasil, akan ada tanda centang hijau  
❌ Jika gagal, klik untuk lihat error logs

### Test Lokal

```bash
# Jalankan script langsung
cd app/cron
php send_reminders.php
```

Atau via PowerShell:

```powershell
cd c:\apache\htdocs\albashiro\app\cron
php send_reminders.php
```

## 📊 Monitoring

### 1. GitHub Actions Dashboard

- Buka tab **Actions** di repository
- Lihat history execution
- Klik untuk detail logs
- Setiap execution tercatat dengan timestamp

### 2. Application Logs

File log: `logs/reminders.log`

```bash
# View logs
cat logs/reminders.log

# View last 20 lines
tail -n 20 logs/reminders.log
```

### 3. Database Logs

Cek tabel `reminder_logs`:

```sql
SELECT 
    booking_id,
    wa_number,
    delivery_status,
    sent_at
FROM reminder_logs 
ORDER BY sent_at DESC 
LIMIT 20;
```

## 🔧 Ubah Schedule

Edit file `.github/workflows/send-reminders.yml`:

```yaml
on:
  schedule:
    # Setiap jam (default)
    - cron: '0 * * * *'
    
    # Setiap 30 menit
    # - cron: '*/30 * * * *'
    
    # Setiap hari jam 9 pagi
    # - cron: '0 9 * * *'
    
    # Setiap jam dari 8 pagi - 8 malam
    # - cron: '0 8-20 * * *'
```

**Format cron:** `minute hour day month dayOfWeek`

Setelah edit, commit dan push:

```bash
git add .github/workflows/send-reminders.yml
git commit -m "Update cron schedule"
git push
```

## 📋 Konfigurasi

Semua konfigurasi ada di `config/config.php`:

```php
// Database (TiDB Cloud)
define('DB_HOST', 'gateway01.ap-northeast-1.prod.aws.tidbcloud.com');
define('DB_NAME', 'albashiro');
define('DB_USER', '4TnpUUxik5ZLHTT.root');
define('DB_PASS', 'xYwYMe4gp4c7IkgI');

// Fonnte API
define('FONNTE_API_TOKEN', 'baXPGAQDBSfTe3vQ84W8');
define('FONNTE_GROUP_ID', '120363422798942271@g.us');

// Admin WhatsApp
define('ADMIN_WHATSAPP', '6282228967897');

// Therapist WhatsApp
define('THERAPIST_WHATSAPP', [
    1 => '628155017069',   // Bunda Dewi
    2 => '62895335419945',  // Bu Muza
    3 => '6282228967897',   // Ustadzah Fatimah Zahra
]);
```

**Tidak perlu file .env!** Semua sudah tersimpan di `config.php`.

## 🐛 Troubleshooting

### Cron tidak berjalan

1. ✅ Cek GitHub Actions tab untuk error logs
2. ✅ Pastikan workflow file sudah di-push ke GitHub
3. ✅ Cek apakah ada syntax error di YAML

### Reminder tidak terkirim

1. Cek `logs/reminders.log` untuk error messages
2. Verifikasi Fonnte API token masih valid
3. Cek tabel `bookings` untuk data yang sesuai:
   - `appointment_date` = hari ini
   - `appointment_time` = jam sekarang (format: HH:00:00)
   - `status` = 'confirmed' atau 'pending'
   - `reminder_sent` = 0

### Database connection error

1. Cek credentials di `config/config.php`
2. Pastikan database host bisa diakses dari GitHub Actions
3. TiDB Cloud harus allow public access atau whitelist GitHub IPs

### PHP errors

Cek GitHub Actions logs untuk detail error:
- Klik tab **Actions**
- Klik pada execution yang failed
- Expand **"Run reminder cron script"** step
- Lihat error message

## 💡 Tips

### Disable Cron Sementara

Edit `.github/workflows/send-reminders.yml`, comment schedule:

```yaml
on:
  # schedule:
  #   - cron: '0 * * * *'
  
  workflow_dispatch:  # Tetap bisa manual trigger
```

### Test dengan Booking Dummy

```sql
-- Insert booking untuk testing (1 jam dari sekarang)
INSERT INTO bookings (
    booking_code, client_name, wa_number, 
    appointment_date, appointment_time,
    therapist_id, service_id, status, reminder_sent
) VALUES (
    'TEST-001', 'Test Client', '6281234567890',
    CURDATE(), 
    DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 1 HOUR), '%H:00:00'),
    1, 1, 'confirmed', 0
);

-- Cek data
SELECT 
    booking_code, client_name, 
    appointment_date, appointment_time,
    reminder_sent
FROM bookings 
WHERE booking_code = 'TEST-001';
```

### Manual Trigger untuk Testing

Jika ingin test sekarang tanpa tunggu schedule:
1. Buka GitHub → Actions
2. Pilih workflow "Send WhatsApp Reminders"
3. Klik "Run workflow"
4. Pilih branch (biasanya `main`)
5. Klik "Run workflow"

## 📈 Cara Kerja

1. **GitHub Actions** menjalankan workflow setiap jam
2. **Checkout code** dari repository
3. **Setup PHP 8.1** dengan extensions yang dibutuhkan
4. **Jalankan** `app/cron/send_reminders.php`
5. **Script** akan:
   - Connect ke database
   - Cari bookings yang perlu reminder
   - Kirim WhatsApp via Fonnte API
   - Update status `reminder_sent = 1`
   - Log ke `reminder_logs` table
6. **GitHub Actions** menampilkan hasil di logs

## ✅ Checklist Setup

- [ ] Push code ke GitHub
- [ ] Verifikasi workflow file ada di `.github/workflows/`
- [ ] Test manual di GitHub Actions
- [ ] Tunggu 1 jam untuk cron pertama berjalan
- [ ] Cek logs di GitHub Actions
- [ ] Verifikasi di `reminder_logs` table

## 🎯 Keuntungan Setup Ini

✅ **GRATIS** - GitHub Actions free tier cukup  
✅ **Simple** - Tidak perlu API wrapper  
✅ **Direct** - Langsung jalankan PHP script  
✅ **No Secrets** - Semua config di `config.php`  
✅ **Reliable** - GitHub infrastructure  
✅ **Monitoring** - Built-in logs di GitHub Actions  

## 📞 Support

Jika ada masalah:
1. Cek GitHub Actions logs (tab Actions)
2. Cek application logs (`logs/reminders.log`)
3. Cek database logs (`reminder_logs` table)
4. Cek Fonnte dashboard untuk status API

---

**Selamat! Sistem cron WhatsApp reminder Anda sudah siap! 🎉**

**Sangat sederhana:** Push ke GitHub → Done! Tidak perlu setup apapun lagi.
