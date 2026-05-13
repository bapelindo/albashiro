/*!40014 SET FOREIGN_KEY_CHECKS=0*/;
/*!40101 SET NAMES binary*/;
CREATE TABLE `reminder_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int unsigned NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `wa_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sent_at` datetime NOT NULL,
  `delivery_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `idx_booking_id` (`booking_id`),
  KEY `idx_sent_at` (`sent_at`),
  KEY `idx_status` (`delivery_status`),
  CONSTRAINT `fk_reminder_booking` FOREIGN KEY (`booking_id`) REFERENCES `albashiro`.`bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=176948;
