/*!40014 SET FOREIGN_KEY_CHECKS=0*/;
/*!40101 SET NAMES binary*/;
CREATE TABLE `bookings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `booking_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `therapist_id` int unsigned NOT NULL,
  `service_id` int unsigned DEFAULT NULL,
  `client_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `wa_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `problem_description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time DEFAULT NULL,
  `status` enum('pending','confirmed','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reminder_sent` tinyint(1) DEFAULT '0' COMMENT 'WhatsApp reminder sent flag',
  `reminder_sent_at` datetime DEFAULT NULL COMMENT 'When reminder was sent',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  UNIQUE KEY `booking_code` (`booking_code`),
  KEY `therapist_id` (`therapist_id`),
  KEY `service_id` (`service_id`),
  KEY `idx_reminder` (`appointment_date`,`appointment_time`,`reminder_sent`),
  CONSTRAINT `fk_booking_therapist` FOREIGN KEY (`therapist_id`) REFERENCES `albashiro`.`therapists` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_booking_service` FOREIGN KEY (`service_id`) REFERENCES `albashiro`.`services` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=240001;
