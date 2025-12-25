<!-- Reservasi Page -->
<section class="pt-32 pb-16 bg-gradient-to-br from-primary-50 via-cream-50 to-lavender-50 islamic-pattern">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto" data-aos="fade-up">
            <span
                class="inline-block px-4 py-2 bg-primary-100 text-primary-800 rounded-full text-sm font-semibold mb-4">
                <i class="fas fa-calendar-alt mr-2"></i>Reservasi
            </span>
            <h1 class="text-4xl md:text-5xl font-bold text-primary-900 mb-6">
                Buat <span class="gradient-text">Janji Temu</span>
            </h1>
            <p class="text-lg text-gray-600">
                Isi form di bawah untuk membuat reservasi. Tim kami akan menghubungi Anda untuk konfirmasi.
            </p>
        </div>
    </div>
</section>

<!-- Flash Messages -->
<?php if (isset($flash) && $flash): ?>
    <div class="max-w-3xl mx-auto px-4 -mt-8 mb-8">
        <div
            class="<?= $flash['type'] === 'error' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-green-50 border-green-200 text-green-700' ?> px-6 py-4 rounded-xl border flex items-start space-x-3">
            <i class="fas <?= $flash['type'] === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle' ?> mt-0.5"></i>
            <p class="text-sm"><?= $flash['message'] ?></p>
        </div>
    </div>
<?php endif; ?>

