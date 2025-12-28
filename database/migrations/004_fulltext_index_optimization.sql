-- =====================================================
-- TiDB-Compatible Optimization
-- TiDB doesn't support FULLTEXT, use regular indexes instead
-- =====================================================

-- Add regular indexes for faster LIKE queries
ALTER TABLE `faqs` 
ADD INDEX `idx_question` (`question`(100)),
ADD INDEX `idx_answer` (`answer`(100));

-- Verify indexes created
SHOW INDEX FROM `faqs` WHERE Key_name IN ('idx_question', 'idx_answer');
