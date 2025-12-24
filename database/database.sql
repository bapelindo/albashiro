-- =====================================================
-- TiDB Cloud Compatible Database Schema
-- Generated for: Albashiro Hypnotherapy
-- Target Database: test (TiDB Serverless)
-- =====================================================

-- Create database (skip this line when importing to TiDB Cloud)
CREATE DATABASE IF NOT EXISTS `albashiro` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `albashiro`;

SET FOREIGN_KEY_CHECKS=0;

-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               12.1.2-MariaDB - MariaDB Server
-- Server OS:                    Win64
-- HeidiSQL Version:             12.14.0.7165
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for albashiro



-- Dumping structure for table albashiro.availability_overrides
CREATE TABLE IF NOT EXISTS `availability_overrides` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `therapist_id` int(11) unsigned NOT NULL,
  `override_date` date NOT NULL,
  `is_available` tinyint(1) DEFAULT 0,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_therapist_date` (`therapist_id`,`override_date`),
  KEY `idx_date` (`override_date`),
  CONSTRAINT `fk_override_therapist` FOREIGN KEY (`therapist_id`) REFERENCES `therapists` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table albashiro.availability_overrides: ~1 rows (approximately)
INSERT INTO `availability_overrides` (`id`, `therapist_id`, `override_date`, `is_available`, `start_time`, `end_time`, `reason`, `created_at`) VALUES
	(1, 1, '2025-12-25', 0, NULL, NULL, 'sakit', '2025-12-24 16:19:50');

-- Dumping structure for table albashiro.blog_posts
CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext NOT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `author_id` int(11) unsigned NOT NULL,
  `category` varchar(100) DEFAULT 'Artikel',
  `tags` varchar(255) DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'draft',
  `views` int(11) DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `author_id` (`author_id`),
  CONSTRAINT `fk_blog_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table albashiro.blog_posts: ~3 rows (approximately)
INSERT INTO `blog_posts` (`id`, `title`, `slug`, `excerpt`, `content`, `featured_image`, `author_id`, `category`, `tags`, `status`, `views`, `published_at`, `created_at`, `updated_at`) VALUES
	(1, 'Apa Itu Hipnoterapi Islami? Panduan Lengkap untuk Pemula', 'apa-itu-hipnoterapi-islami', 'Hipnoterapi Islami adalah metode terapi yang menggabungkan teknik hipnoterapi modern dengan nilai-nilai Islam. Pelajari lebih lanjut tentang bagaimana terapi ini dapat membantu Anda.', '<h2>Pengertian Hipnoterapi Islami</h2><p>Hipnoterapi Islami adalah pendekatan terapeutik yang menggabungkan teknik hipnoterapi klinis dengan bimbingan spiritual berdasarkan Al-Quran dan Sunnah.</p>', NULL, 1, 'Edukasi', 'hipnoterapi,islami,kesehatan mental', 'published', 0, '2025-12-24 14:34:27', '2025-12-24 14:34:27', '2025-12-24 14:34:27'),
	(2, 'Mengatasi Kecemasan dengan Pendekatan Islami', 'mengatasi-kecemasan-pendekatan-islami', 'Kecemasan adalah masalah yang banyak dialami masyarakat modern. Bagaimana cara mengatasinya dengan pendekatan yang sesuai syariat Islam?', '<h2>Memahami Kecemasan</h2><p>Kecemasan adalah respons alami tubuh terhadap stres. Namun, ketika kecemasan menjadi berlebihan dan mengganggu kehidupan sehari-hari, diperlukan penanganan yang tepat.</p>', NULL, 1, 'Tips Kesehatan', 'kecemasan,anxiety,mental health', 'published', 0, '2025-12-24 14:34:27', '2025-12-24 14:34:27', '2025-12-24 14:34:27'),
	(3, 'Tips Memilih Terapis Hipnoterapi yang Tepat', 'tips-memilih-terapis-hipnoterapi', 'Memilih terapis yang tepat adalah langkah penting dalam perjalanan penyembuhan Anda. Berikut panduan untuk memilih terapis hipnoterapi yang sesuai.', '<h2>Pentingnya Memilih Terapis yang Tepat</h2><p>Keberhasilan hipnoterapi sangat bergantung pada kualitas terapis dan hubungan terapeutik yang terbangun.</p>', NULL, 1, 'Tips', 'terapis,hipnoterapi,tips', 'published', 0, '2025-12-24 14:34:27', '2025-12-24 14:34:27', '2025-12-24 14:34:27');

