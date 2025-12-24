<!-- SECTION: Hero -->
<section id="beranda" class="relative min-h-screen flex items-center pt-20 overflow-hidden w-full">
    <!-- Background with Islamic Pattern -->
    <div class="absolute inset-0 islamic-pattern bg-gradient-to-br from-primary-50 via-cream-50 to-lavender-50"></div>

    <!-- Decorative Elements - contained within section -->
    <div
        class="absolute top-20 -right-32 w-64 md:right-0 md:w-96 h-64 md:h-96 bg-accent-200/20 rounded-full blur-3xl gentle-pulse">
    </div>
    <div
        class="absolute bottom-0 -left-32 w-64 md:left-0 md:w-80 h-64 md:h-80 bg-lavender-200/30 rounded-full blur-3xl">
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-20 w-full">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <!-- Left Content -->
            <div class="text-center lg:text-left" data-aos="fade-right">
                <!-- Arabic Bismillah -->
                <p class="text-2xl md:text-3xl font-arabic text-gold-500 mb-4">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ
                </p>

                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-primary-900 leading-tight mb-6">
                    Temukan <span class="gradient-text">Kedamaian Jiwa</span> dengan Hipnoterapi Islami
                </h1>

                <p class="text-lg md:text-xl text-gray-600 mb-8 leading-relaxed max-w-xl mx-auto lg:mx-0">
                    Layanan hipnoterapi profesional yang menggabungkan pendekatan psikologi modern dengan nilai-nilai
                    spiritual Islam untuk membantu Anda meraih ketenangan batin.
                </p>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start mb-10">
                    <a href="<?= base_url('reservasi') ?>"
                        class="inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-primary-700 to-accent-600 text-white font-semibold rounded-full shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all">
                        <i class="fas fa-calendar-check mr-2"></i>
                        Reservasi Sekarang
                    </a>
                    <a href="<?= base_url('tentang') ?>"
                        class="inline-flex items-center justify-center px-8 py-4 bg-white text-primary-700 font-semibold rounded-full border-2 border-primary-200 hover:border-primary-400 hover:bg-primary-50 transition-all">
                        <i class="fas fa-play-circle mr-2"></i>
                        Pelajari Lebih Lanjut
                    </a>
                </div>

                <!-- Trust Badges -->
                <div class="flex flex-wrap gap-6 justify-center lg:justify-start">
                    <div class="flex items-center space-x-2 text-gray-600">
                        <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-certificate text-primary-600"></i>
                        </div>
                        <span class="text-sm font-medium">Tersertifikasi</span>
                    </div>
                    <div class="flex items-center space-x-2 text-gray-600">
                        <div class="w-10 h-10 bg-gold-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-users text-gold-500"></i>
                        </div>
                        <span class="text-sm font-medium">500+ Klien</span>
                    </div>
                    <div class="flex items-center space-x-2 text-gray-600">
                        <div class="w-10 h-10 bg-accent-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-star text-accent-600"></i>
                        </div>
                        <span class="text-sm font-medium">Rating 4.9</span>
                    </div>
                </div>
            </div>

            <!-- Right Image -->
            <div class="relative" data-aos="fade-left" data-aos-delay="200">
                <div class="relative z-10">
                    <!-- Main Image Placeholder -->
                    <div
                        class="aspect-square w-full max-w-md mx-auto lg:max-w-lg rounded-3xl overflow-hidden shadow-2xl hypno-glow">
                        <img src="<?= base_url('public/images/hero-hypnotherapy.jpg') ?>"
                            alt="Islamic Hypnotherapy Session" class="w-full h-full object-cover">
                    </div>

                    <!-- Floating Card 1 -->
                    <div class="absolute -bottom-6 left-4 sm:-left-6 bg-white rounded-2xl shadow-xl p-3 sm:p-4 animate-bounce max-w-[90%] sm:max-w-none"
                        style="animation-duration: 3s;">
                        <div class="flex items-center space-x-2 sm:space-x-3">
                            <div
                                class="w-10 h-10 sm:w-12 sm:h-12 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fab fa-whatsapp text-green-600 text-lg sm:text-xl"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs sm:text-sm font-semibold text-gray-800 truncate">Konsultasi Gratis</p>
                                <p class="text-xs text-gray-500">via WhatsApp</p>
                            </div>
                        </div>
                    </div>

                    <!-- Floating Card 2 -->
                    <div
                        class="absolute -top-4 right-4 sm:-right-4 bg-white rounded-2xl shadow-xl p-3 sm:p-4 max-w-[90%] sm:max-w-none">
                        <div class="flex items-center space-x-2">
                            <div class="flex -space-x-2 flex-shrink-0">
                                <img src="<?= base_url('public/images/therapist-1.jpg') ?>" alt="Terapis 1"
                                    class="w-7 h-7 sm:w-8 sm:h-8 rounded-full border-2 border-white object-cover">
                                <img src="<?= base_url('public/images/therapist-2.jpg') ?>" alt="Terapis 2"
                                    class="w-7 h-7 sm:w-8 sm:h-8 rounded-full border-2 border-white object-cover">
                                <img src="<?= base_url('public/images/therapist-3.jpg') ?>" alt="Terapis 3"
                                    class="w-7 h-7 sm:w-8 sm:h-8 rounded-full border-2 border-white object-cover">
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs sm:text-sm font-semibold text-gray-800">3 Terapis</p>
                                <p class="text-xs text-gray-500">Profesional</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Background Decorations -->
                <div
                    class="hidden sm:block absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full max-w-lg max-h-lg rounded-full border-2 border-dashed border-accent-200 opacity-50">
                </div>
            </div>
        </div>
    </div>

    <!-- Scroll Indicator -->
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
        <a href="#tentang" class="flex flex-col items-center text-gray-400 hover:text-primary-600 transition-colors">
            <span class="text-sm mb-2">Scroll</span>
            <i class="fas fa-chevron-down"></i>
        </a>
    </div>
