-- =====================================================
-- AUTO-LEARNING SYSTEM DATABASE SCHEMA
-- Migration: 002_auto_learning_system.sql
-- Purpose: Enable AI to automatically detect and suggest knowledge gaps
-- =====================================================

-- Conversation tracking with quality metrics
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

-- Knowledge gap suggestions with frequency tracking
CREATE TABLE IF NOT EXISTS `knowledge_suggestions` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `question` TEXT NOT NULL,
    `question_hash` VARCHAR(64) NOT NULL COMMENT 'MD5 hash for deduplication',
    `keywords` TEXT,
    `frequency` INT DEFAULT 1,
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `suggested_answer` TEXT,
    `category` VARCHAR(100),
    `priority` INT DEFAULT 5 COMMENT '1=highest, 10=lowest',
    `first_asked` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `last_asked` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `approved_by` INT(11) UNSIGNED NULL,
    `approved_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_question_hash` (`question_hash`),
    KEY `idx_status` (`status`),
    KEY `idx_frequency` (`frequency` DESC),
    KEY `idx_priority` (`priority` ASC),
    KEY `idx_first_asked` (`first_asked`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Daily analytics aggregation
CREATE TABLE IF NOT EXISTS `chat_analytics` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `date` DATE NOT NULL,
    `total_conversations` INT DEFAULT 0,
    `knowledge_matched` INT DEFAULT 0 COMMENT 'Conversations with match >= 2',
    `knowledge_not_matched` INT DEFAULT 0 COMMENT 'Conversations with match < 2',
    `avg_response_time_ms` INT DEFAULT 0,
    `unique_sessions` INT DEFAULT 0,
    `new_suggestions` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert initial analytics record for today
INSERT INTO `chat_analytics` (`date`, `total_conversations`, `knowledge_matched`, `knowledge_not_matched`, `avg_response_time_ms`, `unique_sessions`)
VALUES (CURDATE(), 0, 0, 0, 0, 0)
ON DUPLICATE KEY UPDATE `date` = `date`;

-- Create indexes for performance
CREATE INDEX `idx_conversations_session_created` ON `chat_conversations` (`session_id`, `created_at`);
CREATE INDEX `idx_suggestions_status_frequency` ON `knowledge_suggestions` (`status`, `frequency` DESC);
