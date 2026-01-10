<!-- Layanan Page -->
<section class="pt-32 pb-16 bg-gradient-to-br from-primary-50 via-cream-50 to-lavender-50 islamic-pattern">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto" data-aos="fade-up">
            <span
                class="inline-block px-4 py-2 bg-primary-100 text-primary-800 rounded-full text-sm font-semibold mb-4">
                <i class="fas fa-hand-holding-heart mr-2"></i>Layanan Kami
            </span>
            <h1 class="text-4xl md:text-5xl font-bold text-primary-900 mb-6">
                Layanan <span class="gradient-text">Hipnoterapi</span>
            </h1>
            <p class="text-lg text-gray-600">
                Berbagai layanan hipnoterapi dan konseling untuk membantu Anda mengatasi berbagai masalah psikologis dan
                spiritual.
            </p>
        </div>
    </div>
</section>

<!-- Services List -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- All Services Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($services as $index => $service): ?>
                <div class="group hover-lift bg-white rounded-2xl p-8 shadow-lg border border-gray-100" data-aos="fade-up"
                    data-aos-delay="<?= ($index % 3) * 100 ?>">
                    <!-- Icon -->
                    <div
                        class="w-16 h-16 bg-gradient-to-br from-primary-100 to-accent-100 rounded-2xl flex items-center justify-center mb-6 group-hover:from-primary-600 group-hover:to-accent-600 transition-all">
                        <i
                            class="fas <?= e($service->icon) ?> text-2xl text-primary-700 group-hover:text-white transition-colors"></i>
                    </div>

                    <!-- Content -->
                    <h3 class="text-xl font-bold text-primary-900 mb-3"><?= e($service->name) ?></h3>
                    <p class="text-gray-600 mb-6 leading-relaxed"><?= e($service->description) ?></p>

                    <!-- Meta -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                        <div class="flex items-center space-x-2">
                            <span class="text-xs font-medium px-3 py-1 bg-primary-50 text-primary-700 rounded-full">
                                <?= e($service->target_audience) ?>
                            </span>
                            <span class="text-xs text-gray-500">
                                <i class="fas fa-clock mr-1"></i><?= e($service->duration) ?>
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-xl font-bold text-primary-800"><?= format_rupiah($service->price) ?></span>
                        <a href="<?= base_url('reservasi') ?>"
                            class="text-sm text-accent-600 hover:text-accent-700 font-semibold">
                            Reservasi <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Online Hypnotherapy Service -->
