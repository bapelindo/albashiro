-- =====================================================
-- TiDB Cloud Compatible Database Schema
-- Generated for: Albashiro Hypnotherapy
-- Target Database: test (TiDB Serverless)
-- =====================================================

-- Create database (skip this line when importing to TiDB Cloud)
CREATE DATABASE IF NOT EXISTS `albashiro` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `albashiro`;

SET FOREIGN_KEY_CHECKS=0;

-- =====================================================
-- Table: therapists
-- =====================================================
CREATE TABLE IF NOT EXISTS `therapists` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `title` VARCHAR(100) NOT NULL COMMENT 'e.g., S.Psi, CH.t., CI',
    `specialty` VARCHAR(255) NOT NULL,
    `photo_url` VARCHAR(255) DEFAULT 'default-therapist.jpg',
    `bio` TEXT NOT NULL,
    `credentials` TEXT COMMENT 'Certifications and credentials',
    `experience_years` INT(2) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: services
-- =====================================================
CREATE TABLE IF NOT EXISTS `services` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(150) NOT NULL,
    `description` TEXT NOT NULL,
    `icon` VARCHAR(50) DEFAULT 'fa-heart',
    `target_audience` ENUM('Anak', 'Remaja', 'Dewasa', 'Semua') DEFAULT 'Semua',
    `price` DECIMAL(12,2) DEFAULT 0.00,
    `duration` VARCHAR(50) DEFAULT '90-120 menit',
    `is_featured` TINYINT(1) DEFAULT 0,
    `sort_order` INT(3) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: users (Admin Authentication)
-- =====================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'editor') DEFAULT 'editor',
    `avatar` VARCHAR(255) DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `last_login` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: testimonials
-- =====================================================
CREATE TABLE IF NOT EXISTS `testimonials` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `client_name` VARCHAR(100) NOT NULL,
    `client_initial` VARCHAR(10) DEFAULT NULL COMMENT 'For privacy, e.g., A.S.',
    `client_location` VARCHAR(100) DEFAULT NULL,
    `content` TEXT NOT NULL,
    `rating` TINYINT(1) DEFAULT 5 COMMENT '1-5 stars',
    `therapist_id` INT(11) UNSIGNED DEFAULT NULL,
    `is_featured` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `therapist_id` (`therapist_id`),
    CONSTRAINT `fk_testimonial_therapist` FOREIGN KEY (`therapist_id`) REFERENCES `therapists`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: faqs
-- =====================================================
CREATE TABLE IF NOT EXISTS `faqs` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `question` VARCHAR(500) NOT NULL,
    `answer` TEXT NOT NULL,
    `sort_order` INT(3) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: therapist_availability (Weekly Schedule)
-- =====================================================
CREATE TABLE IF NOT EXISTS `therapist_availability` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `therapist_id` INT(11) UNSIGNED NOT NULL,
    `day_of_week` ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') NOT NULL,
    `is_available` TINYINT(1) DEFAULT 1,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_therapist_day` (`therapist_id`, `day_of_week`),
    KEY `idx_therapist` (`therapist_id`),
    KEY `idx_day` (`day_of_week`),
    CONSTRAINT `fk_availability_therapist` FOREIGN KEY (`therapist_id`) REFERENCES `therapists`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: availability_overrides (Holidays/Special Hours)
-- =====================================================
CREATE TABLE IF NOT EXISTS `availability_overrides` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `therapist_id` INT(11) UNSIGNED NOT NULL,
    `override_date` DATE NOT NULL,
    `is_available` TINYINT(1) DEFAULT 0,
    `start_time` TIME NULL,
    `end_time` TIME NULL,
    `reason` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_therapist_date` (`therapist_id`, `override_date`),
    KEY `idx_date` (`override_date`),
    CONSTRAINT `fk_override_therapist` FOREIGN KEY (`therapist_id`) REFERENCES `therapists`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: global_holidays (Holidays for All Therapists)
-- =====================================================
CREATE TABLE IF NOT EXISTS `global_holidays` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `holiday_date` DATE NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_holiday_date` (`holiday_date`),
    KEY `idx_date` (`holiday_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: bookings (WITH REMINDER TRACKING)