-- Dumping structure for table albashiro.bookings
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `booking_code` varchar(20) NOT NULL,
  `therapist_id` int(11) unsigned NOT NULL,
  `service_id` int(11) unsigned DEFAULT NULL,
  `client_name` varchar(100) NOT NULL,
  `wa_number` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `problem_description` text NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time DEFAULT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `reminder_sent` tinyint(1) DEFAULT 0 COMMENT 'WhatsApp reminder sent flag',
  `reminder_sent_at` datetime DEFAULT NULL COMMENT 'When reminder was sent',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `booking_code` (`booking_code`),
  KEY `therapist_id` (`therapist_id`),
  KEY `service_id` (`service_id`),
  KEY `idx_reminder` (`appointment_date`,`appointment_time`,`reminder_sent`),
  CONSTRAINT `fk_booking_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_booking_therapist` FOREIGN KEY (`therapist_id`) REFERENCES `therapists` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table albashiro.bookings: ~5 rows (approximately)
INSERT INTO `bookings` (`id`, `booking_code`, `therapist_id`, `service_id`, `client_name`, `wa_number`, `email`, `problem_description`, `appointment_date`, `appointment_time`, `status`, `notes`, `reminder_sent`, `reminder_sent_at`, `created_at`, `updated_at`) VALUES
	(1, 'ALB-2A373AD3', 1, 1, 'Ahmad Nur Rizki', '6282229114960', 'ahnrizki@gmail.com', 'asdsasddsasd', '2025-12-25', '09:00:00', 'pending', NULL, 1, '2025-12-24 22:56:12', '2025-12-24 14:48:44', '2025-12-24 15:56:12'),
	(2, 'ALB-C40AF8A9', 1, 1, 'Ahmad Nur Rizki', '6282229114960', 'ahnrizki@gmail.com', 'asdsasddsasd', '2025-12-25', '12:00:00', 'pending', NULL, 0, NULL, '2025-12-24 14:52:13', '2025-12-24 14:52:13'),
	(20, 'MANUAL-TEST-225307', 1, 1, 'Manual Test User', '6282228967897', NULL, 'Testing client and therapist reminder', '2025-12-24', '22:00:00', 'confirmed', NULL, 1, '2025-12-24 22:53:08', '2025-12-24 15:53:07', '2025-12-24 15:53:08'),
	(21, 'FUTURE-TEST-225612', 1, 1, 'Future Test User', '6282228967897', NULL, 'Testing future booking system', '2025-12-25', '09:00:00', 'confirmed', NULL, 1, '2025-12-24 22:56:13', '2025-12-24 15:56:12', '2025-12-24 15:56:13'),
	(22, 'ALB-CC47622D', 1, 1, 'asddsa', '6282228967897', 'ahnrizki@gmail.com', 'kadalkesit', '2025-12-24', '23:00:00', 'pending', NULL, 1, '2025-12-24 23:23:44', '2025-12-24 16:21:18', '2025-12-24 16:23:44');