<section class="py-20 bg-primary-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div data-aos="fade-right">
                <div class="relative">
                    <img src="<?= base_url('public/images/therapist-1.jpg') ?>" alt="Bunda Dewi Irani"
                        class="mx-auto relative z-10 w-3/4 md:w-2/3 lg:w-full drop-shadow-2xl rounded-2xl">
                    <!-- Decorative Circle behind image if needed -->
                    <div
                        class="absolute top-10 left-10 w-64 h-64 bg-accent-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob">
                    </div>
                </div>
            </div>

            <div data-aos="fade-left">
                <span
                    class="inline-block px-4 py-2 bg-white text-primary-800 rounded-full text-sm font-semibold mb-4 shadow-sm">
                    <i class="fas fa-notes-medical mr-2"></i>Layanan Klinis
                </span>
                <h2 class="text-3xl font-bold text-primary-900 mb-2">Layanan Hipnoterapi</h2>
                <p class="text-xl text-accent-700 font-medium mb-6">Oleh Tim Terapis Profesional Albashiro</p>

                <div class="bg-white rounded-3xl p-8 shadow-xl border border-primary-100">
                    <ul class="grid sm:grid-cols-2 gap-x-4 gap-y-3">
                        <?php
                        $onlineServices = [
                            'Stress',
                            'Depresi',
                            'Trauma',
                            'Kebiasaan Buruk',
                            'LGBT (Lesbian, Gay, Biseksual & Transgender)',
                            'Menaikkan & Menurunkan Berat Badan',
                            'Menurunkan Skala Nyeri',
                            'Meningkatkan Imunitas',
                            'Menaikkan Nafsu Makan',
                            'Berhenti Merokok',
                            'Phobia (Ketakutan Berlebihan)',
                            'Kebiasaan/Perilaku Berulang',
                            'Gangguan Emosi',
                            'Baby Blues',
                            'Mengatasi Gangguan Tidur',
                            'Meningkatkan Motivasi Kerja',
                            'Menghancurkan Mental Blok',
                            'Membantu Mengatasi Masalah Seks',
                            'Gangguan Stress Pasca Trauma',
                            'Asam Lambung & Gerd',
                            'Mengatasi Ketergantungan Obat-Obatan Terlarang',
                            'Dan Masih Banyak Lagi'
                        ];
                        foreach ($onlineServices as $item): ?>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-3 flex-shrink-0"></i>
                                <span class="text-gray-700"><?= $item ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Child Hypnotherapy Service -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16" data-aos="fade-up">
            <span
                class="inline-block px-4 py-2 bg-lavender-100 text-lavender-800 rounded-full text-sm font-semibold mb-4">
                <i class="fas fa-child mr-2"></i>Hypno Kids
            </span>
            <h2 class="text-3xl md:text-4xl font-bold text-primary-900 mb-4">Pelayanan Hipnotherapy Anak</h2>
            <p class="text-xl text-gray-600">Perubahan Positif dengan Kekuatan Imajinasi</p>
        </div>

        <div class="grid lg:grid-cols-2 gap-16 items-center">
            <div class="order-2 lg:order-1" data-aos="fade-right">
                <div
                    class="bg-cream-50 rounded-3xl p-8 md:p-10 border border-gold-100 shadow-lg relative overflow-hidden">
                    <!-- Decor -->
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gold-200 rounded-bl-full opacity-20"></div>

                    <p class="text-lg text-gray-700 leading-relaxed mb-8 italic">
                        "Hypnoterapi anak menekankan kekuatan imajinasi anak untuk dapatnya memberikan sebuah perubahan
                        positif terhadap anak.
                        Saat melakukan sesi terapi Hypnoterapi mampu menciptakan cerita dan gambaran positif pada
                        pikiran bawah sadar anak
                        sehingga sekaligus dapat diterimanya sugesti positif secara optimal."
                    </p>

                    <h3 class="font-bold text-primary-900 text-xl mb-6 flex items-center">
                        <i class="fas fa-star text-gold-500 mr-2"></i>
                        Hypno Kids Mampu Mengatasi:
                    </h3>

                    <ul class="space-y-3">
                        <?php
                        $childServices1 = [
                            'Mengatasi Hiperaktif',
                            'Mengatasi Fobia',
                            'Mengatasi Insomnia',
                            'Kurang Fokus Belajar',
                            'Manajemen Kemarahan/Tantrum',
                            'Mengatasi Anak Ngompol',
                            'Mengatasi Kecemasan dan Stres',
                            'Mengatasi Trauma dan Bingung Puting',
                            'Mengatasi Anak Kecanduan Dot, Gadget dan Merokok',
                            'Meningkatkan Rasa Percaya Diri'
                        ];
                        foreach ($childServices1 as $item): ?>
                            <li class="flex items-center">
                                <span
                                    class="w-6 h-6 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center text-xs mr-3 flex-shrink-0">
                                    <i class="fas fa-check"></i>
                                </span>
                                <span class="text-gray-700"><?= $item ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div class="order-1 lg:order-2 space-y-8" data-aos="fade-left">
                <div class="bg-lavender-50 rounded-3xl p-8 shadow-inner">
                    <ul class="space-y-3">
                        <?php
                        $childServices2 = [
                            'Meningkatkan Prestasi Anak',
                            'Mengatasi Alergi, Asma',
                            'Mengatasi Anak yang Kurang Disiplin',
                            'Mengatasi Anak Gagap dan Sulit Bicara',
                            'Mengatasi Kebiasaan Menggigit Kuku, Menghisap Jempol',
                            'Mengatasi Anak yang Sulit Bergaul',
                            'Mengurangi Rasa Sakit pada Saat Dilakukan Tindakan Medis (Imunisasi, Tindik, Infus, dll)',
                            'Mempersiapkan Anak Mengikuti Lomba Kejuaraan',
                            'Berbagai Fobia (Nasi, Buah, Ketinggian, Ketakutan Bersosialisasi)',
                            'Mengatasi Inner Child (Hypno Kids + Hypno Parent)'
                        ];
                        foreach ($childServices2 as $item): ?>
                            <li class="flex items-start">
                                <i class="fas fa-heart text-lavender-500 mt-1 mr-3 flex-shrink-0"></i>
                                <span class="text-gray-700"><?= $item ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div
                    class="bg-gradient-to-r from-primary-600 to-primary-800 rounded-2xl p-8 text-white text-center shadow-xl transform hover:-translate-y-1 transition-all duration-300">
                    <h3 class="text-2xl font-bold mb-2">Konsultasi Anak?</h3>
                    <p class="mb-6 opacity-90">Hubungi kami untuk jadwal konsultasi terbaik buah hati Anda</p>
                    <a href="https://wa.me/<?= ADMIN_WHATSAPP ?>?text=Halo, saya ingin konsultasi Hypno Kids"
                        class="inline-block px-8 py-3 bg-white text-primary-900 font-bold rounded-full hover:bg-gold-300 transition-colors">
                        Chat WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Service Flow -->