</section>

<!-- SECTION: About / Mengapa Memilih Kami -->
<section id="tentang" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
            <!-- Left Image -->
            <div class="relative" data-aos="fade-right">
                <div class="relative z-10 rounded-3xl overflow-hidden shadow-2xl">
                    <img src="<?= base_url('public/images/islamic-approach.jpg') ?>" alt="Islamic Approach to Therapy"
                        class="aspect-[4/3] w-full object-cover">
                </div>
                <!-- Decorative Frame -->
                <div class="absolute -bottom-6 -right-6 w-full h-full border-4 border-gold-300 rounded-3xl -z-10"></div>
            </div>

            <!-- Right Content -->
            <div data-aos="fade-left">
                <span
                    class="inline-block px-4 py-2 bg-primary-100 text-primary-700 rounded-full text-sm font-semibold mb-4">
                    <i class="fas fa-info-circle mr-2"></i>Tentang Kami
                </span>

                <h2 class="text-3xl md:text-4xl font-bold text-primary-900 mb-6">
                    Mengapa Memilih <span class="gradient-text">Albashiro?</span>
                </h2>

                <p class="text-gray-600 leading-relaxed mb-8">
                    Albashiro adalah layanan hipnoterapi profesional yang menggabungkan teknik hipnoterapi modern dengan
                    bimbingan spiritual Islami. Kami percaya bahwa kesembuhan jiwa datang dari keselarasan antara
                    pikiran, hati, dan spiritualitas.
                </p>

                <!-- Features Grid -->
                <div class="grid sm:grid-cols-2 gap-6">
                    <div class="group p-6 bg-primary-50 rounded-2xl hover:bg-primary-100 transition-colors">
                        <div
                            class="w-14 h-14 bg-white rounded-xl shadow-md flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-book-quran text-2xl text-primary-700"></i>
                        </div>
                        <h4 class="font-semibold text-gray-800 mb-2">Pendekatan Syar'i</h4>
                        <p class="text-sm text-gray-600">Terapi berdasarkan nilai-nilai Al-Quran dan Sunnah</p>
                    </div>

                    <div class="group p-6 bg-gold-50 rounded-2xl hover:bg-gold-100 transition-colors">
                        <div
                            class="w-14 h-14 bg-white rounded-xl shadow-md flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-award text-2xl text-gold-500"></i>
                        </div>
                        <h4 class="font-semibold text-gray-800 mb-2">Terapis Bersertifikasi</h4>
                        <p class="text-sm text-gray-600">Profesional dengan kredensial resmi</p>
                    </div>

                    <div class="group p-6 bg-lavender-50 rounded-2xl hover:bg-lavender-100 transition-colors">
                        <div
                            class="w-14 h-14 bg-white rounded-xl shadow-md flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-user-shield text-2xl text-accent-600"></i>
                        </div>
                        <h4 class="font-semibold text-gray-800 mb-2">Privasi Terjaga</h4>
                        <p class="text-sm text-gray-600">Kerahasiaan klien adalah prioritas utama</p>
                    </div>

                    <div class="group p-6 bg-calm-50 rounded-2xl hover:bg-calm-100 transition-colors">
                        <div
                            class="w-14 h-14 bg-white rounded-xl shadow-md flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-brain text-2xl text-calm-600"></i>
                        </div>
                        <h4 class="font-semibold text-gray-800 mb-2">Metode Modern</h4>
                        <p class="text-sm text-gray-600">Teknik hipnoterapi terkini yang terbukti efektif</p>
                    </div>
                </div>

                <div class="mt-8">
                    <a href="<?= base_url('tentang') ?>"
                        class="inline-flex items-center text-primary-700 hover:text-primary-800 font-semibold">
                        Selengkapnya tentang kami
                        <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION: Animated Statistics Counter -->