-- =====================================================
CREATE TABLE IF NOT EXISTS `bookings` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `booking_code` VARCHAR(20) NOT NULL,
    `therapist_id` INT(11) UNSIGNED NOT NULL,
    `service_id` INT(11) UNSIGNED DEFAULT NULL,
    `client_name` VARCHAR(100) NOT NULL,
    `wa_number` VARCHAR(20) NOT NULL,
    `email` VARCHAR(100) DEFAULT NULL,
    `problem_description` TEXT NOT NULL,
    `appointment_date` DATE NOT NULL,
    `appointment_time` TIME DEFAULT NULL,
    `status` ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    `notes` TEXT DEFAULT NULL,
    `reminder_sent` TINYINT(1) DEFAULT 0 COMMENT 'WhatsApp reminder sent flag',
    `reminder_sent_at` DATETIME NULL COMMENT 'When reminder was sent',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `booking_code` (`booking_code`),
    KEY `therapist_id` (`therapist_id`),
    KEY `service_id` (`service_id`),
    KEY `idx_reminder` (`appointment_date`, `appointment_time`, `reminder_sent`),
    CONSTRAINT `fk_booking_therapist` FOREIGN KEY (`therapist_id`) REFERENCES `therapists`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_booking_service` FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: reschedule_history
-- =====================================================
CREATE TABLE IF NOT EXISTS `reschedule_history` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `booking_id` INT(11) UNSIGNED NOT NULL,
    `old_date` DATE NOT NULL,
    `old_time` TIME NOT NULL,
    `new_date` DATE NOT NULL,
    `new_time` TIME NOT NULL,
    `reason` VARCHAR(255),
    `rescheduled_by` ENUM('admin', 'client') DEFAULT 'admin',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_booking` (`booking_id`),
    CONSTRAINT `fk_reschedule_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: reminder_logs (WhatsApp Reminder Tracking)
-- =====================================================
CREATE TABLE IF NOT EXISTS `reminder_logs` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `booking_id` INT(11) UNSIGNED NOT NULL,
    `message` TEXT NOT NULL,
    `wa_number` VARCHAR(20) NOT NULL,
    `sent_at` DATETIME NOT NULL,
    `delivery_status` VARCHAR(20) DEFAULT 'pending',
    PRIMARY KEY (`id`),
    KEY `idx_booking_id` (`booking_id`),
    KEY `idx_sent_at` (`sent_at`),
    KEY `idx_status` (`delivery_status`),
    CONSTRAINT `fk_reminder_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: blog_posts
-- =====================================================
CREATE TABLE IF NOT EXISTS `blog_posts` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `excerpt` TEXT,
    `content` LONGTEXT NOT NULL,
    `featured_image` VARCHAR(255) DEFAULT NULL,
    `author_id` INT(11) UNSIGNED NOT NULL,
    `category` VARCHAR(100) DEFAULT 'Artikel',
    `tags` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('draft', 'published') DEFAULT 'draft',
    `views` INT(11) DEFAULT 0,
    `published_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`),
    KEY `author_id` (`author_id`),
    CONSTRAINT `fk_blog_author` FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: site_settings
-- =====================================================
CREATE TABLE IF NOT EXISTS `site_settings` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT NOT NULL,
    `setting_type` ENUM('text', 'number', 'boolean', 'json') DEFAULT 'text',
    PRIMARY KEY (`id`),
    UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SEED DATA: Therapists
-- =====================================================
INSERT INTO `therapists` (`id`, `name`, `title`, `specialty`, `photo_url`, `bio`, `credentials`, `experience_years`, `is_active`) VALUES
(1, 'Hj. Dewi Irvani', 'S.Hut, MCHt, CI, MPh, MSI', 'Hipnoterapi Spiritual & Trauma Healing', 'therapist-1.jpg', 
'Ustadz Ahmad Fadhil adalah praktisi hipnoterapi Islami dengan pengalaman lebih dari 10 tahun dalam membantu klien menemukan kedamaian jiwa. Beliau menggabungkan pendekatan psikologi modern dengan nilai-nilai spiritual Islam untuk memberikan terapi yang holistik dan menyembuhkan.', 
'Certified Hypnotherapist (IBH), Master Practitioner of NLP, Konselor Islam Bersertifikat, Anggota Asosiasi Hipnoterapis Indonesia', 10, 1),

