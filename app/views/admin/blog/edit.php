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
    <!-- Sidebar -->
    <aside class="fixed left-0 top-0 w-64 h-full bg-white shadow-lg z-50">
        <div class="p-6 border-b">
            <div class="flex items-center space-x-3">
                <div
                    class="w-10 h-10 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-spa text-white"></i>
                </div>
                <div>
                    <span class="font-bold text-gray-800"><?= SITE_NAME ?></span>
                    <span class="block text-xs text-gray-500">Admin Panel</span>
                </div>
            </div>
        </div>

        <nav class="p-4">
            <ul class="space-y-2">
                <li>
                    <a href="<?= base_url('admin') ?>"
                        class="flex items-center px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 rounded-xl transition-colors">
                        <i class="fas fa-home w-5 mr-3"></i>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="<?= base_url('admin/calendar') ?>"
                        class="flex items-center px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 rounded-xl transition-colors">
                        <i class="fas fa-calendar-alt w-5 mr-3"></i>
                        Calendar
                    </a>
                </li>
                <li>
                    <a href="<?= base_url('admin/bookings') ?>"
                        class="flex items-center px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 rounded-xl transition-colors">
                        <i class="fas fa-calendar-check w-5 mr-3"></i>
                        Reservasi
                    </a>
                </li>
                <li><a href="<?= base_url('admin/blog') ?>"
                        class="flex items-center px-4 py-3 bg-indigo-50 text-indigo-700 rounded-xl"><i
                            class="fas fa-newspaper w-5 mr-3"></i>Blog</a></li>
                <li class="pt-4 border-t mt-4">
                    <a href="<?= base_url() ?>" target="_blank"
                        class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-xl"><i
                            class="fas fa-external-link-alt w-5 mr-3"></i>Lihat Website</a>
                </li>
                <li><a href="<?= base_url('auth/logout') ?>"
                        class="flex items-center px-4 py-3 text-red-600 hover:bg-red-50 rounded-xl"><i
                            class="fas fa-sign-out-alt w-5 mr-3"></i>Logout</a></li>
            </ul>
            <?php include __DIR__ . '/../includes/dark-mode-toggle.php'; ?>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="ml-64 p-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <a href="<?= base_url('admin/blog') ?>"
                    class="text-gray-500 hover:text-indigo-600 text-sm mb-2 inline-block">
                    <i class="fas fa-arrow-left mr-1"></i>Kembali
                </a>
                <h1 class="text-2xl font-bold text-gray-800"><?= e($title) ?></h1>
            </div>
        </div>

        <!-- Form -->
        <form action="<?= base_url('admin/update/' . $post->id) ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Main Content Column -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Title -->
                    <div class="bg-white rounded-2xl shadow-sm p-8">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Judul Artikel <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="title" id="title" required value="<?= e($post->title) ?>"
                            placeholder="Masukkan judul artikel yang menarik"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-lg font-medium"
                            onkeyup="updateSlugPreview()">
                        <p class="text-xs text-gray-500 mt-2">Slug: <span id="slug-preview"
                                class="text-indigo-600 font-mono"><?= e($post->slug) ?></span></p>
                    </div>

                    <!-- Content Editor -->
                    <div class="bg-white rounded-2xl shadow-sm p-8">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Konten Artikel <span
                                class="text-red-500">*</span></label>
                        <!-- Quill Editor Container -->
                        <div id="quill-editor" style="height: 400px;"></div>
                        <!-- Hidden textarea for form submission -->
                        <textarea name="content" id="content"
                            style="display:none;"><?= htmlspecialchars($post->content) ?></textarea>
                        <p class="text-xs text-gray-500 mt-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Gunakan editor untuk format teks, tambah gambar, dan styling
                        </p>
                    </div>

                    <!-- Excerpt -->
                    <div class="bg-white rounded-2xl shadow-sm p-8">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ringkasan / Excerpt</label>
                        <textarea name="excerpt" id="excerpt" rows="3"
                            placeholder="Ringkasan singkat untuk preview artikel (opsional, max 200 karakter)"
                            maxlength="200"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 resize-none"
                            onkeyup="updateCharCount()"><?= e($post->excerpt) ?></textarea>
                        <p class="text-xs text-gray-500 mt-1"><span
                                id="char-count"><?= strlen($post->excerpt ?? '') ?></span>/200 karakter</p>
                    </div>

                    <!-- SEO Settings -->
                    <div class="bg-white rounded-2xl shadow-sm p-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-search text-indigo-600 mr-2"></i>
                            SEO & Meta Tags
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Meta Description</label>
                                <textarea name="meta_description" rows="2"
                                    placeholder="Deskripsi untuk search engine (max 160 karakter)" maxlength="160"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 resize-none"><?= e($post->meta_description ?? '') ?></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Focus Keyword</label>
                                <input type="text" name="focus_keyword" value="<?= e($post->focus_keyword ?? '') ?>"
                                    placeholder="Kata kunci utama artikel"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Column -->
                <div class="space-y-6">
                    <!-- Publish Settings -->
                    <div class="bg-white rounded-2xl shadow-sm p-6">
                        <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-cog text-indigo-600 mr-2"></i>
                            Pengaturan Publish
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select name="status" id="status"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                                    <option value="draft" <?= $post->status === 'draft' ? 'selected' : '' ?>>Draft</option>
                                    <option value="published" <?= $post->status === 'published' ? 'selected' : '' ?>>
                                        Published</option>
                                    <option value="scheduled" <?= $post->status === 'scheduled' ? 'selected' : '' ?>>
                                        Scheduled</option>
                                </select>
                            </div>
                            <div id="schedule-date"
                                style="display:<?= $post->status === 'scheduled' ? 'block' : 'none' ?>;">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Publish</label>
                                <input type="datetime-local" name="scheduled_at"
                                    value="<?= e($post->scheduled_at ?? '') ?>"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Author</label>
                                <input type="text" name="author" value="<?= e($post->author ?? 'Admin') ?>"
                                    placeholder="Nama penulis"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                            </div>
                        </div>
                    </div>

                    <!-- Category & Tags -->
                    <div class="bg-white rounded-2xl shadow-sm p-6">
                        <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-tags text-indigo-600 mr-2"></i>
                            Kategori & Tags
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                                <select name="category"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                                    <option value="Artikel" <?= $post->category === 'Artikel' ? 'selected' : '' ?>>Artikel
                                    </option>
                                    <option value="Edukasi" <?= $post->category === 'Edukasi' ? 'selected' : '' ?>>Edukasi
                                    </option>
                                    <option value="Tips Kesehatan" <?= $post->category === 'Tips Kesehatan' ? 'selected' : '' ?>>Tips Kesehatan</option>
                                    <option value="Hipnoterapi" <?= $post->category === 'Hipnoterapi' ? 'selected' : '' ?>>
                                        Hipnoterapi</option>
                                    <option value="Kesehatan Mental" <?= $post->category === 'Kesehatan Mental' ? 'selected' : '' ?>>Kesehatan Mental</option>
                                    <option value="Spiritual" <?= $post->category === 'Spiritual' ? 'selected' : '' ?>>
                                        Spiritual</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tags</label>
                                <input type="text" name="tags" value="<?= e($post->tags) ?>"
                                    placeholder="hipnoterapi, kesehatan, mental"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                                <p class="text-xs text-gray-500 mt-1">Pisahkan dengan koma</p>
                            </div>
                        </div>
                    </div>

                    <!-- Featured Image -->
                    <div class="bg-white rounded-2xl shadow-sm p-6">
                        <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-image text-indigo-600 mr-2"></i>
                            Featured Image
                        </h3>
                        <div class="space-y-4">
                            <?php if (!empty($post->featured_image)): ?>
                                <div id="image-preview" class="mb-4">
                                    <img id="preview-img" src="<?= base_url('public/images/' . $post->featured_image) ?>"
                                        alt="Preview" class="w-full h-48 object-cover rounded-xl">
                                    <button type="button" onclick="removeImage()"
                                        class="mt-2 text-sm text-red-600 hover:text-red-700">
                                        <i class="fas fa-times mr-1"></i>Hapus gambar
                                    </button>
                                </div>
                            <?php else: ?>
                                <div id="image-preview" class="hidden mb-4">
                                    <img id="preview-img" src="" alt="Preview" class="w-full h-48 object-cover rounded-xl">
                                    <button type="button" onclick="removeImage()"
                                        class="mt-2 text-sm text-red-600 hover:text-red-700">
                                        <i class="fas fa-times mr-1"></i>Hapus gambar
                                    </button>
                                </div>
                            <?php endif; ?>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Upload Gambar</label>
                                <input type="file" name="featured_image_upload" id="image-upload" accept="image/*"
                                    onchange="previewImage(event)"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                                <p class="text-xs text-gray-500 mt-1">Atau masukkan URL gambar di bawah</p>
                            </div>
                            <div>
                                <input type="text" name="featured_image" value="<?= e($post->featured_image) ?>"
                                    placeholder="https://example.com/image.jpg"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                            </div>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="bg-white rounded-2xl shadow-sm p-6">
                        <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-chart-line text-indigo-600 mr-2"></i>
                            Statistik
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Estimasi Waktu Baca:</span>
                                <span id="reading-time" class="text-sm font-medium text-gray-800">0 menit</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Jumlah Kata:</span>
                                <span id="word-count" class="text-sm font-medium text-gray-800">0</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Total Views:</span>
                                <span
                                    class="text-sm font-medium text-gray-800"><?= number_format($post->views) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="mt-8 flex items-center justify-between bg-white rounded-2xl shadow-sm p-6">
                <a href="<?= base_url('admin/blog') ?>" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times mr-2"></i>Batal
                </a>
                <div class="flex items-center space-x-4">
                    <button type="submit" name="status" value="draft"
                        class="px-8 py-3 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition-colors">
                        <i class="fas fa-save mr-2"></i>Simpan Draft
                    </button>
                    <button type="submit" name="status" value="published"
                        class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg hover:shadow-xl">
                        <i class="fas fa-check mr-2"></i>Update & Publish
                    </button>
                </div>
            </div>
        </form>
    </main>


    <!-- Quill Editor (Free, No API Key Required) -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

    <script>
        // Initialize Quill Editor
        var quill = new Quill('#quill-editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'align': [] }],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    ['blockquote', 'code-block'],
                    ['link', 'image'],
                    ['clean']
                ]
            },
            placeholder: 'Tulis konten artikel di sini...'
        });

        // Load existing content from hidden textarea
        var existingContent = document.getElementById('content').value;
        if (existingContent && existingContent.trim() !== '') {
            quill.root.innerHTML = existingContent;
            setTimeout(function() {
                updateReadingStats(); // Update stats after content loads
            }, 100);
        }

        // Sync Quill content to textarea for form submission
        quill.on('text-change', function () {
            document.getElementById('content').value = quill.root.innerHTML;
            updateReadingStats();
        });

        // Slug preview
        function updateSlugPreview() {
            const title = document.getElementById('title').value;
            const slug = title.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
            document.getElementById('slug-preview').textContent = slug || '<?= e($post->slug) ?>';
        }

        // Character count for excerpt
        function updateCharCount() {
            const excerpt = document.getElementById('excerpt').value;
            document.getElementById('char-count').textContent = excerpt.length;
        }

        // Reading time and word count
        function updateReadingStats() {
            const content = quill.getText();
            const words = content.trim().split(/\s+/).filter(w => w.length > 0).length;
            const readingTime = Math.ceil(words / 200); // Average reading speed: 200 words/min

            document.getElementById('word-count').textContent = words.toLocaleString();
            document.getElementById('reading-time').textContent = readingTime + ' menit';
        }

        // Image preview
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('preview-img').src = e.target.result;
                    document.getElementById('image-preview').classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        }

        function removeImage() {
            document.getElementById('image-upload').value = '';
            document.getElementById('image-preview').classList.add('hidden');
        }

        // Show/hide schedule date
        document.getElementById('status').addEventListener('change', function () {
            const scheduleDiv = document.getElementById('schedule-date');
            if (this.value === 'scheduled') {
                scheduleDiv.style.display = 'block';
            } else {
                scheduleDiv.style.display = 'none';
            }
        });

        // Handle form submission
        document.querySelector('form').addEventListener('submit', function (e) {
            // Sync TinyMCE content to textarea
            if (typeof tinymce !== 'undefined' && tinymce.get('content')) {
                tinymce.triggerSave();
            }

            // Validate required fields
            const title = document.getElementById('title').value.trim();
            const content = document.getElementById('content').value.trim();

            if (!title || !content) {
                e.preventDefault();
                alert('Judul dan konten harus diisi!');
                return false;
            }
        });

        // Initialize on load
        document.addEventListener('DOMContentLoaded', function () {
            updateCharCount();
            // Update stats after TinyMCE loads
            setTimeout(updateReadingStats, 1000);
        });
    </script>
    <?php include __DIR__ . '/../includes/dark-mode-script.php'; ?>
</body>

</html>