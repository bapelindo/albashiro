/*!40014 SET FOREIGN_KEY_CHECKS=0*/;
/*!40101 SET NAMES binary*/;
CREATE TABLE `ai_knowledge_base` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'services, therapists, faq, process, policies, conditions, etc',
  `topic` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `question` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `answer` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `keywords` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Comma-separated keywords for search',
  `priority` int DEFAULT '0' COMMENT 'Higher = more important',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `idx_category` (`category`),
  KEY `idx_priority` (`priority`),
  KEY `idx_active` (`is_active`),
  KEY `idx_question` (`question`(100)),
  KEY `idx_answer` (`answer`(100)),
  KEY `idx_keywords` (`keywords`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1015936;