(2, 'Siti Muzayanah', 'SPd.I, C.H, C.MH, C.Ht, C.MMH', 'Konseling Keluarga & Anak-Anak', 'therapist-2.jpg',
'Dr. Siti Aminah adalah psikolog klinis dan hipnoterapis yang berfokus pada terapi anak dan keluarga. Dengan pendekatan yang lembut dan penuh kasih sayang, beliau membantu anak-anak mengatasi trauma, kecemasan, dan masalah perilaku dengan metode yang ramah dan Islami.',
'Psikolog Klinis Terdaftar, Certified Child Hypnotherapist, Terapis Keluarga Bersertifikat, Lulusan Magister Psikologi UGM', 8, 1),

(3, 'Ustadzah Fatimah Zahra', 'S.Psi., CH.t., CI', 'Hipnoterapi Wanita & Pasangan', 'therapist-3.jpg',
'Ustadzah Fatimah Zahra adalah hipnoterapis perempuan yang khusus melayani klien wanita dan konseling pasangan. Beliau memahami kebutuhan unik perempuan Muslim dan menyediakan ruang aman untuk berbagi serta menemukan solusi dengan bimbingan nilai-nilai Islami.',
'Certified Hypnotherapist, Konselor Pernikahan Islam, Praktisi EFT, Trainer Parenting Islami', 6, 1);

-- =====================================================
-- SEED DATA: Services
-- =====================================================
INSERT INTO `services` (`id`, `name`, `slug`, `description`, `icon`, `target_audience`, `price`, `duration`, `is_featured`, `sort_order`) VALUES
(1, 'Hipnoterapi Anak', 'hipnoterapi-anak', 'Terapi khusus untuk anak-anak yang mengalami susah makan, kurang fokus, trauma, atau masalah perilaku lainnya. Pendekatan lembut dan menyenangkan.', 'fa-child', 'Anak', 500000.00, '60-90 menit', 1, 1),
(2, 'Hipnoterapi Remaja', 'hipnoterapi-remaja', 'Membantu remaja mengatasi kecemasan, kurang percaya diri, bullying, prokrastinasi, dan masalah akademik dengan pendekatan modern dan Islami.', 'fa-user-graduate', 'Remaja', 500000.00, '90-120 menit', 1, 2),
(3, 'Manajemen Stres & Kecemasan', 'manajemen-stres-kecemasan', 'Terapi untuk mengatasi stres berlebihan, kecemasan, panic attack, dan gangguan psikosomatis dengan teknik relaksasi mendalam.', 'fa-brain', 'Dewasa', 500000.00, '90-120 menit', 1, 3),
(4, 'Trauma & Luka Batin', 'trauma-luka-batin', 'Membantu menyembuhkan trauma masa lalu, luka batin, PTSD, dan pengalaman menyakitkan dengan metode hipnoterapi yang aman dan efektif.', 'fa-heart-crack', 'Dewasa', 600000.00, '120-150 menit', 1, 4),
(5, 'Konseling Pasangan & Keluarga', 'konseling-pasangan-keluarga', 'Terapi untuk mengatasi konflik rumah tangga, masalah komunikasi, dan memperbaiki hubungan dengan pasangan atau keluarga.', 'fa-people-roof', 'Dewasa', 700000.00, '90-120 menit', 1, 5),
(6, 'Peningkatan Percaya Diri', 'peningkatan-percaya-diri', 'Membangun kepercayaan diri, mengatasi rasa minder, dan mengembangkan self-esteem yang sehat dengan sugesti positif.', 'fa-star', 'Semua', 500000.00, '90-120 menit', 0, 6),
(7, 'Mengatasi Kebiasaan Buruk', 'mengatasi-kebiasaan-buruk', 'Terapi untuk berhenti dari kebiasaan buruk seperti merokok, overthinking, doom scrolling, dan perilaku negatif lainnya.', 'fa-ban', 'Dewasa', 500000.00, '90-120 menit', 0, 7),
(8, 'Konseling Online', 'konseling-online', 'Sesi konseling dan hipnoterapi via video call untuk Anda yang tidak bisa datang langsung. Fleksibel dan tetap efektif.', 'fa-video', 'Semua', 400000.00, '60-90 menit', 1, 8);