-- Dumping structure for table albashiro.faqs
CREATE TABLE IF NOT EXISTS `faqs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `question` varchar(500) NOT NULL,
  `answer` text NOT NULL,
  `sort_order` int(3) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table albashiro.faqs: ~8 rows (approximately)
INSERT INTO `faqs` (`id`, `question`, `answer`, `sort_order`, `is_active`, `created_at`) VALUES
	(1, 'Apa itu Hipnoterapi Islami?', 'Hipnoterapi Islami adalah metode terapi yang menggabungkan teknik hipnoterapi modern dengan nilai-nilai dan prinsip Islam. Terapi ini menggunakan kondisi relaksasi mendalam (trance) untuk mengakses pikiran bawah sadar, dikombinasikan dengan doa, dzikir, dan bimbingan spiritual sesuai syariat Islam.', 1, 1, '2025-12-24 14:34:27'),
	(2, 'Apakah Hipnoterapi itu halal?', 'Ya, hipnoterapi yang dilakukan dengan tujuan menyembuhkan dan membantu seseorang adalah halal. Yang diharamkan adalah hipnosis untuk tujuan hiburan (hypnosis show) atau merugikan orang lain. Di Albashiro, kami memastikan setiap sesi terapi sesuai dengan syariat Islam.', 2, 1, '2025-12-24 14:34:27'),
	(3, 'Berapa lama durasi satu sesi terapi?', 'Durasi satu sesi terapi berkisar antara 90-120 menit, tergantung jenis layanan dan kebutuhan klien. Sesi pertama biasanya lebih lama karena mencakup konsultasi awal dan pengisian form data.', 3, 1, '2025-12-24 14:34:27'),
	(4, 'Berapa kali sesi yang dibutuhkan untuk sembuh?', 'Setiap orang berbeda-beda. Beberapa klien merasakan perubahan signifikan setelah 1-2 sesi, sementara yang lain membutuhkan 3-5 sesi. Terapis akan mendiskusikan rencana terapi yang sesuai dengan kondisi Anda.', 4, 1, '2025-12-24 14:34:27'),
	(5, 'Apakah bisa terapi online?', 'Ya, kami menyediakan layanan terapi online via video call untuk klien yang tidak bisa datang langsung. Efektivitasnya sama dengan terapi offline, asalkan koneksi internet stabil dan berada di ruangan yang tenang.', 5, 1, '2025-12-24 14:34:27'),
	(6, 'Apakah rahasia klien terjaga?', 'Sangat terjaga. Kami menjunjung tinggi kode etik profesi dan menjamin kerahasiaan semua informasi klien. Data dan isi sesi terapi tidak akan dibagikan kepada siapapun tanpa izin klien.', 6, 1, '2025-12-24 14:34:27'),
	(7, 'Siapa saja yang bisa menjalani hipnoterapi?', 'Hipnoterapi aman untuk semua usia, mulai dari anak-anak (minimal 5 tahun) hingga dewasa. Namun, tidak disarankan untuk orang dengan gangguan mental berat (seperti skizofrenia) tanpa rekomendasi dokter.', 7, 1, '2025-12-24 14:34:27'),
	(8, 'Bagaimana cara membuat janji?', 'Anda bisa mengisi form reservasi di website ini atau langsung menghubungi kami via WhatsApp. Setelah itu, tim kami akan mengkonfirmasi jadwal yang tersedia dan memberikan detail lokasi/link meeting.', 8, 1, '2025-12-24 14:34:27');

-- Dumping structure for table albashiro.reminder_logs
CREATE TABLE IF NOT EXISTS `reminder_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) unsigned NOT NULL,
  `message` text NOT NULL,
  `wa_number` varchar(20) NOT NULL,
  `sent_at` datetime NOT NULL,
  `delivery_status` varchar(20) DEFAULT 'pending',
  PRIMARY KEY (`id`),
  KEY `idx_booking_id` (`booking_id`),
  KEY `idx_sent_at` (`sent_at`),
  KEY `idx_status` (`delivery_status`),
  CONSTRAINT `fk_reminder_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table albashiro.reminder_logs: ~4 rows (approximately)
INSERT INTO `reminder_logs` (`id`, `booking_id`, `message`, `wa_number`, `sent_at`, `delivery_status`) VALUES
	(1, 20, 'Assalamu\'alaikum Manual Test User,\n\n🔔 *PENGINGAT JANJI TEMU*\n\nAnda memiliki jadwal hipnoterapi *HARI INI*:\n\n👳 Terapis: Ustadz Ahmad Fadhil\n✨ Layanan: Hipnoterapi Anak\n📅 Tanggal: Rabu, 24 Desember 2025\n⏰ Waktu: 22:00 WIB\n📍 Lokasi: Al-Bashiro Hypnotherapy Center\n🔖 Kode Booking: *MANUAL-TEST-225307*\n\n⚠️ Mohon datang 10 menit lebih awal.\n\nJika berhalangan hadir, silakan hubungi kami segera.\n\nJazakallahu khairan. 🤲', '6282228967897', '2025-12-24 22:53:08', 'sent'),
	(2, 1, 'Assalamu\'alaikum Ahmad Nur Rizki,\n\n🔔 *PENGINGAT JANJI TEMU*\n\nAnda memiliki jadwal hipnoterapi *HARI INI*:\n\n👳 Terapis: Ustadz Ahmad Fadhil\n✨ Layanan: Hipnoterapi Anak\n📅 Tanggal: Kamis, 25 Desember 2025\n⏰ Waktu: 09:00 WIB\n📍 Lokasi: Al-Bashiro Hypnotherapy Center\n🔖 Kode Booking: *ALB-2A373AD3*\n\n⚠️ Mohon datang 10 menit lebih awal.\n\nJika berhalangan hadir, silakan hubungi kami segera.\n\nJazakallahu khairan. 🤲', '6282229114960', '2025-12-24 22:56:12', 'sent'),
	(3, 21, 'Assalamu\'alaikum Future Test User,\n\n🔔 *PENGINGAT JANJI TEMU*\n\nAnda memiliki jadwal hipnoterapi *HARI INI*:\n\n👳 Terapis: Ustadz Ahmad Fadhil\n✨ Layanan: Hipnoterapi Anak\n📅 Tanggal: Kamis, 25 Desember 2025\n⏰ Waktu: 09:00 WIB\n📍 Lokasi: Al-Bashiro Hypnotherapy Center\n🔖 Kode Booking: *FUTURE-TEST-225612*\n\n⚠️ Mohon datang 10 menit lebih awal.\n\nJika berhalangan hadir, silakan hubungi kami segera.\n\nJazakallahu khairan. 🤲', '6282228967897', '2025-12-24 22:56:13', 'sent'),
	(4, 22, 'Assalamu\'alaikum asddsa,\n\n🔔 *PENGINGAT JANJI TEMU*\n\nAnda memiliki jadwal hipnoterapi *HARI INI*:\n\n👳 Terapis: Ustadz Ahmad Fadhil\n✨ Layanan: Hipnoterapi Anak\n📅 Tanggal: Rabu, 24 Desember 2025\n⏰ Waktu: 23:00 WIB\n📍 Lokasi: Al-Bashiro Hypnotherapy Center\n🔖 Kode Booking: *ALB-CC47622D*\n\n⚠️ Mohon datang 10 menit lebih awal.\n\nJika berhalangan hadir, silakan hubungi kami segera.\n\nJazakallahu khairan. 🤲', '6282228967897', '2025-12-24 23:23:44', 'sent');

-- Dumping structure for table albashiro.reschedule_history
CREATE TABLE IF NOT EXISTS `reschedule_history` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) unsigned NOT NULL,
  `old_date` date NOT NULL,
  `old_time` time NOT NULL,
  `new_date` date NOT NULL,
  `new_time` time NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `rescheduled_by` enum('admin','client') DEFAULT 'admin',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_booking` (`booking_id`),
  CONSTRAINT `fk_reschedule_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table albashiro.reschedule_history: ~0 rows (approximately)

-- Dumping structure for table albashiro.services
CREATE TABLE IF NOT EXISTS `services` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `icon` varchar(50) DEFAULT 'fa-heart',
  `target_audience` enum('Anak','Remaja','Dewasa','Semua') DEFAULT 'Semua',
  `price` decimal(12,2) DEFAULT 0.00,
  `duration` varchar(50) DEFAULT '90-120 menit',
  `is_featured` tinyint(1) DEFAULT 0,
  `sort_order` int(3) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table albashiro.services: ~8 rows (approximately)
INSERT INTO `services` (`id`, `name`, `slug`, `description`, `icon`, `target_audience`, `price`, `duration`, `is_featured`, `sort_order`, `created_at`) VALUES
	(1, 'Hipnoterapi Anak', 'hipnoterapi-anak', 'Terapi khusus untuk anak-anak yang mengalami susah makan, kurang fokus, trauma, atau masalah perilaku lainnya. Pendekatan lembut dan menyenangkan.', 'fa-child', 'Anak', 500000.00, '60-90 menit', 1, 1, '2025-12-24 14:34:27'),
	(2, 'Hipnoterapi Remaja', 'hipnoterapi-remaja', 'Membantu remaja mengatasi kecemasan, kurang percaya diri, bullying, prokrastinasi, dan masalah akademik dengan pendekatan modern dan Islami.', 'fa-user-graduate', 'Remaja', 500000.00, '90-120 menit', 1, 2, '2025-12-24 14:34:27'),
	(3, 'Manajemen Stres & Kecemasan', 'manajemen-stres-kecemasan', 'Terapi untuk mengatasi stres berlebihan, kecemasan, panic attack, dan gangguan psikosomatis dengan teknik relaksasi mendalam.', 'fa-brain', 'Dewasa', 500000.00, '90-120 menit', 1, 3, '2025-12-24 14:34:27'),
	(4, 'Trauma & Luka Batin', 'trauma-luka-batin', 'Membantu menyembuhkan trauma masa lalu, luka batin, PTSD, dan pengalaman menyakitkan dengan metode hipnoterapi yang aman dan efektif.', 'fa-heart-crack', 'Dewasa', 600000.00, '120-150 menit', 1, 4, '2025-12-24 14:34:27'),
	(5, 'Konseling Pasangan & Keluarga', 'konseling-pasangan-keluarga', 'Terapi untuk mengatasi konflik rumah tangga, masalah komunikasi, dan memperbaiki hubungan dengan pasangan atau keluarga.', 'fa-people-roof', 'Dewasa', 700000.00, '90-120 menit', 1, 5, '2025-12-24 14:34:27'),
	(6, 'Peningkatan Percaya Diri', 'peningkatan-percaya-diri', 'Membangun kepercayaan diri, mengatasi rasa minder, dan mengembangkan self-esteem yang sehat dengan sugesti positif.', 'fa-star', 'Semua', 500000.00, '90-120 menit', 0, 6, '2025-12-24 14:34:27'),
	(7, 'Mengatasi Kebiasaan Buruk', 'mengatasi-kebiasaan-buruk', 'Terapi untuk berhenti dari kebiasaan buruk seperti merokok, overthinking, doom scrolling, dan perilaku negatif lainnya.', 'fa-ban', 'Dewasa', 500000.00, '90-120 menit', 0, 7, '2025-12-24 14:34:27'),
	(8, 'Konseling Online', 'konseling-online', 'Sesi konseling dan hipnoterapi via video call untuk Anda yang tidak bisa datang langsung. Fleksibel dan tetap efektif.', 'fa-video', 'Semua', 400000.00, '60-90 menit', 1, 8, '2025-12-24 14:34:27');

-- Dumping structure for table albashiro.site_settings
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `setting_type` enum('text','number','boolean','json') DEFAULT 'text',
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table albashiro.site_settings: ~12 rows (approximately)
INSERT INTO `site_settings` (`id`, `setting_key`, `setting_value`, `setting_type`) VALUES
	(1, 'site_name', 'Albashiro', 'text'),
	(2, 'site_tagline', 'Islamic Spiritual Hypnotherapy', 'text'),
	(3, 'admin_whatsapp', '6282228967897', 'text'),
	(4, 'admin_email', 'info@albashiro.com', 'text'),
	(5, 'address', 'Jl. Imam Bonjol No. 123, Jakarta Pusat, DKI Jakarta 10310, Indonesia', 'text'),
	(6, 'operating_hours', 'Senin - Sabtu: 09:00 - 17:00 WIB | Minggu: Tutup', 'text'),
	(7, 'instagram', 'https://instagram.com/albashiro', 'text'),
	(8, 'facebook', 'https://facebook.com/albashiro', 'text'),
	(9, 'youtube', 'https://youtube.com/@albashiro', 'text'),
	(10, 'tiktok', 'https://tiktok.com/@albashiro', 'text'),
	(11, 'fonnte_api_token', 'baXPGAQDBSfTe3vQ84W8', 'text'),
	(12, 'fonnte_group_id', '120363422821859147@g.us', 'text');

-- Dumping structure for table albashiro.testimonials
CREATE TABLE IF NOT EXISTS `testimonials` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `client_name` varchar(100) NOT NULL,
  `client_initial` varchar(10) DEFAULT NULL COMMENT 'For privacy, e.g., A.S.',
  `client_location` varchar(100) DEFAULT NULL,
  `content` text NOT NULL,
  `rating` tinyint(1) DEFAULT 5 COMMENT '1-5 stars',
  `therapist_id` int(11) unsigned DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `therapist_id` (`therapist_id`),
  CONSTRAINT `fk_testimonial_therapist` FOREIGN KEY (`therapist_id`) REFERENCES `therapists` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table albashiro.testimonials: ~6 rows (approximately)
INSERT INTO `testimonials` (`id`, `client_name`, `client_initial`, `client_location`, `content`, `rating`, `therapist_id`, `is_featured`, `created_at`) VALUES
	(1, 'Ahmad Rizki', 'A.R.', 'Jakarta', 'Alhamdulillah, setelah 3 sesi dengan Ustadz Ahmad, kecemasan saya yang sudah 2 tahun akhirnya bisa teratasi. Pendekatan Islamnya sangat menenangkan dan membuat saya merasa aman. Sangat merekomendasikan!', 5, 1, 1, '2025-12-24 14:34:27'),
	(2, 'Ibu Nurhasanah', 'N.H.', 'Bandung', 'Anak saya yang susah makan akhirnya mau makan sayur setelah terapi dengan Dr. Siti. Prosesnya menyenangkan dan anak saya tidak merasa takut sama sekali. Terima kasih banyak!', 5, 2, 1, '2025-12-24 14:34:27'),
	(3, 'Dewi Safitri', 'D.S.', 'Surabaya', 'Saya sangat bersyukur menemukan Ustadzah Fatimah. Sebagai wanita, saya merasa nyaman curhat tentang masalah rumah tangga saya. Beliau sangat sabar dan memberikan solusi yang bijak berdasarkan Al-Quran dan Sunnah.', 5, 3, 1, '2025-12-24 14:34:27'),
	(4, 'Budi Santoso', 'B.S.', 'Yogyakarta', 'Trauma masa kecil yang saya pendam selama 20 tahun akhirnya bisa dilepaskan. Proses hipnoterapinya sangat profesional dan saya merasa lebih ringan sekarang. Jazakallahu khairan!', 5, 1, 1, '2025-12-24 14:34:27'),
	(5, 'Rina Amalia', 'R.A.', 'Semarang', 'Anak saya yang kena bullying di sekolah sekarang jadi lebih percaya diri setelah terapi. Dr. Siti sangat memahami psikologi anak dan pendekatannya sangat tepat.', 5, 2, 1, '2025-12-24 14:34:27'),
	(6, 'Hana Permata', 'H.P.', 'Malang', 'Konflik dengan suami yang sudah berlarut-larut akhirnya bisa diselesaikan dengan baik setelah konseling dengan Ustadzah Fatimah. Rumah tangga kami sekarang jauh lebih harmonis.', 5, 3, 1, '2025-12-24 14:34:27');

-- Dumping structure for table albashiro.therapist_availability
CREATE TABLE IF NOT EXISTS `therapist_availability` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `therapist_id` int(11) unsigned NOT NULL,
  `day_of_week` enum('monday','tuesday','wednesday','thursday','friday','saturday','sunday') NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_therapist_day` (`therapist_id`,`day_of_week`),
  KEY `idx_therapist` (`therapist_id`),
  KEY `idx_day` (`day_of_week`),
  CONSTRAINT `fk_availability_therapist` FOREIGN KEY (`therapist_id`) REFERENCES `therapists` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table albashiro.therapist_availability: ~15 rows (approximately)