<section class="py-20 bg-primary-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16" data-aos="fade-up">
            <h2 class="text-3xl font-bold text-white mb-4">Alur Layanan</h2>
            <p class="text-primary-200">Langkah mudah untuk memulai terapi</p>
        </div>

        <div class="grid md:grid-cols-4 gap-8">
            <div class="text-center" data-aos="fade-up">
                <div
                    class="w-20 h-20 bg-white rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-xl relative">
                    <span
                        class="absolute -top-3 -right-3 w-8 h-8 bg-gold-400 rounded-full flex items-center justify-center text-primary-900 font-bold">1</span>
                    <i class="fas fa-calendar-plus text-3xl text-primary-700"></i>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">Buat Janji</h3>
                <p class="text-primary-300 text-sm">Isi form reservasi atau hubungi via WhatsApp</p>
            </div>

            <div class="text-center" data-aos="fade-up" data-aos-delay="100">
                <div
                    class="w-20 h-20 bg-white rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-xl relative">
                    <span
                        class="absolute -top-3 -right-3 w-8 h-8 bg-gold-400 rounded-full flex items-center justify-center text-primary-900 font-bold">2</span>
                    <i class="fas fa-comments text-3xl text-primary-700"></i>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">Konsultasi</h3>
                <p class="text-primary-300 text-sm">Ceritakan keluhan dan harapan Anda</p>
            </div>

            <div class="text-center" data-aos="fade-up" data-aos-delay="200">
                <div
                    class="w-20 h-20 bg-white rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-xl relative">
                    <span
                        class="absolute -top-3 -right-3 w-8 h-8 bg-gold-400 rounded-full flex items-center justify-center text-primary-900 font-bold">3</span>
                    <i class="fas fa-spa text-3xl text-primary-700"></i>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">Terapi</h3>
                <p class="text-primary-300 text-sm">Jalani sesi hipnoterapi dengan terapis</p>
            </div>

            <div class="text-center" data-aos="fade-up" data-aos-delay="300">
                <div
                    class="w-20 h-20 bg-white rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-xl relative">
                    <span
                        class="absolute -top-3 -right-3 w-8 h-8 bg-gold-400 rounded-full flex items-center justify-center text-primary-900 font-bold">4</span>
                    <i class="fas fa-clipboard-check text-3xl text-primary-700"></i>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">Evaluasi</h3>
                <p class="text-primary-300 text-sm">Follow up dan rekomendasi lanjutan</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-16 bg-gradient-to-r from-gold-100 to-lavender-100">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-2xl md:text-3xl font-bold text-primary-900 mb-4">Butuh Bantuan Memilih Layanan?</h2>
        <p class="text-gray-600 mb-8">Konsultasikan kebutuhan Anda dengan tim kami</p>
        <a href="https://wa.me/<?= ADMIN_WHATSAPP ?>?text=Halo, saya ingin konsultasi tentang layanan hipnoterapi"
            target="_blank"
            class="inline-flex items-center px-8 py-4 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-full shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all">
            <i class="fab fa-whatsapp text-xl mr-2"></i>
            Chat via WhatsApp
        </a>
    </div>
</section>