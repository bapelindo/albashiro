<!-- Terapis Page -->
<section class="pt-32 pb-16 bg-gradient-to-br from-primary-50 via-cream-50 to-lavender-50 islamic-pattern">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto" data-aos="fade-up">
            <span
                class="inline-block px-4 py-2 bg-primary-100 text-primary-800 rounded-full text-sm font-semibold mb-4">
                <i class="fas fa-user-md mr-2"></i>Tim Kami
            </span>
            <h1 class="text-4xl md:text-5xl font-bold text-primary-900 mb-6">
                Terapis <span class="gradient-text">Profesional</span>
            </h1>
            <p class="text-lg text-gray-600">
                Dipandu oleh para ahli berpengalaman dan bersertifikasi di bidang hipnoterapi Islami.
            </p>
        </div>
    </div>
</section>

<!-- Therapists Grid -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-3 gap-8">
            <?php foreach ($therapists as $index => $therapist): ?>
                <div class="group" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                    <div class="bg-white rounded-3xl overflow-hidden shadow-xl border border-gray-100 hover-lift">
                        <!-- Photo -->
                        <div class="relative h-80 overflow-hidden">
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
                            <!-- Badge -->
                            <div class="absolute top-4 right-4">
                                <span class="px-4 py-2 bg-gold-400 text-primary-900 text-sm font-bold rounded-full shadow">
                                    <?= e($therapist->experience_years) ?> Tahun
                                </span>
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="p-8">
                            <h3 class="text-2xl font-bold text-primary-900 mb-1"><?= e($therapist->name) ?></h3>
                            <p class="text-accent-600 font-medium mb-4"><?= e($therapist->title) ?></p>

                            <div class="mb-4">
                                <span
                                    class="inline-block px-3 py-1 bg-primary-50 text-primary-700 text-sm font-medium rounded-full">
                                    <?= e($therapist->specialty) ?>
                                </span>
                            </div>

                            <p class="text-gray-600 text-sm mb-6 leading-relaxed"><?= e($therapist->bio) ?></p>

                            <!-- Credentials -->
                            <div class="pt-4 border-t border-gray-100">
                                <p class="text-xs text-gray-500 mb-3 font-semibold uppercase tracking-wide">Kredensial</p>
                                <p class="text-sm text-gray-600"><?= e($therapist->credentials) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Why Our Therapists -->
<section class="py-20 bg-cream-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12" data-aos="fade-up">
            <h2 class="text-3xl font-bold text-primary-900 mb-4">Mengapa Memilih Terapis Kami?</h2>
        </div>

        <div class="grid md:grid-cols-4 gap-8">
            <div class="text-center p-6" data-aos="fade-up">
                <div class="w-16 h-16 bg-primary-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-award text-2xl text-primary-700"></i>
                </div>
                <h3 class="font-semibold text-primary-900 mb-2">Bersertifikasi</h3>
                <p class="text-sm text-gray-600">Semua terapis memiliki sertifikasi resmi dari lembaga yang diakui</p>
            </div>

            <div class="text-center p-6" data-aos="fade-up" data-aos-delay="100">
                <div class="w-16 h-16 bg-accent-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-book-quran text-2xl text-accent-700"></i>
                </div>
                <h3 class="font-semibold text-primary-900 mb-2">Islami</h3>
                <p class="text-sm text-gray-600">Memahami dan menerapkan nilai-nilai Islam dalam terapi</p>
            </div>

            <div class="text-center p-6" data-aos="fade-up" data-aos-delay="200">
                <div class="w-16 h-16 bg-lavender-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-heart text-2xl text-lavender-700"></i>
                </div>
                <h3 class="font-semibold text-primary-900 mb-2">Empatik</h3>
                <p class="text-sm text-gray-600">Memberikan ruang aman dan penuh empati untuk klien</p>
            </div>

            <div class="text-center p-6" data-aos="fade-up" data-aos-delay="300">
                <div class="w-16 h-16 bg-gold-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-user-shield text-2xl text-gold-600"></i>
                </div>
                <h3 class="font-semibold text-primary-900 mb-2">Rahasia</h3>
                <p class="text-sm text-gray-600">Menjaga kerahasiaan klien dengan penuh tanggung jawab</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-16 bg-gradient-to-r from-accent-600 to-primary-800">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-2xl md:text-3xl font-bold text-white mb-4">Siap Berkonsultasi?</h2>
        <p class="text-primary-100 mb-8">Pilih terapis dan buat janji sekarang</p>
        <a href="<?= base_url('reservasi') ?>"
            class="inline-flex items-center px-8 py-4 bg-white text-primary-800 font-semibold rounded-full shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all">
            <i class="fas fa-calendar-check mr-2"></i>
            Buat Reservasi
        </a>
    </div>
</section>