<!-- Booking Success Page -->
<section
    class="min-h-screen flex items-center py-20 bg-gradient-to-br from-sage-50 via-white to-gold-50 islamic-pattern">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl p-8 md:p-12 shadow-2xl text-center" data-aos="zoom-in">
            <!-- Success Icon -->
            <div
                class="w-24 h-24 bg-gradient-to-br from-green-400 to-green-500 rounded-full flex items-center justify-center mx-auto mb-8 shadow-lg">
                <i class="fas fa-check text-white text-4xl"></i>
            </div>

            <!-- Success Message -->
            <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                Reservasi Berhasil! 🎉
            </h1>

            <p class="text-gray-600 mb-8 text-lg">
                Terima kasih telah mempercayakan kami. Reservasi Anda telah dicatat dalam sistem kami.
            </p>

            <!-- Booking Details Card -->
            <div class="bg-sage-50 rounded-2xl p-6 mb-8 text-left">
                <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-receipt mr-2 text-sage-600"></i>
                    Detail Reservasi
                </h3>

                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-sage-200">
                        <span class="text-gray-600">Kode Booking</span>
                        <span class="font-mono font-bold text-sage-700 bg-sage-100 px-3 py-1 rounded-lg">
                            <?= e($booking['code']) ?>
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-sage-200">
                        <span class="text-gray-600">Terapis</span>
                        <span class="font-semibold text-gray-800"><?= e($booking['therapist']) ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-gray-600">Tanggal</span>
                        <span class="font-semibold text-gray-800"><?= e($booking['date']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Important Notice -->
            <div class="bg-gold-50 border border-gold-200 rounded-2xl p-6 mb-8 text-left">
                <div class="flex items-start space-x-3">
                    <i class="fas fa-info-circle text-gold-500 mt-1"></i>
                    <div>
                        <h4 class="font-semibold text-gray-800 mb-1">Langkah Selanjutnya</h4>
                        <p class="text-gray-600 text-sm">
                            Silakan klik tombol di bawah untuk menghubungi kami via WhatsApp dan konfirmasi jadwal Anda.
                            Tim kami akan segera merespons untuk memastikan ketersediaan jadwal.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="<?= e($booking['wa_link']) ?>" target="_blank"
                    class="inline-flex items-center justify-center px-8 py-4 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-full shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all">
                    <i class="fab fa-whatsapp text-xl mr-3"></i>
                    Konfirmasi via WhatsApp
                </a>

                <a href="<?= base_url() ?>"
                    class="inline-flex items-center justify-center px-8 py-4 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-full transition-all">
                    <i class="fas fa-home mr-2"></i>
                    Kembali ke Beranda
                </a>
            </div>

            <!-- Arabic Dua -->
            <div class="mt-10 pt-8 border-t border-gray-100">
                <p class="text-2xl font-arabic text-gold-400 mb-2">بَارَكَ اللَّهُ فِيكُمْ</p>
                <p class="text-gray-500 text-sm">Semoga Allah memberikan keberkahan kepada Anda</p>
            </div>
        </div>

        <!-- Additional Help -->
        <div class="mt-8 text-center">
            <p class="text-gray-500 text-sm">
                Butuh bantuan? Hubungi kami di
                <a href="https://wa.me/<?= ADMIN_WHATSAPP ?>" class="text-sage-600 hover:text-sage-700 font-medium">
                    WhatsApp
                </a>
                atau email ke
                <a href="mailto:<?= ADMIN_EMAIL ?>" class="text-sage-600 hover:text-sage-700 font-medium">
                    <?= ADMIN_EMAIL ?>
                </a>
            </p>
        </div>
    </div>
</section>