<section class="py-20 bg-gradient-to-br from-primary-800 via-primary-900 to-accent-900 relative overflow-hidden">
    <!-- Decorative Background -->
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 left-0 w-96 h-96 bg-white rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-accent-300 rounded-full blur-3xl"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center text-white">
            <!-- Stat 1 -->
            <div data-aos="fade-up" data-aos-delay="0">
                <div class="mb-3">
                    <i class="fas fa-users text-4xl text-gold-300 mb-2"></i>
                </div>
                <div class="text-4xl md:text-5xl font-bold mb-2">
                    <span class="counter" data-target="500">0</span>+
                </div>
                <p class="text-primary-200 font-medium">Klien Puas</p>
            </div>

            <!-- Stat 2 -->
            <div data-aos="fade-up" data-aos-delay="100">
                <div class="mb-3">
                    <i class="fas fa-calendar-check text-4xl text-gold-300 mb-2"></i>
                </div>
                <div class="text-4xl md:text-5xl font-bold mb-2">
                    <span class="counter" data-target="1000">0</span>+
                </div>
                <p class="text-primary-200 font-medium">Sesi Terapi</p>
            </div>

            <!-- Stat 3 -->
            <div data-aos="fade-up" data-aos-delay="200">
                <div class="mb-3">
                    <i class="fas fa-award text-4xl text-gold-300 mb-2"></i>
                </div>
                <div class="text-4xl md:text-5xl font-bold mb-2">
                    <span class="counter" data-target="5">0</span>+
                </div>
                <p class="text-primary-200 font-medium">Tahun Pengalaman</p>
            </div>

            <!-- Stat 4 -->
            <div data-aos="fade-up" data-aos-delay="300">
                <div class="mb-3">
                    <i class="fas fa-star text-4xl text-gold-300 mb-2"></i>
                </div>
                <div class="text-4xl md:text-5xl font-bold mb-2">
                    <span class="counter" data-target="98">0</span>%
                </div>
                <p class="text-primary-200 font-medium">Tingkat Kepuasan</p>
            </div>
        </div>
    </div>
</section>

