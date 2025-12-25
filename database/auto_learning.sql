-- =====================================================
-- AUTO-LEARNING SYSTEM DATABASE SCHEMA
-- =====================================================

-- Conversation tracking
CREATE TABLE IF NOT EXISTS `chat_conversations` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `session_id` VARCHAR(100) NOT NULL,
    `user_message` TEXT NOT NULL,
    `ai_response` TEXT NOT NULL,
    `knowledge_matched` INT DEFAULT 0,
    `keywords_searched` TEXT,
    `response_time_ms` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_session` (`session_id`),
    KEY `idx_created` (`created_at`),
    KEY `idx_knowledge` (`knowledge_matched`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Knowledge gap suggestions
CREATE TABLE IF NOT EXISTS `knowledge_suggestions` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `question` TEXT NOT NULL,
    `keywords` TEXT,
    `frequency` INT DEFAULT 1,
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `suggested_answer` TEXT,
    `category` VARCHAR(100),
    `priority` INT DEFAULT 5,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_status` (`status`),
    KEY `idx_frequency` (`frequency`),
    KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Learning analytics
CREATE TABLE IF NOT EXISTS `chat_analytics` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `date` DATE NOT NULL,
    `total_conversations` INT DEFAULT 0,
    `knowledge_matched` INT DEFAULT 0,
    `knowledge_not_matched` INT DEFAULT 0,
    `avg_response_time_ms` INT DEFAULT 0,
    `unique_sessions` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
