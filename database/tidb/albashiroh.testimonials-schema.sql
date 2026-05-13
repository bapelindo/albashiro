/*!40014 SET FOREIGN_KEY_CHECKS=0*/;
/*!40101 SET NAMES binary*/;
CREATE TABLE `testimonials` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `client_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_initial` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'For privacy, e.g., A.S.',
  `client_location` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` tinyint(1) DEFAULT '5' COMMENT '1-5 stars',
  `therapist_id` int unsigned DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT '0',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `therapist_id` (`therapist_id`),
  CONSTRAINT `fk_testimonial_therapist` FOREIGN KEY (`therapist_id`) REFERENCES `albashiro`.`therapists` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=30002;