-- =====================================================
-- SEED DATA: Testimonials
-- =====================================================
INSERT INTO `testimonials` (`id`, `client_name`, `client_initial`, `client_location`, `content`, `rating`, `therapist_id`, `is_featured`) VALUES
(1, 'Ahmad Rizki', 'A.R.', 'Jakarta', 'Alhamdulillah, setelah 3 sesi dengan Ustadz Ahmad, kecemasan saya yang sudah 2 tahun akhirnya bisa teratasi. Pendekatan Islamnya sangat menenangkan dan membuat saya merasa aman. Sangat merekomendasikan!', 5, 1, 1),
(2, 'Ibu Nurhasanah', 'N.H.', 'Bandung', 'Anak saya yang susah makan akhirnya mau makan sayur setelah terapi dengan Dr. Siti. Prosesnya menyenangkan dan anak saya tidak merasa takut sama sekali. Terima kasih banyak!', 5, 2, 1),
(3, 'Dewi Safitri', 'D.S.', 'Surabaya', 'Saya sangat bersyukur menemukan Ustadzah Fatimah. Sebagai wanita, saya merasa nyaman curhat tentang masalah rumah tangga saya. Beliau sangat sabar dan memberikan solusi yang bijak berdasarkan Al-Quran dan Sunnah.', 5, 3, 1),
(4, 'Budi Santoso', 'B.S.', 'Yogyakarta', 'Trauma masa kecil yang saya pendam selama 20 tahun akhirnya bisa dilepaskan. Proses hipnoterapinya sangat profesional dan saya merasa lebih ringan sekarang. Jazakallahu khairan!', 5, 1, 1),
(5, 'Rina Amalia', 'R.A.', 'Semarang', 'Anak saya yang kena bullying di sekolah sekarang jadi lebih percaya diri setelah terapi. Dr. Siti sangat memahami psikologi anak dan pendekatannya sangat tepat.', 5, 2, 1),
(6, 'Hana Permata', 'H.P.', 'Malang', 'Konflik dengan suami yang sudah berlarut-larut akhirnya bisa diselesaikan dengan baik setelah konseling dengan Ustadzah Fatimah. Rumah tangga kami sekarang jauh lebih harmonis.', 5, 3, 1);

-- =====================================================
-- SEED DATA: FAQs
-- =====================================================
INSERT INTO `faqs` (`id`, `question`, `answer`, `sort_order`, `is_active`) VALUES
(1, 'Apa itu Hipnoterapi Islami?', 'Hipnoterapi Islami adalah metode terapi yang menggabungkan teknik hipnoterapi modern dengan nilai-nilai dan prinsip Islam. Terapi ini menggunakan kondisi relaksasi mendalam (trance) untuk mengakses pikiran bawah sadar, dikombinasikan dengan doa, dzikir, dan bimbingan spiritual sesuai syariat Islam.', 1, 1),
(2, 'Apakah Hipnoterapi itu halal?', 'Ya, hipnoterapi yang dilakukan dengan tujuan menyembuhkan dan membantu seseorang adalah halal. Yang diharamkan adalah hipnosis untuk tujuan hiburan (hypnosis show) atau merugikan orang lain. Di Albashiro, kami memastikan setiap sesi terapi sesuai dengan syariat Islam.', 2, 1),
(3, 'Berapa lama durasi satu sesi terapi?', 'Durasi satu sesi terapi berkisar antara 90-120 menit, tergantung jenis layanan dan kebutuhan klien. Sesi pertama biasanya lebih lama karena mencakup konsultasi awal dan pengisian form data.', 3, 1),
(4, 'Berapa kali sesi yang dibutuhkan untuk sembuh?', 'Setiap orang berbeda-beda. Beberapa klien merasakan perubahan signifikan setelah 1-2 sesi, sementara yang lain membutuhkan 3-5 sesi. Terapis akan mendiskusikan rencana terapi yang sesuai dengan kondisi Anda.', 4, 1),
(5, 'Apakah bisa terapi online?', 'Ya, kami menyediakan layanan terapi online via video call untuk klien yang tidak bisa datang langsung. Efektivitasnya sama dengan terapi offline, asalkan koneksi internet stabil dan berada di ruangan yang tenang.', 5, 1),
(6, 'Apakah rahasia klien terjaga?', 'Sangat terjaga. Kami menjunjung tinggi kode etik profesi dan menjamin kerahasiaan semua informasi klien. Data dan isi sesi terapi tidak akan dibagikan kepada siapapun tanpa izin klien.', 6, 1),
(7, 'Siapa saja yang bisa menjalani hipnoterapi?', 'Hipnoterapi aman untuk semua usia, mulai dari anak-anak (minimal 5 tahun) hingga dewasa. Namun, tidak disarankan untuk orang dengan gangguan mental berat (seperti skizofrenia) tanpa rekomendasi dokter.', 7, 1),
(8, 'Bagaimana cara membuat janji?', 'Anda bisa mengisi form reservasi di website ini atau langsung menghubungi kami via WhatsApp. Setelah itu, tim kami akan mengkonfirmasi jadwal yang tersedia dan memberikan detail lokasi/link meeting.', 8, 1);

