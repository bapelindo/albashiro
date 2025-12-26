-- =====================================================
-- AI Performance Logs Table
-- Tracks detailed performance metrics for AI chatbot
-- =====================================================

CREATE TABLE IF NOT EXISTS `ai_performance_logs` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `session_id` VARCHAR(50) NOT NULL COMMENT 'Chat session identifier',
    `user_message` TEXT NOT NULL COMMENT 'User input message',
    `ai_response` TEXT NOT NULL COMMENT 'AI response text',
    
    -- Provider Information
    `provider` VARCHAR(100) NOT NULL COMMENT 'AI Provider: Google Gemini/Ollama/HuggingFace',
    `model` VARCHAR(100) DEFAULT NULL COMMENT 'Model name used',
    
    -- Performance Metrics (in milliseconds)
    `total_time_ms` INT(11) NOT NULL COMMENT 'Total response time',
    `api_call_time_ms` INT(11) DEFAULT NULL COMMENT 'Time for API call to provider',
    `db_services_time_ms` INT(11) DEFAULT NULL COMMENT 'Time to query services',
    `db_therapists_time_ms` INT(11) DEFAULT NULL COMMENT 'Time to query therapists',
    `db_schedule_time_ms` INT(11) DEFAULT NULL COMMENT 'Time to query schedules',
    `db_knowledge_time_ms` INT(11) DEFAULT NULL COMMENT 'Time to query knowledge base',
    `context_build_time_ms` INT(11) DEFAULT NULL COMMENT 'Time to build system context',
    
    -- Knowledge Base Metrics
    `knowledge_matched` INT(11) DEFAULT 0 COMMENT 'Number of knowledge base entries matched',
    `keywords_searched` VARCHAR(500) DEFAULT NULL COMMENT 'Keywords used for search',
    
    -- Error Tracking
    `error_occurred` TINYINT(1) DEFAULT 0 COMMENT 'Whether an error occurred',
    `error_message` TEXT DEFAULT NULL COMMENT 'Error message if any',
    `fallback_used` TINYINT(1) DEFAULT 0 COMMENT 'Whether fallback provider was used',
    `fallback_reason` VARCHAR(255) DEFAULT NULL COMMENT 'Reason for fallback',
    
    -- Timestamps
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`id`),
    KEY `idx_session` (`session_id`),
    KEY `idx_provider` (`provider`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_error` (`error_occurred`),
    KEY `idx_total_time` (`total_time_ms`),
    KEY `idx_session_created` (`session_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
