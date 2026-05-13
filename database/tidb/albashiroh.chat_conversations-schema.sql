/*!40014 SET FOREIGN_KEY_CHECKS=0*/;
/*!40101 SET NAMES binary*/;
CREATE TABLE `chat_conversations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `chat_session_id` int DEFAULT NULL,
  `session_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ai_response` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `knowledge_matched` int DEFAULT '0',
  `keywords_searched` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `response_time_ms` int DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `idx_session` (`session_id`),
  KEY `idx_created` (`created_at`),
  KEY `idx_knowledge` (`knowledge_matched`),
  KEY `idx_conversations_session_created` (`session_id`,`created_at`),
  KEY `idx_chat_session` (`chat_session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1872158;