INSERT INTO `therapist_availability` (`id`, `therapist_id`, `day_of_week`, `is_available`, `start_time`, `end_time`, `created_at`, `updated_at`) VALUES
	(1, 1, 'monday', 1, '09:00:00', '17:00:00', '2025-12-24 14:48:06', '2025-12-24 14:48:06'),
	(2, 1, 'tuesday', 1, '09:00:00', '17:00:00', '2025-12-24 14:48:06', '2025-12-24 14:48:06'),
	(3, 1, 'wednesday', 1, '00:00:00', '23:00:00', '2025-12-24 14:48:06', '2025-12-24 16:19:39'),
	(4, 1, 'thursday', 1, '09:00:00', '17:00:00', '2025-12-24 14:48:06', '2025-12-24 14:48:06'),
	(5, 1, 'friday', 1, '09:00:00', '17:00:00', '2025-12-24 14:48:06', '2025-12-24 14:48:06'),
	(6, 2, 'monday', 1, '09:00:00', '17:00:00', '2025-12-24 14:48:10', '2025-12-24 14:48:10'),
	(7, 2, 'tuesday', 1, '09:00:00', '17:00:00', '2025-12-24 14:48:10', '2025-12-24 14:48:10'),
	(8, 2, 'wednesday', 1, '09:00:00', '17:00:00', '2025-12-24 14:48:10', '2025-12-24 14:48:10'),
	(9, 2, 'thursday', 1, '09:00:00', '17:00:00', '2025-12-24 14:48:10', '2025-12-24 14:48:10'),
	(10, 2, 'friday', 1, '09:00:00', '17:00:00', '2025-12-24 14:48:10', '2025-12-24 14:48:10'),
	(11, 3, 'monday', 1, '09:00:00', '17:00:00', '2025-12-24 14:48:15', '2025-12-24 14:48:15'),
	(12, 3, 'tuesday', 1, '09:00:00', '17:00:00', '2025-12-24 14:48:15', '2025-12-24 14:48:15'),
	(13, 3, 'wednesday', 1, '09:00:00', '17:00:00', '2025-12-24 14:48:15', '2025-12-24 14:48:15'),
	(14, 3, 'thursday', 1, '09:00:00', '17:00:00', '2025-12-24 14:48:15', '2025-12-24 14:48:15'),
	(15, 3, 'friday', 1, '09:00:00', '17:00:00', '2025-12-24 14:48:15', '2025-12-24 14:48:15');

