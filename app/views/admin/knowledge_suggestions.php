<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Auto-Learning Dashboard' ?> - <?= SITE_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-50">
    
    <?php include __DIR__ . '/../admin/includes/sidebar.php'; ?>
    
    <div class="ml-64 p-8">
        <h1 class="text-3xl font-bold mb-6">Auto-Learning Dashboard</h1>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-600 text-sm">Total Conversations Today</h3>
                <p class="text-3xl font-bold text-indigo-600"><?= $stats->total_conversations ?? 0 ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-600 text-sm">No Knowledge Match</h3>
                <p class="text-3xl font-bold text-red-600"><?= $stats->no_match_count ?? 0 ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-600 text-sm">Avg Matches</h3>
                <p class="text-3xl font-bold text-green-600"><?= number_format($stats->avg_matches ?? 0, 1) ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-600 text-sm">Avg Response Time</h3>
                <p class="text-3xl font-bold text-blue-600"><?= round($stats->avg_response_time ?? 0) ?>ms</p>
            </div>
        </div>

        <!-- Knowledge Suggestions -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold">Knowledge Gap Suggestions</h2>
                <p class="text-sm text-gray-600">Questions that need knowledge base entries (sorted by frequency)</p>
            </div>

            <div class="p-6">
                <?php if (empty($suggestions)): ?>
                    <p class="text-gray-500 text-center py-8">No suggestions yet! All questions are being answered well. 🎉
                    </p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($suggestions as $suggestion): ?>
                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-lg mb-2"><?= htmlspecialchars($suggestion->question) ?>
                                        </h3>
                                        <div class="flex gap-4 text-sm text-gray-600">
                                            <span class="flex items-center">
                                                <i class="fas fa-eye mr-1"></i>
                                                Asked <?= $suggestion->frequency ?>x
                                            </span>
                                            <span>Keywords: <?= htmlspecialchars($suggestion->keywords) ?></span>
                                            <span><?= date('d M Y', strtotime($suggestion->created_at)) ?></span>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <button onclick="approveSuggestion(<?= $suggestion->id ?>)"
                                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                            ✓ Add to KB
                                        </button>
                                        <button onclick="rejectSuggestion(<?= $suggestion->id ?>)"
                                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                                            ✗ Reject
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Instructions -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="font-semibold text-blue-900 mb-2"><i class="fas fa-lightbulb"></i> How Auto-Learning Works</h3>
            <ul class="text-sm text-blue-800 space-y-1">
                <li>• Every chat conversation is logged automatically</li>
                <li>• When a question has 0 knowledge matches, it's added as a suggestion</li>
                <li>• Frequently asked unanswered questions appear here</li>
                <li>• Review suggestions and add them to knowledge base</li>
                <li>• System continuously improves based on real user questions</li>
            </ul>
        </div>
    </div>

    <script>
        function approveSuggestion(id) {
            if (!confirm('Add this suggestion to knowledge base?')) return;

            fetch('<?= base_url('admin/approveSuggestion') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            })
                .then(r => r.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) location.reload();
                });
        }

        function rejectSuggestion(id) {
            if (!confirm('Reject this suggestion?')) return;

            fetch('<?= base_url('admin/rejectSuggestion') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            })
                .then(r => r.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) location.reload();
                });
        }
    </script>
</body>

</html>