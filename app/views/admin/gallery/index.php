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

        .animate-fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            bg-transparent;
        }

        ::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background-color: #94a3b8;
        }
    </style>
    <?php include dirname(__DIR__) . '/includes/dark-mode-styles.php'; ?>
</head>

<body class="bg-gray-100 min-h-screen text-gray-800">
    <?php
    $activeMenu = 'gallery';
    include dirname(__DIR__) . '/includes/sidebar.php';
    ?>

    <!-- Main Content -->
    <main class="ml-64 p-8">
        <!-- Header -->
        <header class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900"><?= e($title) ?></h1>
                <p class="text-gray-500 mt-1">Kelola galeri foto, kategori, dan upload batch.</p>
            </div>
            <div class="flex space-x-3">
                <a href="<?= base_url('admin/galleryCategories') ?>"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors shadow-sm font-medium">
                    <i class="fas fa-tags mr-2 text-gray-400"></i>Kategori
                </a>
                <button id="btnOpenUpload"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors shadow-md font-medium">
                    <i class="fas fa-cloud-upload-alt mr-2"></i>Upload Batch
                </button>
            </div>
        </header>

        <!-- Controls & Filters -->
        <div class="bg-white rounded-xl p-6 shadow-sm mb-8 border border-gray-100">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <!-- Filter -->
                <form action="<?= base_url('admin/gallery') ?>" method="GET"
                    class="w-full md:w-auto flex items-center bg-gray-50 p-2 rounded-lg border border-gray-200">
                    <label for="category" class="font-medium text-gray-600 mx-2"><i
                            class="fas fa-filter mr-1"></i>Filter:</label>
                    <select name="category" id="category" onchange="this.form.submit()"
                        class="bg-transparent border-none focus:ring-0 text-gray-700 font-medium cursor-pointer py-1 pr-8">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat->id ?>" <?= $currentCategory == $cat->id ? 'selected' : '' ?>>
                                <?= e($cat->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <!-- Bulk Actions Toolbar -->
                <div class="w-full md:w-auto flex items-center justify-end space-x-2">
                    <div class="flex items-center bg-indigo-50 px-4 py-2 rounded-lg border border-indigo-100">
                        <input type="checkbox" id="selectAll"
                            class="form-checkbox h-5 w-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500 cursor-pointer">
                        <label for="selectAll"
                            class="ml-2 text-sm font-semibold text-indigo-700 cursor-pointer select-none">Pilih
                            Semua</label>
                    </div>

                    <form method="POST" id="bulkActionForm"
                        class="hidden flex items-center space-x-2 transition-all duration-300">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <!-- IDs injected via JS -->

                        <!-- Move Action -->
                        <div class="flex rounded-md shadow-sm">
                            <select name="target_category"
                                class="rounded-l-lg border-gray-300 border-r-0 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                <option value="">Pindah ke...</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat->id ?>"><?= e($cat->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" formaction="<?= base_url('admin/moveGalleryBulk') ?>"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 rounded-r-lg font-medium text-sm transition-colors">
                                <i class="fas fa-exchange-alt mr-1"></i> Pindah
                            </button>
                        </div>

                        <!-- Delete Action -->
                        <button type="submit" formaction="<?= base_url('admin/deleteGalleryBulk') ?>"
                            onclick="return confirm('Hapus item terpilih secara permanen?')"
                            class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 shadow-md font-medium text-sm transition-colors">
                            <i class="fas fa-trash-alt mr-1"></i>Hapus (<span id="selectedCountDisplay">0</span>)
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Gallery Grid -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
            <?php if (!empty($galleries)): ?>
                <?php foreach ($galleries as $image): ?>
                    <div
                        class="group relative bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 hover:border-indigo-200">
                        <!-- Selection Checkbox Overlay -->
                        <div class="absolute top-3 left-3 z-20">
                            <input type="checkbox" data-id="<?= $image->id ?>"
                                class="item-checkbox form-checkbox h-6 w-6 text-indigo-600 rounded-md border-gray-300 focus:ring-indigo-500 shadow-sm cursor-pointer transition-transform transform hover:scale-110">
                        </div>

                        <!-- Image Aspect Ratio (Padding Hack for robustness) -->
                        <div class="relative w-full pt-[75%] bg-gray-200 overflow-hidden">
                            <img src="<?= base_url('public/images/' . $image->image_url) ?>" alt="Gallery Image" loading="lazy"
                                class="absolute inset-0 w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700 ease-out"
                                onerror="this.src='https://placehold.co/600x400?text=No+Image';">

                            <!-- Hover Overlay -->
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
                            </div>
                        </div>

                        <!-- Meta Info -->
                        <div class="p-4 relative">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                <?= e($image->category_name ?? 'Umum') ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div
                    class="col-span-full flex flex-col items-center justify-center p-12 bg-white rounded-3xl border-2 border-dashed border-gray-200 text-center">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-image text-3xl text-gray-300"></i>
                    </div>
                    <h3 class="text-xl font-medium text-gray-900">Belum ada gambar</h3>
                    <p class="text-gray-500 mt-2">Upload gambar pertama Anda untuk memulai.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="mt-10 flex justify-center">
                <nav class="inline-flex rounded-md shadow-sm isolate" aria-label="Pagination">
                    <!-- Prev -->
                    <a href="<?= $page > 1 ? base_url("admin/gallery?page=" . ($page - 1) . ($currentCategory ? "&category=$currentCategory" : "")) : '#' ?>"
                        class="relative inline-flex items-center rounded-l-md px-3 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 <?= $page <= 1 ? 'pointer-events-none opacity-50' : '' ?>">
                        <span class="sr-only">Previous</span>
                        <i class="fas fa-chevron-left h-5 w-5"></i>
                    </a>

                    <!-- Page Info -->
                    <span
                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 focus:outline-offset-0">
                        Halaman <?= $page ?> dari <?= $totalPages ?>
                    </span>

                    <!-- Next -->
                    <a href="<?= $page < $totalPages ? base_url("admin/gallery?page=" . ($page + 1) . ($currentCategory ? "&category=$currentCategory" : "")) : '#' ?>"
                        class="relative inline-flex items-center rounded-r-md px-3 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 <?= $page >= $totalPages ? 'pointer-events-none opacity-50' : '' ?>">
                        <span class="sr-only">Next</span>
                        <i class="fas fa-chevron-right h-5 w-5"></i>
                    </a>
                </nav>
            </div>
        <?php endif; ?>
    </main>

    <!-- ENTERPRISE UPLOAD MODAL -->
    <div id="uploadModal" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity" id="modalBackdrop"></div>

        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl border border-gray-100">

                <!-- Modal Header -->
                <div class="bg-indigo-600 px-4 py-3 sm:px-6 flex justify-between items-center">
                    <h3 class="text-base font-semibold leading-6 text-white" id="modal-title">
                        <i class="fas fa-cloud-upload-alt mr-2"></i>Upload Batch Gallery
                    </h3>
                    <button type="button" onclick="GalleryApp.closeModal()"
                        class="text-indigo-200 hover:text-white transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="px-4 py-5 sm:p-6 bg-white min-h-[400px] flex flex-col">

                    <!-- Config Section -->
                    <div class="mb-6 grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm font-medium leading-6 text-gray-900">Kategori Upload</label>
                            <select id="uploadCategory"
                                class="mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat->id ?>"><?= e($cat->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- DROPZONE AREA -->
                    <div id="dropzone"
                        class="flex-1 flex flex-col justify-center items-center rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 hover:bg-gray-100 hover:border-indigo-500 transition-all cursor-pointer p-8 group">
                        <input type="file" id="fileInput" multiple accept="image/*" class="hidden">

                        <!-- Initial State -->
                        <div id="dropzoneInitial" class="text-center">
                            <div
                                class="bg-white p-4 rounded-full shadow-sm inline-block mb-4 group-hover:scale-110 transition-transform">
                                <i class="fas fa-images text-4xl text-indigo-500"></i>
                            </div>
                            <p class="text-lg font-semibold text-gray-700">Drag & Drop foto di sini</p>
                            <p class="text-sm text-gray-500 mt-1">atau klik untuk memilih file</p>
                            <p class="text-xs text-indigo-400 mt-4 font-mono">Support: JPG, PNG, WEBP (Max 5MB)</p>
                        </div>

                        <!-- Preview State (Hidden by default) -->
                        <div id="dropzonePreview" class="hidden w-full h-full">
                            <div class="flex justify-between items-center mb-4 border-b pb-2">
                                <h4 class="font-bold text-gray-800"><span id="fileCountDisplay">0</span> File Siap
                                    Upload</h4>
                                <button type="button" onclick="GalleryApp.resetUpload()"
                                    class="text-xs text-red-500 font-medium hover:text-red-700 hover:underline">
                                    <i class="fas fa-trash mr-1"></i>Reset & Hapus Semua
                                </button>
                            </div>

                            <!-- Scrollable Grid -->
                            <div id="previewContainer"
                                class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-4 max-h-[300px] overflow-y-auto p-2">
                                <!-- Thumbnails injected here -->
                            </div>

                            <!-- Manifest List (Client request) -->
                            <div
                                class="mt-4 text-left bg-slate-50 p-3 rounded-lg border border-slate-200 max-h-[150px] overflow-y-auto">
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Manifest File:
                                </p>
                                <ul id="filesManifest"
                                    class="text-xs font-mono text-gray-600 space-y-1 list-decimal pl-5">
                                    <!-- List items injected here -->
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Overlay (Hidden by default) -->
                    <div id="uploadProgress" class="hidden mt-6 text-center">
                        <div class="relative pt-1">
                            <div class="flex mb-2 items-center justify-between">
                                <div>
                                    <span
                                        class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-indigo-600 bg-indigo-200">
                                        Proses Upload
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span id="progressPercent"
                                        class="text-xs font-semibold inline-block text-indigo-600">0%</span>
                                </div>
                            </div>
                            <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-indigo-200">
                                <div id="progressBar" style="width:0%"
                                    class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500 transition-all duration-300">
                                </div>
                            </div>
                            <p id="progressText" class="text-sm text-gray-600 font-medium">Menyiapkan...</p>
                        </div>
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="button" id="btnStartUpload" disabled
                        class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                        <i class="fas fa-cloud-upload-alt mr-2"></i>Mulai Upload
                    </button>
                    <button type="button" onclick="GalleryApp.closeModal()"
                        class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Form for CSRF usage in JS -->
    <form id="csrfForm" class="hidden">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    </form>

    <!-- JAVASCRIPT LOGIC (Enterprise Grade) -->
    <script>
        /**
         * BulkActionManager
         * Handles selection logic, "Select All", and visibility of bulk actions.
         */
        const BulkActionManager = {
            init() {
                this.checkboxes = document.querySelectorAll('.item-checkbox');
                this.masterCheckbox = document.getElementById('selectAll');
                this.bulkForm = document.getElementById('bulkActionForm');
                this.countDisplay = document.getElementById('selectedCountDisplay');

                if (!this.masterCheckbox) return;

                // Bind Master Checkbox
                this.masterCheckbox.addEventListener('change', (e) => {
                    this.toggleAll(e.target.checked);
                });

                // Bind Individual Checkboxes
                this.checkboxes.forEach(cb => {
                    cb.addEventListener('change', () => this.updateState());
                });
            },

            toggleAll(state) {
                this.checkboxes.forEach(cb => cb.checked = state);
                this.updateState();
            },

            updateState() {
                const checked = document.querySelectorAll('.item-checkbox:checked');
                const total = this.checkboxes.length;
                const count = checked.length;

                // Update Master styling (indeterminate)
                this.masterCheckbox.checked = (count === total) && total > 0;
                this.masterCheckbox.indeterminate = (count > 0 && count < total);

                // Update UI
                this.countDisplay.innerText = count;
                if (count > 0) {
                    this.bulkForm.classList.remove('hidden');

                    // Inject IDs into form
                    // Clean previous inputs first
                    const existingInputs = this.bulkForm.querySelectorAll('input[name="selected_ids[]"]');
                    existingInputs.forEach(el => el.remove());

                    checked.forEach(cb => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'selected_ids[]';
                        input.value = cb.dataset.id;
                        this.bulkForm.appendChild(input);
                    });

                } else {
                    this.bulkForm.classList.add('hidden');
                }
            }
        };

        /**
         * GalleryApp (Uploader)
         * Central logic for Modal, State, and Upload Process.
         */
        const GalleryApp = {
            state: {
                files: [], // Array of File objects
                isUploading: false,
                uploadUrl: '<?= base_url('admin/storeGallery') ?>'
            },

            init() {
                this.modal = document.getElementById('uploadModal');
                this.dropzone = document.getElementById('dropzone');
                this.fileInput = document.getElementById('fileInput');
                this.btnStart = document.getElementById('btnStartUpload');

                // --- Event Listeners ---

                // Open Modal
                document.getElementById('btnOpenUpload').addEventListener('click', () => {
                    this.modal.classList.remove('hidden');
                });

                // Input Click Trigger
                this.dropzone.addEventListener('click', () => this.fileInput.click());

                // File Input Change
                this.fileInput.addEventListener('change', (e) => {
                    this.handleFiles(Array.from(e.target.files));
                });

                // Drag & Drop
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(evt => {
                    this.dropzone.addEventListener(evt, (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                    });
                });

                ['dragenter', 'dragover'].forEach(evt => {
                    this.dropzone.addEventListener(evt, () => {
                        this.dropzone.classList.add('border-indigo-600', 'bg-indigo-50');
                    });
                });

                ['dragleave', 'drop'].forEach(evt => {
                    this.dropzone.addEventListener(evt, () => {
                        this.dropzone.classList.remove('border-indigo-600', 'bg-indigo-50');
                    });
                });

                this.dropzone.addEventListener('drop', (e) => {
                    this.handleFiles(Array.from(e.dataTransfer.files));
                });

                // Start Upload
                this.btnStart.addEventListener('click', () => this.startUpload());

                // Close on backdrop click
                document.getElementById('modalBackdrop').addEventListener('click', () => this.closeModal());
            },

            handleFiles(newFiles) {
                if (this.state.isUploading) return;

                // Validation filter (Images only)
                const validFiles = newFiles.filter(f => f.type.startsWith('image/'));

                if (validFiles.length < newFiles.length) {
                    alert("Beberapa file bukan gambar dan diabaikan.");
                }

                if (validFiles.length === 0) return;

                // Add to state
                this.state.files = this.state.files.concat(validFiles);

                // Update UI
                this.renderPreview();
            },

            renderPreview() {
                const initialView = document.getElementById('dropzoneInitial');
                const previewView = document.getElementById('dropzonePreview');
                const container = document.getElementById('previewContainer');
                const manifest = document.getElementById('filesManifest');
                const countDisplay = document.getElementById('fileCountDisplay');

                if (this.state.files.length > 0) {
                    initialView.classList.add('hidden');
                    previewView.classList.remove('hidden');
                    this.btnStart.disabled = false;

                    countDisplay.innerText = this.state.files.length;

                    // 1. Render Thumbnails (Max 20 for performance)
                    container.innerHTML = '';
                    this.state.files.slice(0, 20).forEach(file => {
                        const reader = new FileReader();
                        reader.readAsDataURL(file);
                        reader.onload = (e) => {
                            const div = document.createElement('div');
                            div.className = "aspect-w-1 aspect-h-1 relative rounded-lg overflow-hidden border border-gray-200 shadow-sm";
                            div.innerHTML = `<img src="${e.target.result}" class="object-cover w-full h-full">`;
                            container.appendChild(div);
                        };
                    });

                    if (this.state.files.length > 20) {
                        const more = document.createElement('div');
                        more.className = "flex items-center justify-center bg-gray-100 text-gray-500 font-bold rounded-lg border";
                        more.innerText = `+${this.state.files.length - 20}`;
                        container.appendChild(more);
                    }

                    // 2. Render Manifest
                    manifest.innerHTML = this.state.files.map(f =>
                        `<li>${f.name} <span class="text-gray-400">(${(f.size / 1024).toFixed(0)} KB)</span></li>`
                    ).join('');

                } else {
                    initialView.classList.remove('hidden');
                    previewView.classList.add('hidden');
                    this.btnStart.disabled = true;
                }
            },

            resetUpload() {
                if (this.state.isUploading) return;
                this.state.files = [];
                this.fileInput.value = '';
                this.renderPreview();
            },

            closeModal() {
                if (this.state.isUploading) {
                    if (!confirm("Upload sedang berjalan. Yakin ingin membatalkan?")) return;
                }
                this.modal.classList.add('hidden');
                this.resetUpload();
            },

            async startUpload() {
                if (this.state.files.length === 0) return;

                this.state.isUploading = true;
                this.btnStart.disabled = true;

                // UI Switch to Progress
                document.getElementById('dropzone').classList.add('opacity-50', 'pointer-events-none');
                document.getElementById('uploadProgress').classList.remove('hidden');

                const totalFiles = this.state.files.length;
                const BATCH_SIZE = 5;
                const categoryId = document.getElementById('uploadCategory').value;
                const csrfToken = document.querySelector('#csrfForm input[name="csrf_token"]').value;

                let uploadedCount = 0;
                let successCount = 0;
                let failCount = 0;

                const progressBar = document.getElementById('progressBar');
                const progressPercent = document.getElementById('progressPercent');
                const progressText = document.getElementById('progressText');

                try {
                    for (let i = 0; i < totalFiles; i += BATCH_SIZE) {
                        const batch = this.state.files.slice(i, i + BATCH_SIZE);
                        const formData = new FormData();

                        formData.append('category_id', categoryId);
                        formData.append('csrf_token', csrfToken); // Re-use token

                        batch.forEach(f => formData.append('image_upload[]', f));

                        // Update Status
                        progressText.innerText = `Mengupload batch ${Math.ceil((i + 1) / BATCH_SIZE)}... (${uploadedCount}/${totalFiles})`;

                        try {
                            const response = await fetch(this.state.uploadUrl, {
                                method: 'POST',
                                body: formData,
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            });

                            if (response.ok) {
                                const res = await response.json();
                                if (res.success) {
                                    successCount += (res.count || batch.length);
                                } else {
                                    failCount += batch.length;
                                    console.error("Batch failed:", res.message);
                                }
                            } else {
                                failCount += batch.length;
                            }
                        } catch (err) {
                            console.error("Network error:", err);
                            failCount += batch.length;
                        }

                        uploadedCount += batch.length;
                        const percent = Math.min(100, Math.round((uploadedCount / totalFiles) * 100));

                        progressBar.style.width = percent + "%";
                        progressPercent.innerText = percent + "%";
                    }

                    // Done
                    progressBar.classList.remove('bg-indigo-500');
                    progressBar.classList.add('bg-green-500');
                    progressText.innerHTML = `<span class="text-green-600 font-bold">Selesai! ${successCount} berhasil, ${failCount} gagal.</span>`;

                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);

                } catch (e) {
                    alert("Terjadi kesalahan fatal: " + e.message);
                    this.state.isUploading = false;
                    this.btnStart.disabled = false;
                }
            }
        };

        // Initialize Everything
        document.addEventListener('DOMContentLoaded', () => {
            BulkActionManager.init();
            GalleryApp.init();
            window.GalleryApp = GalleryApp; // Expose for specific onclicks
        });
    </script>
    <?php include dirname(__DIR__) . '/includes/dark-mode-script.php'; ?>
</body>

</html>