<!-- SECTION: Services / Layanan -->
<section id="layanan" class="py-24 bg-gradient-to-b from-cream-50 to-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center max-w-3xl mx-auto mb-16" data-aos="fade-up">
            <span
                class="inline-block px-4 py-2 bg-primary-100 text-primary-700 rounded-full text-sm font-semibold mb-4">
                <i class="fas fa-hand-holding-heart mr-2"></i>Layanan Kami
            </span>
            <h2 class="text-3xl md:text-4xl font-bold text-primary-900 mb-4">
                Apa yang Bisa <span class="gradient-text">Kami Bantu?</span>
            </h2>
            <p class="text-gray-600">
                Kami menyediakan berbagai layanan hipnoterapi dan konseling untuk membantu Anda mengatasi berbagai
                masalah psikologis dan spiritual.
            </p>
        </div>

        <!-- Services Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($services as $index => $service): ?>
                <div class="group hover-lift bg-white rounded-2xl p-6 shadow-lg border border-gray-100" data-aos="fade-up"
                    data-aos-delay="<?= ($index % 4) * 100 ?>">
                    <!-- Icon -->
                    <div
                        class="w-16 h-16 bg-gradient-to-br from-primary-100 to-accent-100 rounded-2xl flex items-center justify-center mb-5 group-hover:from-primary-600 group-hover:to-accent-600 transition-all">
                        <i
                            class="fas <?= e($service->icon) ?> text-2xl text-primary-700 group-hover:text-white transition-colors"></i>
                    </div>

                    <!-- Content -->
                    <h3 class="text-lg font-semibold text-primary-900 mb-2"><?= e($service->name) ?></h3>
                    <p class="text-sm text-gray-600 mb-4 line-clamp-3"><?= e($service->description) ?></p>

                    <!-- Meta -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                        <span class="text-xs font-medium px-3 py-1 bg-primary-50 text-primary-700 rounded-full">
                            <?= e($service->target_audience) ?>
                        </span>
                        <span class="text-primary-700 font-semibold text-sm">
                            <?= format_rupiah($service->price) ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- View All Link -->
        <div class="text-center mt-12" data-aos="fade-up">
            <a href="<?= base_url('layanan') ?>"
                class="inline-flex items-center text-primary-700 hover:text-primary-800 font-semibold group">
                Lihat Semua Layanan
                <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
            </a>
        </div>
    </div>
</section>

<!-- SECTION: Process / Alur Terapi -->
<section class="py-24 bg-primary-800 relative overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
        <div class="islamic-pattern w-full h-full"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center max-w-3xl mx-auto mb-16" data-aos="fade-up">
            <span class="inline-block px-4 py-2 bg-white/20 text-white rounded-full text-sm font-semibold mb-4">
                <i class="fas fa-route mr-2"></i>Alur Layanan
            </span>
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                Bagaimana Proses Terapinya?
            </h2>
            <p class="text-primary-200">
                Empat langkah mudah untuk memulai perjalanan menuju kedamaian jiwa
            </p>
        </div>

        <!-- Process Steps -->
        <div class="grid md:grid-cols-4 gap-8">
            <!-- Step 1 -->
            <div class="relative text-center" data-aos="fade-up" data-aos-delay="0">
                <div
                    class="w-20 h-20 bg-white rounded-2xl shadow-xl flex items-center justify-center mx-auto mb-6 relative z-10">
                    <span
                        class="absolute -top-3 -right-3 w-8 h-8 bg-gold-400 text-primary-900 rounded-full flex items-center justify-center font-bold text-sm">1</span>
                    <i class="fas fa-calendar-plus text-3xl text-primary-700"></i>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">Buat Janji</h3>
                <p class="text-primary-200 text-sm">Isi form reservasi atau hubungi kami via WhatsApp</p>
                <div class="hidden md:block absolute top-10 left-[60%] w-full h-0.5 bg-white/20"></div>
            </div>

            <!-- Step 2 -->
            <div class="relative text-center" data-aos="fade-up" data-aos-delay="100">
                <div
                    class="w-20 h-20 bg-white rounded-2xl shadow-xl flex items-center justify-center mx-auto mb-6 relative z-10">
                    <span
                        class="absolute -top-3 -right-3 w-8 h-8 bg-gold-400 text-primary-900 rounded-full flex items-center justify-center font-bold text-sm">2</span>
                    <i class="fas fa-comments text-3xl text-primary-700"></i>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">Konsultasi Awal</h3>
                <p class="text-primary-200 text-sm">Ceritakan keluhan dan harapan Anda kepada terapis</p>
                <div class="hidden md:block absolute top-10 left-[60%] w-full h-0.5 bg-white/20"></div>
            </div>

            <!-- Step 3 -->
            <div class="relative text-center" data-aos="fade-up" data-aos-delay="200">
                <div
                    class="w-20 h-20 bg-white rounded-2xl shadow-xl flex items-center justify-center mx-auto mb-6 relative z-10">
                    <span
                        class="absolute -top-3 -right-3 w-8 h-8 bg-gold-400 text-primary-900 rounded-full flex items-center justify-center font-bold text-sm">3</span>
                    <i class="fas fa-spa text-3xl text-primary-700"></i>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">Sesi Hipnoterapi</h3>
                <p class="text-primary-200 text-sm">Jalani sesi terapi dalam kondisi relaksasi mendalam</p>
                <div class="hidden md:block absolute top-10 left-[60%] w-full h-0.5 bg-white/20"></div>
            </div>

            <!-- Step 4 -->
            <div class="relative text-center" data-aos="fade-up" data-aos-delay="300">
                <div
                    class="w-20 h-20 bg-white rounded-2xl shadow-xl flex items-center justify-center mx-auto mb-6 relative z-10">
                    <span
                        class="absolute -top-3 -right-3 w-8 h-8 bg-gold-400 text-primary-900 rounded-full flex items-center justify-center font-bold text-sm">4</span>
                    <i class="fas fa-clipboard-check text-3xl text-primary-700"></i>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">Follow Up</h3>
                <p class="text-primary-200 text-sm">Evaluasi perkembangan dan rekomendasi lanjutan</p>
            </div>
        </div>
    </div>
