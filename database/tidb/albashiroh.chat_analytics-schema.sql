/*!40014 SET FOREIGN_KEY_CHECKS=0*/;
/*!40101 SET NAMES binary*/;
CREATE TABLE `chat_analytics` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `total_conversations` int DEFAULT '0',
  `knowledge_matched` int DEFAULT '0' COMMENT 'Conversations with match >= 2',
  `knowledge_not_matched` int DEFAULT '0' COMMENT 'Conversations with match < 2',
  `avg_response_time_ms` int DEFAULT '0',
  `unique_sessions` int DEFAULT '0',
  `new_suggestions` int DEFAULT '0',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  UNIQUE KEY `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=3763393;
