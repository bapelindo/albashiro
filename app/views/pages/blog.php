<!-- Blog Page -->
<section class="pt-32 pb-16 bg-gradient-to-br from-primary-50 via-cream-50 to-lavender-50 islamic-pattern">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto" data-aos="fade-up">
            <span
                class="inline-block px-4 py-2 bg-primary-100 text-primary-800 rounded-full text-sm font-semibold mb-4">
                <i class="fas fa-newspaper mr-2"></i>Blog
            </span>
            <h1 class="text-4xl md:text-5xl font-bold text-primary-900 mb-6">
                Artikel & <span class="gradient-text">Edukasi</span>
            </h1>
            <p class="text-lg text-gray-600">
                Pelajari lebih banyak tentang hipnoterapi Islami dan kesehatan mental dari artikel-artikel kami.
            </p>
        </div>
    </div>
</section>

<!-- Tags Filter Section -->
<section class="py-8 bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Search Bar -->
        <div class="mb-6">
            <form method="GET" action="<?= base_url('blog') ?>" class="max-w-2xl mx-auto">
                <div class="relative">
                    <input type="text" name="search" value="<?= isset($_GET['search']) ? e($_GET['search']) : '' ?>"
                        placeholder="Cari artikel..."
                        class="w-full px-6 py-4 pr-12 border border-gray-200 rounded-full focus:border-primary-500 focus:ring-2 focus:ring-primary-200 text-gray-700">
                    <button type="submit"
                        class="absolute right-2 top-1/2 -translate-y-1/2 px-6 py-2 bg-primary-600 text-white rounded-full hover:bg-primary-700 transition-colors">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>

        <!-- Tag Filter -->
        <div class="flex flex-wrap items-center gap-3">
            <span class="text-sm font-medium text-gray-700">Filter by Tag:</span>
            <a href="<?= base_url('blog') ?>"
                class="px-4 py-2 rounded-full text-sm font-medium transition-colors <?= !isset($_GET['tag']) ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                Semua
            </a>
            <?php
            // Get all unique tags
            $allTags = [];
            foreach ($posts as $post) {
                if (!empty($post->tags)) {
                    $postTags = array_map('trim', explode(',', $post->tags));
                    $allTags = array_merge($allTags, $postTags);
                }
            }
            $allTags = array_unique($allTags);
            sort($allTags);

            foreach ($allTags as $tag):
                $isActive = isset($_GET['tag']) && $_GET['tag'] === $tag;
                ?>
                <a href="<?= base_url('blog?tag=' . urlencode($tag)) ?>"
                    class="px-4 py-2 rounded-full text-sm font-medium transition-colors <?= $isActive ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                    <i class="fas fa-tag text-xs mr-1"></i><?= e($tag) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Blog Posts Grid -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Skeleton Loading (hidden by default, shown via JS) -->
        <div id="blog-skeleton" class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 hidden">
            <?php for ($i = 0; $i < 6; $i++): ?>
                <div class="bg-white rounded-2xl overflow-hidden shadow-lg border border-gray-100 animate-pulse">
                    <!-- Skeleton Image -->
                    <div class="h-56 bg-gray-200"></div>

                    <!-- Skeleton Content -->
                    <div class="p-6">
                        <!-- Skeleton Meta -->
                        <div class="flex items-center gap-3 mb-3">
                            <div class="h-3 bg-gray-200 rounded w-24"></div>
                            <div class="h-3 bg-gray-200 rounded w-20"></div>
                            <div class="h-3 bg-gray-200 rounded w-16"></div>
                        </div>

                        <!-- Skeleton Title -->
                        <div class="space-y-2 mb-4">
                            <div class="h-4 bg-gray-200 rounded w-full"></div>
                            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                        </div>

                        <!-- Skeleton Excerpt -->
                        <div class="space-y-2 mb-4">
                            <div class="h-3 bg-gray-200 rounded w-full"></div>
                            <div class="h-3 bg-gray-200 rounded w-full"></div>
                            <div class="h-3 bg-gray-200 rounded w-2/3"></div>
                        </div>

                        <!-- Skeleton Button -->
                        <div class="h-8 bg-gray-200 rounded w-32"></div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>

        <!-- Actual Blog Posts -->
        <div id="blog-posts">
            <?php if (empty($posts)): ?>
                <div class="text-center py-16">
                    <i class="fas fa-newspaper text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-lg">Belum ada artikel yang dipublikasikan.</p>
                </div>
            <?php else: ?>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($posts as $index => $post): ?>
                        <?php
                        // Calculate reading time (assuming 200 words per minute)
                        $wordCount = str_word_count(strip_tags($post->content));
                        $readingTime = ceil($wordCount / 200);
                        ?>
                        <article
                            class="group hover-lift bg-white rounded-2xl overflow-hidden shadow-lg border border-gray-100 transition-all hover:shadow-2xl"
                            data-aos="fade-up" data-aos-delay="<?= ($index % 3) * 100 ?>">
                            <!-- Featured Image -->
                            <a href="<?= base_url('blog/' . $post->slug) ?>" class="block relative h-56 overflow-hidden">
                                <?php if ($post->featured_image): ?>
                                    <img src="<?= base_url('public/images/' . $post->featured_image) ?>"
                                        alt="<?= e($post->title) ?>"
                                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                                    <div
                                        class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                                    </div>
                                <?php else: ?>
                                    <div
                                        class="w-full h-full bg-gradient-to-br from-primary-500 via-accent-600 to-purple-600 flex items-center justify-center">
                                        <i class="fas fa-newspaper text-6xl text-white/40"></i>
                                    </div>
                                <?php endif; ?>

                                <!-- Category Badge -->
                                <div class="absolute top-4 left-4">
                                    <span
                                        class="px-3 py-1.5 bg-white/95 backdrop-blur-sm text-primary-800 text-xs font-bold rounded-full shadow-lg">
                                        <?= e($post->category) ?>
                                    </span>
                                </div>
                            </a>

                            <!-- Content -->
                            <div class="p-6">
                                <!-- Meta Info -->
                                <div class="flex items-center text-xs text-gray-500 mb-3 flex-wrap gap-3">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar-alt mr-1.5 text-primary-600"></i>
                                        <?= date('d M Y', strtotime($post->published_at)) ?>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-clock mr-1.5 text-accent-600"></i>
                                        <?= $readingTime ?> min read
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-eye mr-1.5 text-gray-400"></i>
                                        <?= number_format($post->views) ?>
                                    </div>
                                </div>

                                <!-- Title -->
                                <h2 class="text-xl font-bold text-primary-900 mb-3 line-clamp-2 leading-tight">
                                    <a href="<?= base_url('blog/' . $post->slug) ?>"
                                        class="hover:text-accent-700 transition-colors">
                                        <?= e($post->title) ?>
                                    </a>
                                </h2>

                                <!-- Excerpt -->
                                <?php if ($post->excerpt): ?>
                                    <p class="text-gray-600 text-sm line-clamp-3 mb-4 leading-relaxed"><?= e($post->excerpt) ?></p>
                                <?php else: ?>
                                    <p class="text-gray-600 text-sm line-clamp-3 mb-4 leading-relaxed">
                                        <?= e(substr(strip_tags($post->content), 0, 150)) ?>...
                                    </p>
                                <?php endif; ?>

                                <!-- Tags -->
                                <?php if (!empty($post->tags)): ?>
                                    <div class="flex flex-wrap gap-2 mb-4">
                                        <?php foreach (array_slice(array_map('trim', explode(',', $post->tags)), 0, 3) as $tag): ?>
                                            <a href="<?= base_url('blog?tag=' . urlencode($tag)) ?>"
                                                class="px-2 py-1 bg-gray-100 hover:bg-primary-100 text-gray-600 hover:text-primary-700 text-xs rounded-full transition-colors">
                                                #<?= e($tag) ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Read More Link -->
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                    <a href="<?= base_url('blog/' . $post->slug) ?>"
                                        class="inline-flex items-center text-accent-600 hover:text-accent-700 font-semibold text-sm group/link">
                                        Baca Artikel
                                        <i
                                            class="fas fa-arrow-right ml-2 group-hover/link:translate-x-1 transition-transform"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if (!empty($posts) && $totalPages > 1): ?>
                    <div class="mt-16 flex justify-center" data-aos="fade-up">
                        <nav class="flex items-center space-x-2">
                            <!-- Previous Button -->
                            <a href="<?= $currentPage > 1 ? base_url('blog?page=' . ($currentPage - 1)) : '#' ?>"
                                class="px-4 py-2 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors <?= $currentPage <= 1 ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' ?>">
                                <i class="fas fa-chevron-left text-sm"></i>
                            </a>

                            <!-- Page Numbers -->
                            <?php
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($totalPages, $currentPage + 2);

                            // Show first page
                            if ($startPage > 1): ?>
                                <a href="<?= base_url('blog?page=1') ?>"
                                    class="px-4 py-2 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                    1
                                </a>
                                <?php if ($startPage > 2): ?>
                                    <span class="px-3 text-gray-400">...</span>
                                <?php endif; ?>
                            <?php endif; ?>

                            <!-- Middle pages -->
                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <a href="<?= base_url('blog?page=' . $i) ?>"
                                    class="px-4 py-2 <?= $i == $currentPage ? 'bg-primary-600 text-white' : 'bg-white border border-gray-200 hover:bg-gray-50' ?> rounded-lg font-medium transition-colors">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>

                            <!-- Show last page -->
                            <?php if ($endPage < $totalPages): ?>
                                <?php if ($endPage < $totalPages - 1): ?>
                                    <span class="px-3 text-gray-400">...</span>
                                <?php endif; ?>
                                <a href="<?= base_url('blog?page=' . $totalPages) ?>"
                                    class="px-4 py-2 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                    <?= $totalPages ?>
                                </a>
                            <?php endif; ?>

                            <!-- Next Button -->
                            <a href="<?= $currentPage < $totalPages ? base_url('blog?page=' . ($currentPage + 1)) : '#' ?>"
                                class="px-4 py-2 bg-white border border-gray-200 rounded-lg hover:bg-primary-50 hover:border-primary-300 transition-colors <?= $currentPage >= $totalPages ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' ?>">
                                <i class="fas fa-chevron-right text-sm"></i>
                            </a>
                        </nav>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div><!-- End #blog-posts -->
    </div>
</section>

<!-- Newsletter CTA -->
<section class="py-16 bg-gradient-to-r from-primary-800 to-accent-700">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-2xl md:text-3xl font-bold text-white mb-4">Ingin Update Artikel Terbaru?</h2>
        <p class="text-primary-100 mb-8">Ikuti kami di media sosial untuk tips kesehatan mental Islami</p>
        <div class="flex justify-center space-x-4">
            <a href="#"
                class="w-12 h-12 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center transition-colors">
                <i class="fab fa-instagram text-xl text-white"></i>
            </a>
            <a href="#"
                class="w-12 h-12 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center transition-colors">
                <i class="fab fa-facebook-f text-xl text-white"></i>
            </a>
            <a href="#"
                class="w-12 h-12 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center transition-colors">
                <i class="fab fa-youtube text-xl text-white"></i>
            </a>
        </div>
    </div>
</section>