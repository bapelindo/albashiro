<!-- Single Blog Post Page -->
<section class="pt-32 pb-8 bg-gradient-to-br from-primary-50 via-cream-50 to-lavender-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="mb-6" data-aos="fade-up">
            <ol class="flex items-center space-x-2 text-sm text-gray-500">
                <li><a href="<?= base_url() ?>" class="hover:text-primary-700">Beranda</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li><a href="<?= base_url('blog') ?>" class="hover:text-primary-700">Blog</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li class="text-primary-800 font-medium truncate"><?= e($post->title) ?></li>
            </ol>
        </nav>

        <!-- Article Header -->
        <header data-aos="fade-up">
            <span
                class="inline-block px-4 py-2 bg-primary-100 text-primary-800 rounded-full text-sm font-semibold mb-4">
                <?= e($post->category) ?>
            </span>
            <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-primary-900 mb-6 leading-tight">
                <?= e($post->title) ?>
            </h1>

            <div class="flex flex-wrap items-center gap-4 text-gray-500 text-sm">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-primary-200 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-user text-primary-600"></i>
                    </div>
                    <span><?= e($post->author_name) ?></span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-calendar mr-2"></i>
                    <?= date('d F Y', strtotime($post->published_at)) ?>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-eye mr-2"></i>
                    <?= number_format($post->views) ?> views
                </div>
            </div>
        </header>
    </div>
</section>

<!-- Article Content -->
<section class="py-12 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-3 gap-12">
            <!-- Main Content -->
            <article class="lg:col-span-2" data-aos="fade-up">
                <!-- Featured Image -->
                <?php if ($post->featured_image): ?>
                    <div class="mb-8 rounded-2xl overflow-hidden shadow-lg">
                        <img src="<?= base_url('public/images/' . $post->featured_image) ?>" alt="<?= e($post->title) ?>"
                            class="w-full h-auto">
                    </div>
                <?php endif; ?>

                <!-- Content -->
                <div
                    class="prose prose-lg max-w-none prose-headings:text-primary-900 prose-a:text-accent-600 prose-strong:text-primary-800">
                    <?= $post->content ?>
                </div>

                <!-- Tags -->
                <?php if ($post->tags): ?>
                    <div class="mt-8 pt-8 border-t border-gray-200">
                        <div class="flex flex-wrap gap-2">
                            <?php foreach (explode(',', $post->tags) as $tag): ?>
                                <span class="px-4 py-2 bg-gray-100 text-gray-600 text-sm rounded-full">
                                    #<?= e(trim($tag)) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Share -->
                <div class="mt-8 pt-8 border-t border-gray-200">
                    <p class="font-semibold text-primary-900 mb-4">Bagikan Artikel:</p>
                    <div class="flex space-x-3">
                        <a href="https://wa.me/?text=<?= urlencode($post->title . ' - ' . base_url('blog/' . $post->slug)) ?>"
                            target="_blank"
                            class="w-10 h-10 bg-green-500 hover:bg-green-600 rounded-lg flex items-center justify-center transition-colors">
                            <i class="fab fa-whatsapp text-white"></i>
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(base_url('blog/' . $post->slug)) ?>"
                            target="_blank"
                            class="w-10 h-10 bg-blue-600 hover:bg-blue-700 rounded-lg flex items-center justify-center transition-colors">
                            <i class="fab fa-facebook-f text-white"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?text=<?= urlencode($post->title) ?>&url=<?= urlencode(base_url('blog/' . $post->slug)) ?>"
                            target="_blank"
                            class="w-10 h-10 bg-sky-500 hover:bg-sky-600 rounded-lg flex items-center justify-center transition-colors">
                            <i class="fab fa-twitter text-white"></i>
                        </a>
                    </div>
                </div>
            </article>

            <!-- Sidebar -->
            <aside class="lg:col-span-1" data-aos="fade-left">
                <div class="sticky top-24">
                    <!-- Recent Posts -->
                    <div class="bg-cream-50 rounded-2xl p-6">
                        <h3 class="font-bold text-primary-900 mb-4">Artikel Lainnya</h3>
                        <div class="space-y-4">
                            <?php foreach ($recentPosts as $recent): ?>
                                <?php if ($recent->id !== $post->id): ?>
                                    <a href="<?= base_url('blog/' . $recent->slug) ?>" class="block group">
                                        <h4
                                            class="text-sm font-medium text-gray-800 group-hover:text-accent-600 transition-colors line-clamp-2">
                                            <?= e($recent->title) ?>
                                        </h4>
                                        <p class="text-xs text-gray-500 mt-1">
                                            <?= date('d M Y', strtotime($recent->published_at)) ?>
                                        </p>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- CTA -->
                    <div class="bg-gradient-to-br from-primary-700 to-accent-700 rounded-2xl p-6 mt-6 text-white">
                        <h3 class="font-bold mb-2">Butuh Konsultasi?</h3>
                        <p class="text-sm text-primary-100 mb-4">Kami siap membantu Anda</p>
                        <a href="<?= base_url('reservasi') ?>"
                            class="block w-full py-3 bg-white text-primary-800 text-center font-semibold rounded-xl hover:bg-cream-50 transition-colors">
                            Reservasi Sekarang
                        </a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

<!-- Back to Blog -->
<section class="py-8 bg-cream-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="<?= base_url('blog') ?>"
            class="inline-flex items-center text-primary-700 hover:text-primary-800 font-medium">
            <i class="fas fa-arrow-left mr-2"></i>
            Kembali ke Blog
        </a>
    </div>
</section>