</section>

<!-- SECTION: Meet Our Therapists -->
<section id="terapis" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center max-w-3xl mx-auto mb-16" data-aos="fade-up">
            <span
                class="inline-block px-4 py-2 bg-primary-100 text-primary-700 rounded-full text-sm font-semibold mb-4">
                <i class="fas fa-user-md mr-2"></i>Tim Kami
            </span>
            <h2 class="text-3xl md:text-4xl font-bold text-primary-900 mb-4">
                Kenali <span class="gradient-text">Terapis Kami</span>
            </h2>
            <p class="text-gray-600">
                Dipandu oleh para profesional berpengalaman dan bersertifikasi di bidang hipnoterapi Islami
            </p>
        </div>

        <!-- Therapists Grid -->
        <div class="grid md:grid-cols-3 gap-8">
            <?php foreach ($therapists as $index => $therapist): ?>
                <div class="group hover-lift bg-white rounded-3xl overflow-hidden shadow-lg border border-gray-100"
                    data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                    <!-- Photo -->
                    <div class="relative h-72 overflow-hidden">
                        <?php
                        // Determine image source
                        $imagePath = 'public/images/' . $therapist->photo_url;
                        $realPath = SITE_ROOT . '/' . $imagePath;

                        // Fallback mapping if database photo doesn't exist
                        $fallbackImages = [
                            'Ustadz Ahmad Fadhil' => 'therapist-1.jpg',
                            'Dr. Siti Aminah' => 'therapist-2.jpg',
                            'Ustadzah Fatimah Zahra' => 'therapist-3.jpg'
                        ];

                        if (file_exists($realPath) && !empty($therapist->photo_url)) {
                            $finalImage = $therapist->photo_url;
                        } else {
                            $finalImage = $fallbackImages[$therapist->name] ?? 'therapist-2.jpg';
                        }
                        ?>
                        <img src="<?= base_url('public/images/' . $finalImage) ?>" alt="<?= e($therapist->name) ?>"
                            class="w-full h-full object-cover">
                        <!-- Overlay on hover -->
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-primary-900/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-6">
                            <div class="text-white">
                                <p class="text-sm"><?= e($therapist->credentials) ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h3 class="text-xl font-bold text-primary-900"><?= e($therapist->name) ?></h3>
                                <p class="text-accent-600 text-sm font-medium"><?= e($therapist->title) ?></p>
                            </div>
                            <span class="px-3 py-1 bg-gold-100 text-gold-600 rounded-full text-xs font-semibold">
                                <?= e($therapist->experience_years) ?> Tahun
                            </span>
                        </div>

                        <p class="text-gray-600 text-sm mb-4"><?= e($therapist->specialty) ?></p>
                        <p class="text-gray-500 text-sm line-clamp-3"><?= e($therapist->bio) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-12" data-aos="fade-up">
            <a href="<?= base_url('terapis') ?>"
                class="inline-flex items-center text-primary-700 hover:text-primary-800 font-semibold group">
                Lihat Profil Lengkap
                <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
            </a>
        </div>
    </div>
</section>