-- =====================================================
-- SEED DATA: Site Settings
-- =====================================================
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`) VALUES
('site_name', 'Albashiro', 'text'),
('site_tagline', 'Islamic Spiritual Hypnotherapy', 'text'),
('admin_whatsapp', '6282228967897', 'text'),
('admin_email', 'info@albashiro.com', 'text'),
('address', 'Jl. Imam Bonjol No. 123, Jakarta Pusat, DKI Jakarta 10310, Indonesia', 'text'),
('operating_hours', 'Senin - Sabtu: 09:00 - 17:00 WIB | Minggu: Tutup', 'text'),
('instagram', 'https://instagram.com/albashiro', 'text'),
('facebook', 'https://facebook.com/albashiro', 'text'),
('youtube', 'https://youtube.com/@albashiro', 'text'),
('tiktok', 'https://tiktok.com/@albashiro', 'text'),
('fonnte_api_token', 'baXPGAQDBSfTe3vQ84W8', 'text'),
('fonnte_group_id', '120363422821859147@g.us', 'text');

-- =====================================================
-- SEED DATA: Admin User
-- Password: admin123
-- =====================================================
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `is_active`) VALUES
(1, 'Administrator', 'admin@albashiro.com', '$2y$12$kvw/bsfKfHvglz5W1xEqkeef.4jlsqvIMXg1ksYrb6HCxbXxs4VOG', 'admin', 1);

-- =====================================================
-- SEED DATA: Blog Posts
-- =====================================================
INSERT INTO `blog_posts` (`id`, `title`, `slug`, `excerpt`, `content`, `author_id`, `category`, `tags`, `status`, `published_at`) VALUES
(1, 'Apa Itu Hipnoterapi Islami? Panduan Lengkap untuk Pemula', 'apa-itu-hipnoterapi-islami', 
'Hipnoterapi Islami adalah metode terapi yang menggabungkan teknik hipnoterapi modern dengan nilai-nilai Islam. Pelajari lebih lanjut tentang bagaimana terapi ini dapat membantu Anda.',
'<h2>Pengertian Hipnoterapi Islami</h2><p>Hipnoterapi Islami adalah pendekatan terapeutik yang menggabungkan teknik hipnoterapi klinis dengan bimbingan spiritual berdasarkan Al-Quran dan Sunnah.</p>',
1, 'Edukasi', 'hipnoterapi,islami,kesehatan mental', 'published', '2025-12-24 14:34:27'),

(2, 'Mengatasi Kecemasan dengan Pendekatan Islami', 'mengatasi-kecemasan-pendekatan-islami',
'Kecemasan adalah masalah yang banyak dialami masyarakat modern. Bagaimana cara mengatasinya dengan pendekatan yang sesuai syariat Islam?',
'<h2>Memahami Kecemasan</h2><p>Kecemasan adalah respons alami tubuh terhadap stres. Namun, ketika kecemasan menjadi berlebihan dan mengganggu kehidupan sehari-hari, diperlukan penanganan yang tepat.</p>',
1, 'Tips Kesehatan', 'kecemasan,anxiety,mental health', 'published', '2025-12-24 14:34:27'),

(3, 'Tips Memilih Terapis Hipnoterapi yang Tepat', 'tips-memilih-terapis-hipnoterapi',
'Memilih terapis yang tepat adalah langkah penting dalam perjalanan penyembuhan Anda. Berikut panduan untuk memilih terapis hipnoterapi yang sesuai.',
'<h2>Pentingnya Memilih Terapis yang Tepat</h2><p>Keberhasilan hipnoterapi sangat bergantung pada kualitas terapis dan hubungan terapeutik yang terbangun.</p>',
1, 'Tips', 'terapis,hipnoterapi,tips', 'published', '2025-12-24 14:34:27');

SET FOREIGN_KEY_CHECKS=1;
