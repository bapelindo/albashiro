<?php
/**
 * AI Performance Monitoring Dashboard
 * Shows detailed performance metrics and bottleneck analysis
 */
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'AI Performance' ?> - Albashiro Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
    <?php include __DIR__ . '/includes/dark-mode-styles.php'; ?>
</head>

<body class="bg-gray-100 min-h-screen">
    <?php
    $activeMenu = 'ai-performance';
    include __DIR__ . '/includes/sidebar.php';
    ?>

    <!-- Main Content -->
    <main class="ml-64 p-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                <i class="fas fa-chart-line text-blue-600"></i> AI Performance Monitoring
            </h1>
            <p class="text-gray-600">Analisis performa AI chatbot dan identifikasi bottleneck</p>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <form method="GET" action="<?= base_url('admin/aiPerformance') ?>" class="flex flex-wrap gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Periode</label>
                    <select name="days" class="border rounded px-3 py-2">
                        <option value="1" <?= ($currentDays == 1) ? 'selected' : '' ?>>24 Jam Terakhir</option>
                        <option value="7" <?= ($currentDays == 7) ? 'selected' : '' ?>>7 Hari Terakhir</option>
                        <option value="30" <?= ($currentDays == 30) ? 'selected' : '' ?>>30 Hari Terakhir</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Provider</label>
                    <select name="provider" class="border rounded px-3 py-2">
                        <option value="">Semua Provider</option>
                        <option value="Local Ollama" <?= ($currentProvider == 'Local Ollama') ? 'selected' : '' ?>>Local
                            Ollama</option>
                        <option value="Google Gemini" <?= ($currentProvider == 'Google Gemini') ? 'selected' : '' ?>>Google
                            Gemini</option>
                        <option value="Hugging Face" <?= ($currentProvider == 'Hugging Face') ? 'selected' : '' ?>>Hugging
                            Face</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="error_only" <?= $errorOnly ? 'checked' : '' ?> class="mr-2">
                        <span class="text-sm">Error Saja</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="slow_only" <?= $slowOnly ? 'checked' : '' ?> class="mr-2">
                        <span class="text-sm">Lambat Saja (&gt;3s)</span>
                    </label>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Avg Response Time -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-600">Avg Response Time</h3>
                    <i class="fas fa-clock text-blue-500"></i>
                </div>
                <p class="text-3xl font-bold text-gray-900"><?= number_format($avgStats->avg_total ?? 0, 0) ?>ms</p>
                <p class="text-xs text-gray-500 mt-1">Min: <?= number_format($avgStats->min_total ?? 0, 0) ?>ms | Max:
                    <?= number_format($avgStats->max_total ?? 0, 0) ?>ms
                </p>
            </div>

            <!-- Total Requests -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-600">Total Requests</h3>
                    <i class="fas fa-comments text-green-500"></i>
                </div>
                <p class="text-3xl font-bold text-gray-900"><?= number_format($avgStats->total_requests ?? 0) ?></p>
                <p class="text-xs text-gray-500 mt-1">Dalam <?= $currentDays ?> hari terakhir</p>
            </div>

            <!-- Error Rate -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-600">Error Rate</h3>
                    <i class="fas fa-exclamation-triangle text-red-500"></i>
                </div>
                <p class="text-3xl font-bold text-gray-900"><?= number_format($errorStats->error_rate ?? 0, 2) ?>%</p>
                <p class="text-xs text-gray-500 mt-1"><?= $errorStats->total_errors ?? 0 ?> errors dari
                    <?= $errorStats->total_requests ?? 0 ?> requests
                </p>
            </div>

            <!-- Fallback Rate -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-600">Fallback Rate</h3>
                    <i class="fas fa-exchange-alt text-yellow-500"></i>
                </div>
                <p class="text-3xl font-bold text-gray-900"><?= number_format($errorStats->fallback_rate ?? 0, 2) ?>%
                </p>
                <p class="text-xs text-gray-500 mt-1"><?= $errorStats->total_fallbacks ?? 0 ?> fallbacks</p>
            </div>
        </div>

        <!-- Bottleneck Analysis -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900">
                    <i class="fas fa-search text-red-600"></i> Bottleneck Analysis
                </h2>
                <?php if (isset($bottleneckAnalysis['optimization_score'])): ?>
                    <div class="text-right">
                        <div class="text-sm text-gray-600">Optimization Score</div>
                        <div
                            class="text-3xl font-bold <?= $bottleneckAnalysis['optimization_score'] >= 80 ? 'text-green-600' : ($bottleneckAnalysis['optimization_score'] >= 60 ? 'text-yellow-600' : 'text-red-600') ?>">
                            <?= $bottleneckAnalysis['optimization_score'] ?>/100
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($bottleneckAnalysis['components'])): ?>
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-2">Total Avg Time:
                        <strong
                            class="<?= $bottleneckAnalysis['total_avg_time'] > 3000 ? 'text-red-600' : ($bottleneckAnalysis['total_avg_time'] > 2000 ? 'text-yellow-600' : 'text-green-600') ?>"><?= number_format($bottleneckAnalysis['total_avg_time'], 2) ?>ms</strong>
                    </p>
                </div>

                <div class="space-y-4">
                    <?php foreach ($bottleneckAnalysis['components'] as $component): ?>
                        <div
                            class="border rounded-lg p-4 <?= $component['severity'] === 'critical' ? 'border-red-300 bg-red-50' : ($component['severity'] === 'high' ? 'border-orange-300 bg-orange-50' : ($component['severity'] === 'medium' ? 'border-yellow-300 bg-yellow-50' : 'border-gray-200')) ?>">
                            <div class="flex justify-between items-center mb-2">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-sm font-medium <?= $component['is_bottleneck'] ? 'text-red-600' : 'text-gray-700' ?>">
                                        <?= htmlspecialchars($component['component']) ?>
                                    </span>
                                    <?php if ($component['severity'] === 'critical'): ?>
                                        <span class="text-xs bg-red-600 text-white px-2 py-1 rounded font-bold">CRITICAL</span>
                                    <?php elseif ($component['severity'] === 'high'): ?>
                                        <span class="text-xs bg-orange-600 text-white px-2 py-1 rounded font-bold">HIGH</span>
                                    <?php elseif ($component['severity'] === 'medium'): ?>
                                        <span class="text-xs bg-yellow-600 text-white px-2 py-1 rounded">MEDIUM</span>
                                    <?php endif; ?>
                                </div>
                                <span
                                    class="text-sm font-bold <?= $component['severity'] === 'critical' ? 'text-red-700' : ($component['severity'] === 'high' ? 'text-orange-700' : 'text-gray-600') ?>">
                                    <?= number_format($component['avg_time_ms'], 2) ?>ms (<?= $component['percentage'] ?>%)
                                </span>
                            </div>

                            <!-- Progress Bar -->
                            <div class="w-full bg-gray-200 rounded-full h-3 mb-2">
                                <div class="<?= $component['severity'] === 'critical' ? 'bg-red-600' : ($component['severity'] === 'high' ? 'bg-orange-600' : ($component['severity'] === 'medium' ? 'bg-yellow-600' : 'bg-blue-600')) ?> h-3 rounded-full transition-all"
                                    style="width: <?= $component['percentage'] ?>%"></div>
                            </div>

                            <!-- Recommendation -->
                            <?php if (!empty($component['recommendation'])): ?>
                                <div
                                    class="mt-2 text-xs <?= $component['severity'] === 'critical' ? 'text-red-700' : ($component['severity'] === 'high' ? 'text-orange-700' : 'text-gray-600') ?>">
                                    <i class="fas fa-lightbulb mr-1"></i>
                                    <strong>Recommendation:</strong> <?= htmlspecialchars($component['recommendation']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500">Tidak ada data untuk analisis</p>
            <?php endif; ?>
        </div>

        <!-- Provider Stats -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">
                <i class="fas fa-server text-purple-600"></i> Provider Statistics
            </h2>

            <?php if (!empty($providerStats)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Provider</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Requests</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Avg Time</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Errors</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Error Rate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($providerStats as $stat): ?>
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($stat->provider) ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600"><?= number_format($stat->request_count) ?></td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        <?= number_format($stat->avg_response_time, 0) ?>ms
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600"><?= $stat->error_count ?></td>
                                    <td class="px-4 py-3 text-sm">
                                        <span
                                            class="<?= $stat->error_rate > 10 ? 'text-red-600' : 'text-green-600' ?> font-medium">
                                            <?= number_format($stat->error_rate, 2) ?>%
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-500">Tidak ada data provider</p>
            <?php endif; ?>
        </div>

        <!-- Slow Queries -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">
                <i class="fas fa-turtle text-orange-600"></i> Slow Queries (&gt;3s)
            </h2>

            <?php if (!empty($slowQueries)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User Message
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Provider</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">API</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">DB</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($slowQueries as $query): ?>
                                <tr>
                                    <td class="px-4 py-3 text-xs text-gray-500">
                                        <?= date('d/m H:i', strtotime($query->created_at)) ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 max-w-xs truncate">
                                        <?= htmlspecialchars($query->user_message) ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars($query->provider) ?></td>
                                    <td class="px-4 py-3 text-sm font-bold text-red-600">
                                        <?= number_format($query->total_time_ms) ?>ms
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        <?= number_format($query->api_call_time_ms ?? 0) ?>ms
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        <?php
                                        $dbTotal = ($query->db_services_time_ms ?? 0) +
                                            ($query->db_therapists_time_ms ?? 0) +
                                            ($query->db_schedule_time_ms ?? 0) +
                                            ($query->db_knowledge_time_ms ?? 0);
                                        echo number_format($dbTotal);
                                        ?>ms
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-500">Tidak ada slow queries</p>
            <?php endif; ?>
        </div>

        <!-- Recent Logs -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">
                <i class="fas fa-list text-gray-600"></i> Recent Logs (50 Terakhir)
            </h2>

            <?php if (!empty($recentLogs)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Provider</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total Time</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Message</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($recentLogs as $log): ?>
                                <tr class="<?= $log->error_occurred ? 'bg-red-50' : '' ?>">
                                    <td class="px-3 py-2 text-xs text-gray-500 whitespace-nowrap">
                                        <?= date('d/m H:i:s', strtotime($log->created_at)) ?>
                                    </td>
                                    <td class="px-3 py-2 text-xs text-gray-700"><?= htmlspecialchars($log->provider) ?></td>
                                    <td
                                        class="px-3 py-2 text-xs font-medium <?= $log->total_time_ms > 3000 ? 'text-red-600' : 'text-green-600' ?>">
                                        <?= number_format($log->total_time_ms) ?>ms
                                    </td>
                                    <td class="px-3 py-2 text-xs">
                                        <?php if ($log->error_occurred): ?>
                                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Error</span>
                                        <?php elseif ($log->fallback_used): ?>
                                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">Fallback</span>
                                        <?php else: ?>
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">OK</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-3 py-2 text-xs text-gray-600 max-w-md truncate">
                                        <?= htmlspecialchars($log->user_message) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-500">Tidak ada log</p>
            <?php endif; ?>
        </div>
    </main>

    <?php include __DIR__ . '/includes/dark-mode-script.php'; ?>
</body>

</html>