-- Dumping structure for table albashiro.therapists
CREATE TABLE IF NOT EXISTS `therapists` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL COMMENT 'e.g., S.Psi, CH.t., CI',
  `specialty` varchar(255) NOT NULL,
  `photo_url` varchar(255) DEFAULT 'default-therapist.jpg',
  `bio` text NOT NULL,
  `credentials` text DEFAULT NULL COMMENT 'Certifications and credentials',
  `experience_years` int(2) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table albashiro.therapists: ~3 rows (approximately)
INSERT INTO `therapists` (`id`, `name`, `title`, `specialty`, `photo_url`, `bio`, `credentials`, `experience_years`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'Hj. Dewi Irvani', 'S.Hut, MCHt, CI, MPh, MSI', 'Hipnoterapi Spiritual & Trauma Healing', 'therapist-1.jpg', 'Ustadz Ahmad Fadhil adalah praktisi hipnoterapi Islami dengan pengalaman lebih dari 10 tahun dalam membantu klien menemukan kedamaian jiwa. Beliau menggabungkan pendekatan psikologi modern dengan nilai-nilai spiritual Islam untuk memberikan terapi yang holistik dan menyembuhkan.', 'Certified Hypnotherapist (IBH), Master Practitioner of NLP, Konselor Islam Bersertifikat, Anggota Asosiasi Hipnoterapis Indonesia', 10, 1, '2025-12-24 14:34:27', '2025-12-24 16:50:36'),
	(2, 'Siti Muzayanah', 'SPd.I, C.H, C.MH, C.Ht, C.MMH', 'Konseling Keluarga & Anak-Anak', 'therapist-2.jpg', 'Dr. Siti Aminah adalah psikolog klinis dan hipnoterapis yang berfokus pada terapi anak dan keluarga. Dengan pendekatan yang lembut dan penuh kasih sayang, beliau membantu anak-anak mengatasi trauma, kecemasan, dan masalah perilaku dengan metode yang ramah dan Islami.', 'Psikolog Klinis Terdaftar, Certified Child Hypnotherapist, Terapis Keluarga Bersertifikat, Lulusan Magister Psikologi UGM', 8, 1, '2025-12-24 14:34:27', '2025-12-24 16:50:36'),
	(3, 'Ustadzah Fatimah Zahra', 'S.Psi., CH.t., CI', 'Hipnoterapi Wanita & Pasangan', 'therapist-3.jpg', 'Ustadzah Fatimah Zahra adalah hipnoterapis perempuan yang khusus melayani klien wanita dan konseling pasangan. Beliau memahami kebutuhan unik perempuan Muslim dan menyediakan ruang aman untuk berbagi serta menemukan solusi dengan bimbingan nilai-nilai Islami.', 'Certified Hypnotherapist, Konselor Pernikahan Islam, Praktisi EFT, Trainer Parenting Islami', 6, 1, '2025-12-24 14:34:27', '2025-12-24 16:50:36');

-- Dumping structure for table albashiro.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','editor') DEFAULT 'editor',
  `avatar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table albashiro.users: ~1 rows (approximately)
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `avatar`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
	(1, 'Administrator', 'admin@albashiro.com', '$2y$12$kvw/bsfKfHvglz5W1xEqkeef.4jlsqvIMXg1ksYrb6HCxbXxs4VOG', 'admin', NULL, 1, '2025-12-24 16:16:47', '2025-12-24 14:34:27', '2025-12-24 16:16:47');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;

SET FOREIGN_KEY_CHECKS=1;
