/*!40014 SET FOREIGN_KEY_CHECKS=0*/;
/*!40101 SET NAMES binary*/;
CREATE TABLE `knowledge_vectors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `source_table` varchar(255) NOT NULL,
  `source_id` int NOT NULL,
  `article_id` int NOT NULL,
  `content_text` text NOT NULL,
  `embedding` vector(384) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `idx_article` (`article_id`),
  KEY `idx_source` (`source_table`,`source_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin AUTO_INCREMENT=150001;
