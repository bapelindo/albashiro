<?php
/**
 * AiLog Model
 * Handles AI performance logging and bottleneck analysis
 */
class AiLog
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Log AI performance metrics
     * 
     * @param array $data Performance data
     * @return bool Success status
     */
    public function logPerformance($data)
    {
        try {
            $sql = "INSERT INTO ai_performance_logs (
                session_id, user_message, ai_response, provider, model,
                total_time_ms, api_call_time_ms, 
                db_services_time_ms, db_therapists_time_ms, 
                db_schedule_time_ms, db_knowledge_time_ms,
                context_build_time_ms,
                knowledge_matched, keywords_searched,
                error_occurred, error_message, 
                fallback_used, fallback_reason
            ) VALUES (
                :session_id, :user_message, :ai_response, :provider, :model,
                :total_time_ms, :api_call_time_ms,
                :db_services_time_ms, :db_therapists_time_ms,
                :db_schedule_time_ms, :db_knowledge_time_ms,
                :context_build_time_ms,
                :knowledge_matched, :keywords_searched,
                :error_occurred, :error_message,
                :fallback_used, :fallback_reason
            )";

            $this->db->query($sql, [
                'session_id' => $data['session_id'] ?? (session_status() === PHP_SESSION_ACTIVE ? session_id() : 'no-session'),
                'user_message' => $data['user_message'] ?? '',
                'ai_response' => $data['ai_response'] ?? '',
                'provider' => $data['provider'] ?? 'Unknown',
                'model' => $data['model'] ?? null,
                'total_time_ms' => $data['total_time_ms'] ?? 0,
                'api_call_time_ms' => $data['api_call_time_ms'] ?? null,
                'db_services_time_ms' => $data['db_services_time_ms'] ?? null,
                'db_therapists_time_ms' => $data['db_therapists_time_ms'] ?? null,
                'db_schedule_time_ms' => $data['db_schedule_time_ms'] ?? null,
                'db_knowledge_time_ms' => $data['db_knowledge_time_ms'] ?? null,
                'context_build_time_ms' => $data['context_build_time_ms'] ?? null,
                'knowledge_matched' => $data['knowledge_matched'] ?? 0,
                'keywords_searched' => $data['keywords_searched'] ?? null,
                'error_occurred' => $data['error_occurred'] ?? 0,
                'error_message' => $data['error_message'] ?? null,
                'fallback_used' => $data['fallback_used'] ?? 0,
                'fallback_reason' => $data['fallback_reason'] ?? null
            ]);

            return true;
        } catch (Exception $e) {
            // Ignore
            return false;
        }
    }

    /**
     * Get recent logs with filters
     * 
     * @param array $filters Filter options
     * @param int $limit Number of records
     * @return array Log entries
     */
    public function getRecentLogs($filters = [], $limit = 100)
    {
        $where = [];
        $params = [];

        if (!empty($filters['provider'])) {
            $where[] = "provider = :provider";
            $params['provider'] = $filters['provider'];
        }

        if (!empty($filters['error_only'])) {
            $where[] = "error_occurred = 1";
        }

        if (!empty($filters['date_from'])) {
            $where[] = "created_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "created_at <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        if (!empty($filters['slow_only'])) {
            $where[] = "total_time_ms > :slow_threshold";
            $params['slow_threshold'] = $filters['slow_threshold'] ?? 3000;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Cast limit to int for security
        $limit = (int) $limit;

        $sql = "SELECT * FROM ai_performance_logs 
                $whereClause 
                ORDER BY created_at DESC 
                LIMIT $limit";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get average response time statistics
     * 
     * @param string $provider Filter by provider (optional)
     * @param int $days Number of days to analyze
     * @return array Statistics
     */
    public function getAverageResponseTime($provider = null, $days = 7)
    {
        $where = "WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)";
        $params = ['days' => $days];

        if ($provider) {
            $where .= " AND provider = :provider";
            $params['provider'] = $provider;
        }

        $sql = "SELECT 
                    AVG(total_time_ms) as avg_total,
                    AVG(api_call_time_ms) as avg_api,
                    AVG(db_services_time_ms) as avg_services,
                    AVG(db_therapists_time_ms) as avg_therapists,
                    AVG(db_schedule_time_ms) as avg_schedule,
                    AVG(db_knowledge_time_ms) as avg_knowledge,
                    AVG(context_build_time_ms) as avg_context,
                    MIN(total_time_ms) as min_total,
                    MAX(total_time_ms) as max_total,
                    COUNT(*) as total_requests
                FROM ai_performance_logs 
                $where";

        return $this->db->query($sql, $params)->fetch();
    }

    /**
     * Get slow queries (bottleneck identification)
     * 
     * @param int $threshold Threshold in milliseconds
     * @param int $limit Number of records
     * @return array Slow queries
     */
    public function getSlowQueries($threshold = 3000, $limit = 50)
    {
        $limit = (int) $limit;

        $sql = "SELECT 
                    id, session_id, user_message, provider, 
                    total_time_ms, api_call_time_ms,
                    db_services_time_ms, db_therapists_time_ms,
                    db_schedule_time_ms, db_knowledge_time_ms,
                    context_build_time_ms, created_at
                FROM ai_performance_logs 
                WHERE total_time_ms > :threshold 
                ORDER BY total_time_ms DESC 
                LIMIT $limit";

        return $this->db->query($sql, [
            'threshold' => (int) $threshold
        ])->fetchAll();
    }

    /**
     * Get error rate statistics
     * 
     * @param int $days Number of days to analyze
     * @return array Error statistics
     */
    public function getErrorRate($days = 7)
    {
        $sql = "SELECT 
                    COUNT(*) as total_requests,
                    SUM(error_occurred) as total_errors,
                    SUM(fallback_used) as total_fallbacks,
                    ROUND((SUM(error_occurred) / COUNT(*)) * 100, 2) as error_rate,
                    ROUND((SUM(fallback_used) / COUNT(*)) * 100, 2) as fallback_rate
                FROM ai_performance_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)";

        $result = $this->db->query($sql, ['days' => $days])->fetch();
        return $result ?: (object) [
            'total_requests' => 0,
            'total_errors' => 0,
            'total_fallbacks' => 0,
            'error_rate' => 0,
            'fallback_rate' => 0
        ];
    }

    /**
     * Get provider usage statistics
     * 
     * @param int $days Number of days to analyze
     * @return array Provider statistics
     */
    public function getProviderStats($days = 7)
    {
        $sql = "SELECT 
                    provider,
                    COUNT(*) as request_count,
                    AVG(total_time_ms) as avg_response_time,
                    SUM(error_occurred) as error_count,
                    ROUND((SUM(error_occurred) / COUNT(*)) * 100, 2) as error_rate
                FROM ai_performance_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                GROUP BY provider
                ORDER BY request_count DESC";

        return $this->db->query($sql, ['days' => $days])->fetchAll();
    }

    /**
     * Automatic bottleneck analysis
     * Identifies which component is causing slowdowns
     * 
     * @param int $days Number of days to analyze
     * @return array Bottleneck analysis
     */
    public function getBottleneckAnalysis($days = 7)
    {
        $stats = $this->getAverageResponseTime(null, $days);

        // Ensure $stats is an object even if no data exists
        if (!$stats) {
            $stats = (object) [
                'avg_total' => 0,
                'avg_api' => 0,
                'avg_services' => 0,
                'avg_therapists' => 0,
                'avg_schedule' => 0,
                'avg_knowledge' => 0,
                'avg_context' => 0,
                'total_requests' => 0
            ];
        } else {
            $stats = (object) $stats;
        }

        // Enhanced component breakdown
        $components = [
            // Main components
            'API Call (Ollama)' => $stats->avg_api ?? 0,
            'Context Building' => $stats->avg_context ?? 0,

            // Database queries (detailed)
            'DB: Services Query' => $stats->avg_services ?? 0,
            'DB: Therapists Query' => $stats->avg_therapists ?? 0,
            'DB: Schedule Query' => $stats->avg_schedule ?? 0,
            'DB: Knowledge Search' => $stats->avg_knowledge ?? 0,
        ];

        // Sort by time (descending)
        arsort($components);

        $total = array_sum($components);
        $analysis = [];

        foreach ($components as $component => $time) {
            if ($time > 0) {
                $percentage = $total > 0 ? round(($time / $total) * 100, 2) : 0;

                // Determine severity and recommendation
                $severity = 'low';
                $recommendation = '';

                if ($percentage > 40) {
                    $severity = 'critical';
                    $recommendation = 'URGENT: Major bottleneck detected!';
                } elseif ($percentage > 30) {
                    $severity = 'high';
                    $recommendation = 'High impact - needs optimization';
                } elseif ($percentage > 20) {
                    $severity = 'medium';
                    $recommendation = 'Moderate impact - consider optimizing';
                }

                // Component-specific recommendations
                if (strpos($component, 'API Call') !== false && $time > 1000) {
                    $recommendation .= ' | Reduce num_ctx/num_predict or use faster model';
                } elseif (strpos($component, 'Knowledge Search') !== false && $time > 500) {
                    $recommendation .= ' | Add vector index or pre-filter results';
                } elseif (strpos($component, 'Context Building') !== false && $time > 300) {
                    $recommendation .= ' | Enable context caching';
                } elseif (strpos($component, 'DB:') !== false && $time > 100) {
                    $recommendation .= ' | Add caching or optimize query';
                }

                $analysis[] = [
                    'component' => $component,
                    'avg_time_ms' => round($time, 2),
                    'percentage' => $percentage,
                    'is_bottleneck' => $percentage > 30,
                    'severity' => $severity,
                    'recommendation' => $recommendation
                ];
            }
        }

        return [
            'total_avg_time' => round($stats->avg_total ?? 0, 2),
            'components' => $analysis,
            'total_requests' => $stats->total_requests ?? 0,
            'optimization_score' => $this->calculateOptimizationScore($analysis)
        ];
    }

    /**
     * Calculate optimization score (0-100)
     * Higher is better
     */
    private function calculateOptimizationScore($analysis)
    {
        $score = 100;

        foreach ($analysis as $component) {
            if ($component['severity'] === 'critical') {
                $score -= 30;
            } elseif ($component['severity'] === 'high') {
                $score -= 20;
            } elseif ($component['severity'] === 'medium') {
                $score -= 10;
            }
        }

        return max(0, $score);
    }

    /**
     * Get performance trends over time
     * 
     * @param int $days Number of days
     * @return array Daily performance data
     */
    public function getPerformanceTrends($days = 7)
    {
        $sql = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as requests,
                    AVG(total_time_ms) as avg_time,
                    SUM(error_occurred) as errors
                FROM ai_performance_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC";

        return $this->db->query($sql, ['days' => $days])->fetchAll();
    }

    /**
     * Delete old logs (cleanup)
     * 
     * @param int $days Keep logs from last N days
     * @return int Number of deleted records
     */
    public function cleanupOldLogs($days = 30)
    {
        $sql = "DELETE FROM ai_performance_logs 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";

        $this->db->query($sql, ['days' => $days]);
        return $this->db->rowCount();
    }
}
