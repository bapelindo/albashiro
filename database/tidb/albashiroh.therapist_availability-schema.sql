/*!40014 SET FOREIGN_KEY_CHECKS=0*/;
/*!40101 SET NAMES binary*/;
CREATE TABLE `therapist_availability` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `therapist_id` int unsigned NOT NULL,
  `day_of_week` enum('monday','tuesday','wednesday','thursday','friday','saturday','sunday') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_available` tinyint(1) DEFAULT '1',
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  UNIQUE KEY `unique_therapist_day` (`therapist_id`,`day_of_week`),
  KEY `idx_therapist` (`therapist_id`),
  KEY `idx_day` (`day_of_week`),
  CONSTRAINT `fk_availability_therapist` FOREIGN KEY (`therapist_id`) REFERENCES `albashiro`.`therapists` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=150001;
