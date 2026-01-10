<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= e($title) ?> |
        <?= SITE_NAME ?> Admin
    </title>

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
                <h1 class="text-2xl font-bold text-gray-800">
                    <?= e($title) ?>
                </h1>
                <p class="text-gray-500 text-sm">Perbarui nama kategori galeri</p>
            </div>
            <a href="<?= base_url('admin/galleryCategories') ?>"
                class="inline-flex items-center text-gray-600 hover:text-indigo-600 font-medium transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>

        <div class="max-w-xl mx-auto">
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="font-semibold text-gray-800">Edit Kategori</h3>
                </div>

                <!-- Flash Message -->
                <?php if ($flash = (isset($flash) ? $flash : null)): ?>
                    <div
                        class="p-4 <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                        <?= $flash['message'] ?>
                    </div>
                <?php endif; ?>

                <div class="p-6">
                    <form action="<?= base_url('admin/updateGalleryCategory/' . $category->id) ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Kategori</label>
                            <input type="text" name="name" value="<?= e($category->name) ?>" required
                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 transition-shadow">
                        </div>
                        <div class="flex items-center space-x-3">
                            <button type="submit"
                                class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-4 rounded-lg shadow-sm hover:shadow transition-all transform hover:-translate-y-0.5">
                                <i class="fas fa-save mr-2"></i>Simpan Perubahan
                            </button>
                            <a href="<?= base_url('admin/galleryCategories') ?>"
                                class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2.5 px-4 rounded-lg text-center transition-colors">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php include dirname(__DIR__) . '/includes/dark-mode-script.php'; ?>
</body>

</html>