<!-- Reservation Form -->
<section class="py-12 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl p-8 md:p-12 shadow-2xl border border-gray-100" data-aos="fade-up">
            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-gray-700">Progress Pengisian</span>
                    <span id="progress-text" class="text-sm font-bold text-primary-600">0%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                    <div id="progress-bar"
                        class="bg-gradient-to-r from-primary-600 to-accent-600 h-3 rounded-full transition-all duration-500 ease-out"
                        style="width: 0%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    <span id="progress-hint">Mulai dengan memilih terapis</span>
                </p>
            </div>

            <form action="<?= base_url('home/book') ?>" method="POST" id="reservation-form">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <div class="space-y-6">
                    <!-- Therapist Select -->
                    <div class="relative">
                        <label class="block text-sm font-semibold text-primary-900 mb-2">
                            <i class="fas fa-user-md mr-2 text-accent-600"></i>Pilih Terapis <span
                                class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="therapist_id" required data-validate
                                class="w-full px-4 py-4 pr-12 border-2 border-gray-200 rounded-xl focus:border-accent-500 focus:ring-0 transition-all bg-cream-50">
                                <option value="">-- Pilih Terapis --</option>
                                <?php foreach ($therapists as $therapist): ?>
                                    <option value="<?= $therapist->id ?>"><?= e($therapist->name) ?> -
                                        <?= e($therapist->specialty) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i
                                class="fas fa-check-circle absolute right-4 top-1/2 -translate-y-1/2 text-green-500 text-xl opacity-0 transition-opacity field-check"></i>
                        </div>
                    </div>

                    <!-- Service Select -->
                    <div>
                        <label class="block text-sm font-semibold text-primary-900 mb-2">
                            <i class="fas fa-hand-holding-heart mr-2 text-accent-600"></i>Pilih Layanan
                        </label>
                        <select name="service_id"
                            class="w-full px-4 py-4 border-2 border-gray-200 rounded-xl focus:border-accent-500 focus:ring-0 transition-colors bg-cream-50">
                            <option value="">-- Pilih Layanan (Opsional) --</option>
                            <?php foreach ($services as $service): ?>
                                <option value="<?= $service->id ?>"><?= e($service->name) ?> -
                                    <?= format_rupiah($service->price) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Two Columns -->
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-primary-900 mb-2">
                                <i class="fas fa-user mr-2 text-accent-600"></i>Nama Lengkap <span
                                    class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" name="client_name" required minlength="3" data-validate
                                    placeholder="Masukkan nama lengkap"
                                    class="w-full px-4 py-4 pr-12 border-2 border-gray-200 rounded-xl focus:border-accent-500 focus:ring-0 transition-all bg-cream-50">
                                <i
                                    class="fas fa-check-circle absolute right-4 top-1/2 -translate-y-1/2 text-green-500 text-xl opacity-0 transition-opacity field-check"></i>
                            </div>
                            <p class="text-xs text-red-500 mt-1 hidden field-error">Nama minimal 3 karakter</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-primary-900 mb-2">
                                <i class="fab fa-whatsapp mr-2 text-accent-600"></i>Nomor WhatsApp <span
                                    class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="tel" name="wa_number" required placeholder="081234567890" data-validate
                                    pattern="[0-9]{10,15}"
                                    class="w-full px-4 py-4 pr-12 border-2 border-gray-200 rounded-xl focus:border-accent-500 focus:ring-0 transition-all bg-cream-50">
                                <i
                                    class="fas fa-check-circle absolute right-4 top-1/2 -translate-y-1/2 text-green-500 text-xl opacity-0 transition-opacity field-check"></i>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Format: 081234567890 (akan otomatis diformat)</p>
                            <p class="text-xs text-red-500 mt-1 hidden field-error">Nomor WhatsApp tidak valid</p>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-primary-900 mb-2">
                                <i class="fas fa-envelope mr-2 text-accent-600"></i>Email
                            </label>
                            <div class="relative">
                                <input type="email" name="email" placeholder="email@example.com" data-validate
                                    class="w-full px-4 py-4 pr-12 border-2 border-gray-200 rounded-xl focus:border-accent-500 focus:ring-0 transition-all bg-cream-50">
                                <i
                                    class="fas fa-check-circle absolute right-4 top-1/2 -translate-y-1/2 text-green-500 text-xl opacity-0 transition-opacity field-check"></i>
                            </div>
                            <p class="text-xs text-red-500 mt-1 hidden field-error">Format email tidak valid</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-primary-900 mb-2">
                                <i class="fas fa-calendar mr-2 text-accent-600"></i>Tanggal <span
                                    class="text-red-500">*</span>
                            </label>
                            <input type="date" name="appointment_date" required min="<?= date('Y-m-d') ?>"
                                class="w-full px-4 py-4 border-2 border-gray-200 rounded-xl focus:border-accent-500 focus:ring-0 transition-colors bg-cream-50">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-primary-900 mb-2">
                            <i class="fas fa-clock mr-2 text-accent-600"></i>Waktu <span class="text-red-500">*</span>
                        </label>
                        <select name="appointment_time" id="appointment_time" required
                            class="w-full px-4 py-4 border-2 border-gray-200 rounded-xl focus:border-accent-500 focus:ring-0 transition-colors bg-cream-50">
                            <option value="">-- Pilih Tanggal dan Terapis Terlebih Dahulu --</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Waktu yang tersedia akan muncul setelah memilih terapis
                            dan tanggal</p>
                    </div>

                    <!-- Problem Description -->
                    <div>
                        <label class="block text-sm font-semibold text-primary-900 mb-2">
                            <i class="fas fa-comment-alt mr-2 text-accent-600"></i>Ceritakan Keluhan Anda <span
                                class="text-red-500">*</span>
                        </label>
                        <textarea name="problem_description" required minlength="10" rows="4"
                            placeholder="Jelaskan secara singkat masalah atau keluhan yang ingin Anda konsultasikan..."
                            class="w-full px-4 py-4 border-2 border-gray-200 rounded-xl focus:border-accent-500 focus:ring-0 transition-colors bg-cream-50 resize-none"></textarea>
                    </div>
                </div>

                <!-- Privacy Notice -->
                <div class="mt-6 p-4 bg-primary-50 rounded-xl flex items-start space-x-3">
                    <i class="fas fa-shield-alt text-primary-600 mt-0.5"></i>
                    <p class="text-sm text-gray-600">
                        Data Anda dijaga kerahasiaannya dan hanya digunakan untuk keperluan konsultasi.
                    </p>
                </div>

                <!-- Submit -->
                <div class="mt-8">
                    <button type="submit"
                        class="w-full py-4 bg-gradient-to-r from-primary-700 to-accent-600 text-white text-lg font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all flex items-center justify-center">
                        <i class="fas fa-paper-plane mr-3"></i>
                        Kirim Reservasi
                    </button>
                </div>
            </form>
        </div>

        <!-- Alternative Contact -->
        <div class="mt-8 text-center">
            <p class="text-gray-500 mb-4">Atau hubungi langsung via WhatsApp:</p>
            <a href="https://wa.me/<?= ADMIN_WHATSAPP ?>?text=Halo, saya ingin membuat reservasi hipnoterapi"
                target="_blank"
                class="inline-flex items-center px-6 py-3 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-full shadow-lg transition-all">
                <i class="fab fa-whatsapp text-xl mr-2"></i>
                Chat WhatsApp
            </a>
        </div>
    </div>
</section>

<!-- Reservasi Page Styles - Local -->
<link rel="stylesheet" href="<?= base_url('public/css/reservasi.css') ?>">

<!-- Reservasi Page JavaScript - Local -->
<script src="<?= base_url('public/js/reservasi.js') ?>"></script>