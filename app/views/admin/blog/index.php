<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> | <?= SITE_NAME ?> Admin</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
    <?php include __DIR__ . '/../includes/dark-mode-styles.php'; ?>
</head>

<body class="bg-gray-100 min-h-screen">
    <?php
    $activeMenu = 'blog';
    include __DIR__ . '/../includes/sidebar.php';
    ?>

    <!-- Main Content -->
    <main class="ml-64 p-8">
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-800"><?= e($title) ?></h1>
                <p class="text-gray-500 text-sm">Kelola artikel blog Anda</p>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="toggleTagManager()"
                    class="inline-flex items-center px-4 py-2 bg-purple-600 text-white font-medium rounded-xl hover:bg-purple-700 transition-colors">
                    <i class="fas fa-tags mr-2"></i>
                    Kelola Tags
                </button>
                <a href="<?= base_url('admin/create') ?>"
                    class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Tulis Artikel Baru
                </a>
            </div>
        </div>

        <!-- Tag Manager Modal -->
        <div id="tag-manager" class="hidden mb-6">
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-tags text-purple-600 mr-2"></i>
                        Kelola Tags
                    </h3>
                    <button onclick="toggleTagManager()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <!-- All Tags List -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Semua Tags yang Digunakan</h4>
                        <div class="flex flex-wrap gap-2 max-h-64 overflow-y-auto p-3 bg-gray-50 rounded-lg">
                            <?php
                            $allTags = [];
                            foreach ($posts as $post) {
                                if (!empty($post->tags)) {
                                    $postTags = array_map('trim', explode(',', $post->tags));
                                    foreach ($postTags as $tag) {
                                        if (!isset($allTags[$tag])) {
                                            $allTags[$tag] = 0;
                                        }
                                        $allTags[$tag]++;
                                    }
                                }
                            }
                            arsort($allTags);

                            foreach ($allTags as $tag => $count):
                                ?>
                                <span
                                    class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-200 text-gray-700 text-sm rounded-full hover:border-purple-300 transition-colors group">
                                    <i class="fas fa-tag text-xs mr-1.5 text-purple-600"></i>
                                    <?= e($tag) ?>
                                    <span
                                        class="ml-2 px-1.5 py-0.5 bg-purple-100 text-purple-700 text-xs rounded-full"><?= $count ?></span>
                                    <button onclick="deleteTag('<?= e($tag) ?>', <?= $count ?>)"
                                        class="ml-2 text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition-opacity"
                                        title="Hapus tag dari semua artikel">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Tag Statistics -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Statistik Tags</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm text-gray-600">Total Tags Unik:</span>
                                <span class="text-lg font-bold text-purple-600"><?= count($allTags) ?></span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm text-gray-600">Total Artikel dengan Tags:</span>
                                <span class="text-lg font-bold text-purple-600">
                                    <?= count(array_filter($posts, fn($p) => !empty($p->tags))) ?>
                                </span>
                            </div>

                            <div class="mt-4">
                                <h5 class="text-xs font-medium text-gray-600 mb-2">Top 5 Tags Terpopuler:</h5>
                                <div class="space-y-2">
                                    <?php
                                    $topTags = array_slice($allTags, 0, 5, true);
                                    foreach ($topTags as $tag => $count):
                                        ?>
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-700">#<?= e($tag) ?></span>
                                            <div class="flex items-center">
                                                <div class="w-24 h-2 bg-gray-200 rounded-full mr-2">
                                                    <div class="h-2 bg-purple-600 rounded-full"
                                                        style="width: <?= ($count / max($allTags)) * 100 ?>%"></div>
                                                </div>
                                                <span class="text-gray-500 text-xs"><?= $count ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (isset($flash) && $flash): ?>
            <div
                class="mb-6 p-4 <?= $flash['type'] === 'error' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-green-50 border-green-200 text-green-700' ?> rounded-xl border">
                <p class="text-sm"><?= $flash['message'] ?></p>
            </div>
        <?php endif; ?>

        <!-- Posts Table -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <?php if (empty($posts)): ?>
                <div class="text-center py-16">
                    <i class="fas fa-newspaper text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">Belum ada artikel</p>
                    <a href="<?= base_url('admin/create') ?>"
                        class="inline-block mt-4 text-indigo-600 hover:text-indigo-700 font-medium">
                        <i class="fas fa-plus mr-1"></i>Tulis artikel pertama
                    </a>
                </div>
            <?php else: ?>
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-3 px-4 text-gray-600 font-medium">Judul</th>
                            <th class="text-left py-3 px-4 text-gray-600 font-medium">Kategori</th>
                            <th class="text-left py-3 px-4 text-gray-600 font-medium">Tags</th>
                            <th class="text-left py-3 px-4 text-gray-600 font-medium">Status</th>
                            <th class="text-left py-3 px-4 text-gray-600 font-medium">Views</th>
                            <th class="text-left py-3 px-4 text-gray-600 font-medium">Tanggal</th>
                            <th class="text-left py-3 px-4 text-gray-600 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-3 px-4">
                                    <a href="<?= base_url('admin/edit/' . $post->id) ?>"
                                        class="text-gray-800 hover:text-indigo-600 font-medium">
                                        <?= e($post->title) ?>
                                    </a>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded-full">
                                        <?= e($post->category ?? 'Artikel') ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <?php if (!empty($post->tags)): ?>
                                        <div class="flex flex-wrap gap-1">
                                            <?php foreach (array_slice(array_map('trim', explode(',', $post->tags)), 0, 2) as $tag): ?>
                                                <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">
                                                    #<?= e($tag) ?>
                                                </span>
                                            <?php endforeach; ?>
                                            <?php if (count(explode(',', $post->tags)) > 2): ?>
                                                <span class="text-xs text-gray-400">+<?= count(explode(',', $post->tags)) - 2 ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4">
                                    <span
                                        class="px-3 py-1 text-xs font-medium rounded-full <?= $post->status === 'published' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
                                        <?= $post->status === 'published' ? 'Published' : 'Draft' ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-gray-600"><?= number_format($post->views) ?></td>
                                <td class="py-3 px-4 text-gray-600"><?= date('d M Y', strtotime($post->created_at)) ?></td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <?php if ($post->status === 'published'): ?>
                                            <a href="<?= base_url('blog/' . $post->slug) ?>" target="_blank"
                                                class="p-2 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                                title="Lihat">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?= base_url('admin/edit/' . $post->id) ?>"
                                            class="p-2 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="<?= base_url('admin/delete/' . $post->id) ?>" method="POST" class="inline"
                                            onsubmit="return confirm('Yakin ingin menghapus artikel ini?')">
                                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                            <button type="submit"
                                                class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function toggleTagManager() {
            const tagManager = document.getElementById('tag-manager');
            tagManager.classList.toggle('hidden');
        }

        function deleteTag(tagName, count) {
            if (!confirm(`Hapus tag "${tagName}" dari ${count} artikel?\n\nTag ini akan dihapus dari semua artikel yang menggunakannya.`)) {
                return;
            }

            // Show loading
            const btn = event.target.closest('button');
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i>';
            btn.disabled = true;

            // Send AJAX request
            fetch('<?= base_url('admin/deleteTag') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'tag=' + encodeURIComponent(tagName) + '&csrf_token=<?= $_SESSION['csrf_token'] ?? '' ?>'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        if (typeof showToast === 'function') {
                            showToast(`Tag "${tagName}" berhasil dihapus dari ${data.affected} artikel`, 'success');
                        } else {
                            alert(`Tag "${tagName}" berhasil dihapus dari ${data.affected} artikel`);
                        }
                        // Reload page after 1 second
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        alert('Error: ' + (data.message || 'Gagal menghapus tag'));
                        btn.innerHTML = originalHTML;
                        btn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menghapus tag');
                    btn.innerHTML = originalHTML;
                    btn.disabled = false;
                });
        }
    </script>
    <?php include __DIR__ . '/../includes/dark-mode-script.php'; ?>
</body>

</html>