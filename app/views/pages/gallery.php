<!-- Gallery Hero -->
<section class="pt-32 pb-16 bg-gradient-to-br from-primary-50 via-cream-50 to-lavender-50 islamic-pattern">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto" data-aos="fade-up">
            <span
                class="inline-block px-4 py-2 bg-primary-100 text-primary-800 rounded-full text-sm font-semibold mb-4">
                <i class="fas fa-images mr-2"></i>Dokumentasi
            </span>
            <h1 class="text-4xl md:text-5xl font-bold text-primary-900 mb-6">
                Galeri <span class="gradient-text">Albashiro</span>
            </h1>
            <p class="text-lg text-gray-600">
                Dokumentasi kegiatan, fasilitas, dan momen berharga dalam perjalanan penyembuhan bersama kami.
            </p>
        </div>
    </div>
</section>

<!-- Gallery Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Category Filter -->
        <div class="flex flex-wrap justify-center gap-4 mb-12" data-aos="fade-up">
            <a href="<?= base_url('galeri') ?>"
                class="px-6 py-2 rounded-full border transition-all duration-300 <?= !$currentCategory ? 'bg-primary-600 text-white border-primary-600 shadow-lg' : 'bg-white text-gray-600 border-gray-300 hover:border-primary-500 hover:text-primary-600' ?>">
                Semua
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="<?= base_url('galeri?kategori=' . $cat->id) ?>"
                    class="px-6 py-2 rounded-full border transition-all duration-300 <?= $currentCategory == $cat->id ? 'bg-primary-600 text-white border-primary-600 shadow-lg' : 'bg-white text-gray-600 border-gray-300 hover:border-primary-500 hover:text-primary-600' ?>">
                    <?= e($cat->name) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Gallery Grid (Masonry Layout) -->
        <div class="columns-1 md:columns-2 lg:columns-3 gap-6 space-y-6">
            <?php foreach ($galleries as $index => $image): ?>
                <div class="break-inside-avoid group relative bg-white rounded-2xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1"
                    data-aos="fade-up" data-aos-delay="<?= ($index % 3) * 100 ?>">

                    <!-- Wrapper with Skeleton State -->
                    <div class="relative w-full bg-gray-200 animate-pulse min-h-[250px] transition-all duration-500"
                        id="wrapper-<?= $image->id ?>">
                        <!-- Loading Icon (Center) -->
                        <div class="absolute inset-0 flex items-center justify-center text-gray-400 skeleton-icon">
                            <i class="fas fa-image text-4xl"></i>
                        </div>

                        <img src="<?= base_url('public/images/' . $image->image_url) ?>" alt="Albashiro Gallery"
                            loading="lazy"
                            class="relative z-10 w-full h-auto object-cover opacity-0 transition-opacity duration-700 block"
                            onload="this.classList.remove('opacity-0'); document.getElementById('wrapper-<?= $image->id ?>').classList.remove('animate-pulse', 'bg-gray-200', 'min-h-[250px]'); document.getElementById('wrapper-<?= $image->id ?>').querySelector('.skeleton-icon').remove();"
                            onerror="this.onerror=null; this.src='https://placehold.co/600x400?text=Image+Not+Found'; this.classList.remove('opacity-0'); document.getElementById('wrapper-<?= $image->id ?>').classList.remove('animate-pulse', 'bg-gray-200');">

                        <!-- Overlay -->
                        <div
                            class="absolute inset-0 z-20 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-6">
                            <span
                                class="inline-block px-3 py-1 text-sm font-medium text-white bg-primary-600/90 rounded-full backdrop-blur-sm shadow-sm">
                                <?= e($image->category_name ?? 'Albashiro') ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($galleries)): ?>
            <div class="text-center py-20">
                <div class="inline-block p-6 rounded-full bg-gray-50 mb-4">
                    <i class="fas fa-images text-4xl text-gray-400"></i>
                </div>
                <h3 class="text-xl font-medium text-gray-600 mb-2">Belum ada foto</h3>
                <p class="text-gray-500">Galeri untuk kategori ini masih kosong.</p>
                <?php if ($currentCategory): ?>
                    <a href="<?= base_url('galeri') ?>"
                        class="inline-block mt-4 text-primary-600 hover:text-primary-700 font-medium">Lihat Semua Foto</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="mt-16 flex justify-center" data-aos="fade-up">
                <nav class="inline-flex rounded-full shadow-sm bg-white p-1 border border-gray-100" aria-label="Pagination">
                    <!-- Previous -->
                    <a href="<?= $page > 1 ? base_url("galeri?page=" . ($page - 1) . ($currentCategory ? "&kategori=$currentCategory" : "")) : '#' ?>"
                        class="relative inline-flex items-center rounded-l-full px-4 py-2 text-sm font-medium transition-colors duration-200 <?= $page <= 1 ? 'text-gray-300 cursor-not-allowed' : 'text-gray-500 hover:bg-gray-50 hover:text-primary-600' ?>">
                        <i class="fas fa-chevron-left mr-2"></i> Prev
                    </a>

                    <!-- Page Numbers -->
                    <div class="hidden md:flex border-l border-r border-gray-100 mx-1">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="<?= base_url("galeri?page=$i" . ($currentCategory ? "&kategori=$currentCategory" : "")) ?>"
                                class="relative inline-flex items-center px-4 py-2 text-sm font-medium transition-colors duration-200 <?= $page == $i ? 'text-primary-600 bg-primary-50' : 'text-gray-500 hover:bg-gray-50 hover:text-primary-600' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>

                    <!-- Mobile Page Info -->
                    <div class="flex md:hidden items-center px-4 border-l border-r border-gray-100">
                         <span class="text-sm text-gray-700"><?= $page ?> / <?= $totalPages ?></span>
                    </div>

                    <!-- Next -->
                    <a href="<?= $page < $totalPages ? base_url("galeri?page=" . ($page + 1) . ($currentCategory ? "&kategori=$currentCategory" : "")) : '#' ?>"
                        class="relative inline-flex items-center rounded-r-full px-4 py-2 text-sm font-medium transition-colors duration-200 <?= $page >= $totalPages ? 'text-gray-300 cursor-not-allowed' : 'text-gray-500 hover:bg-gray-50 hover:text-primary-600' ?>">
                        Next <i class="fas fa-chevron-right ml-2"></i>
                    </a>
                </nav>
            </div>
        <?php endif; ?>

    </div>
</section>