/*!40014 SET FOREIGN_KEY_CHECKS=0*/;
/*!40101 SET NAMES binary*/;
CREATE TABLE `knowledge_suggestions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `question` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `question_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'MD5 hash for deduplication',
  `keywords` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `frequency` int DEFAULT '1',
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `suggested_answer` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `priority` int DEFAULT '5' COMMENT '1=highest, 10=lowest',
  `first_asked` timestamp DEFAULT CURRENT_TIMESTAMP,
  `last_asked` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `approved_by` int unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  UNIQUE KEY `idx_question_hash` (`question_hash`),
  KEY `idx_status` (`status`),
  KEY `idx_frequency` (`frequency`),
  KEY `idx_priority` (`priority`),
  KEY `idx_first_asked` (`first_asked`),
  KEY `idx_suggestions_status_frequency` (`status`,`frequency`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=389392;
