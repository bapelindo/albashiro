/*!40014 SET FOREIGN_KEY_CHECKS=0*/;
/*!40101 SET NAMES binary*/;
CREATE TABLE `reschedule_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int unsigned NOT NULL,
  `old_date` date NOT NULL,
  `old_time` time NOT NULL,
  `new_date` date NOT NULL,
  `new_time` time NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rescheduled_by` enum('admin','client') COLLATE utf8mb4_unicode_ci DEFAULT 'admin',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `idx_booking` (`booking_id`),
  CONSTRAINT `fk_reschedule_booking` FOREIGN KEY (`booking_id`) REFERENCES `albashiro`.`bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