<!-- SECTION: Testimonials with Swiper Carousel -->
<section id="testimoni" class="py-24 bg-gradient-to-b from-lavender-50 to-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center max-w-3xl mx-auto mb-16" data-aos="fade-up">
            <span
                class="inline-block px-4 py-2 bg-primary-100 text-primary-700 rounded-full text-sm font-semibold mb-4">
                <i class="fas fa-quote-left mr-2"></i>Testimoni
            </span>
            <h2 class="text-3xl md:text-4xl font-bold text-primary-900 mb-4">
                Apa Kata <span class="gradient-text">Klien Kami?</span>
            </h2>
            <p class="text-gray-600">
                Cerita nyata dari mereka yang telah merasakan perubahan positif
            </p>
        </div>

        <!-- Testimonials Carousel -->
        <div class="swiper testimonial-swiper pt-6" data-aos="fade-up">
            <div class="swiper-wrapper pb-12">
                <?php foreach ($testimonials as $testimonial): ?>
                    <div class="swiper-slide">
                        <div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100 h-full relative mt-4">
                            <!-- Quote Icon -->
                            <div class="absolute -top-5 left-6 z-10">
                                <div
                                    class="w-10 h-10 bg-primary-600 rounded-full flex items-center justify-center shadow-md">
                                    <i class="fas fa-quote-left text-white text-sm"></i>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="pt-4">
                                <!-- Stars -->
                                <div class="flex space-x-1 mb-4">
                                    <?php for ($i = 0; $i < $testimonial->rating; $i++): ?>
                                        <i class="fas fa-star text-gold-400 star-animate"
                                            style="animation-delay: <?= $i * 0.1 ?>s"></i>
                                    <?php endfor; ?>
                                </div>

                                <p class="text-gray-600 mb-6 leading-relaxed">"<?= e($testimonial->content) ?>"</p>

                                <!-- Author -->
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center">
                                            <span
                                                class="text-primary-700 font-bold"><?= e($testimonial->client_initial) ?></span>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-primary-900"><?= e($testimonial->client_name) ?>
                                            </p>
                                            <p class="text-sm text-gray-500"><?= e($testimonial->client_location) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- Pagination -->
            <div class="swiper-pagination"></div>
            <!-- Navigation -->
            <div class="swiper-button-next hidden md:flex"></div>
            <div class="swiper-button-prev hidden md:flex"></div>
        </div>
    </div>
</section>

<!-- SECTION: FAQ -->
<section id="faq" class="py-24 bg-cream-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-16" data-aos="fade-up">
            <span
                class="inline-block px-4 py-2 bg-primary-100 text-primary-700 rounded-full text-sm font-semibold mb-4">
                <i class="fas fa-question-circle mr-2"></i>FAQ
            </span>
            <h2 class="text-3xl md:text-4xl font-bold text-primary-900 mb-4">
                Pertanyaan yang <span class="gradient-text">Sering Diajukan</span>
            </h2>
            <p class="text-gray-600">
                Temukan jawaban atas pertanyaan umum tentang layanan hipnoterapi kami
            </p>
        </div>

        <!-- FAQ Accordion -->
        <div class="space-y-4" data-aos="fade-up">
            <?php foreach ($faqs as $index => $faq): ?>
                <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100">
                    <button
                        class="faq-question w-full flex items-center justify-between p-6 text-left hover:bg-gray-50 transition-colors">
                        <span class="font-semibold text-gray-800 pr-4"><?= e($faq->question) ?></span>
                        <i
                            class="fas fa-chevron-down faq-icon text-accent-600 transition-transform duration-300 flex-shrink-0"></i>
                    </button>
                    <div class="hidden px-6 pb-6">
                        <p class="text-gray-600 leading-relaxed"><?= e($faq->answer) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- SECTION: CTA Before Footer -->
<section class="py-16 bg-gradient-to-r from-primary-700 to-accent-600">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center" data-aos="fade-up">
        <h2 class="text-2xl md:text-3xl font-bold text-white mb-4">
            Siap Memulai Perjalanan Anda?
        </h2>
        <p class="text-primary-100 mb-8">
            Hubungi kami sekarang untuk konsultasi gratis atau buat reservasi langsung
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?= base_url('reservasi') ?>"
                class="inline-flex items-center justify-center px-8 py-4 bg-white text-primary-700 font-semibold rounded-full shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all">
                <i class="fas fa-calendar-check mr-2"></i>
                Buat Reservasi
            </a>
            <a href="https://wa.me/<?= ADMIN_WHATSAPP ?>?text=Assalamu'alaikum, saya ingin bertanya tentang layanan hipnoterapi"
                target="_blank"
                class="inline-flex items-center justify-center px-8 py-4 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-full shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all">
                <i class="fab fa-whatsapp text-xl mr-2"></i>
                Chat WhatsApp
            </a>
        </div>
    </div>
</section>