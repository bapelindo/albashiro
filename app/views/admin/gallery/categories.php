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
    <?php include dirname(__DIR__) . '/includes/dark-mode-styles.php'; ?>
</head>

<body class="bg-gray-100 min-h-screen">
    <?php
    $activeMenu = 'gallery';
    include dirname(__DIR__) . '/includes/sidebar.php';
    ?>

    <!-- Main Content -->
    <main class="ml-64 p-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-800"><?= e($title) ?></h1>
                <p class="text-gray-500 text-sm">Kelola kategori untuk pengelompokan foto</p>
            </div>
            <a href="<?= base_url('admin/gallery') ?>" class="inline-flex items-center text-gray-600 hover:text-indigo-600 font-medium transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Galeri
            </a>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- List Categories (Table) -->
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-800">Daftar Kategori</h3>
                    <span class="bg-indigo-100 text-indigo-700 text-xs px-2 py-1 rounded-full font-medium"><?= count($categories) ?> Kategori</span>
                </div>
                
                <!-- Flash Message -->
                <?php if ($flash = (isset($flash) ? $flash : null)): ?>
                    <div class="p-4 <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                        <?= $flash['message'] ?>
                    </div>
                <?php endif; ?>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider border-b border-gray-100">
                                <th class="p-4 font-semibold">Nama Kategori</th>
                                <th class="p-4 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($categories as $cat): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="p-4 text-gray-700 font-medium whitespace-nowrap">
                                        <?= e($cat->name) ?>
                                    </td>
                                    <td class="p-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="<?= base_url('admin/editGalleryCategory/' . $cat->id) ?>" 
                                               class="w-8 h-8 flex items-center justify-center bg-yellow-100 text-yellow-600 rounded-lg hover:bg-yellow-200 transition-colors"
                                               title="Edit">
                                                <i class="fas fa-edit text-sm"></i>
                                            </a>
                                            <form action="<?= base_url('admin/deleteGalleryCategory/' . $cat->id) ?>" method="POST"
                                                class="inline-block"
                                                onsubmit="return confirm('Hapus kategori ini? Semua gambar di dalamnya juga akan terhapus!');">
                                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                                <button type="submit" 
                                                        class="w-8 h-8 flex items-center justify-center bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-colors"
                                                        title="Hapus">
                                                    <i class="fas fa-trash-alt text-sm"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($categories)): ?>
                                <tr>
                                    <td colspan="2" class="p-8 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fas fa-tags text-4xl mb-3 text-gray-300"></i>
                                            <p>Belum ada kategori.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Add Category -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden h-fit sticky top-8">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                        <h3 class="font-semibold text-gray-800">Tambah Kategori Baru</h3>
                    </div>
                    <div class="p-6">
                        <form action="<?= base_url('admin/storeGalleryCategory') ?>" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Kategori</label>
                                <input type="text" name="name" required placeholder="Contoh: Kegiatan, Fasilitas"
                                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 transition-shadow">
                            </div>
                            <button type="submit"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-4 rounded-lg shadow-sm hover:shadow transition-all transform hover:-translate-y-0.5">
                                <i class="fas fa-plus mr-2"></i>Tambah Kategori
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include dirname(__DIR__) . '/includes/dark-mode-script.php'; ?>
</body>
</html>