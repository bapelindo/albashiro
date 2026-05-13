/*!40014 SET FOREIGN_KEY_CHECKS=0*/;
/*!40101 SET NAMES binary*/;
CREATE TABLE `availability_overrides` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `therapist_id` int unsigned NOT NULL,
  `override_date` date NOT NULL,
  `is_available` tinyint(1) DEFAULT '0',
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  UNIQUE KEY `unique_therapist_date` (`therapist_id`,`override_date`),
  KEY `idx_date` (`override_date`),
  CONSTRAINT `fk_override_therapist` FOREIGN KEY (`therapist_id`) REFERENCES `albashiro`.`therapists` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=60001;
