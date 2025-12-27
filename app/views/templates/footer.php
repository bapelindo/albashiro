</main>

<!-- Footer -->
<footer class="bg-primary-900 text-white pt-20 pb-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
            <!-- About Column -->
            <div class="lg:col-span-2">
                <div class="flex items-center space-x-3 mb-6">
                    <div
                        class="w-14 h-14 bg-gradient-to-br from-accent-600 to-accent-700 rounded-xl flex items-center justify-center">
                        <i class="fas fa-spa text-white text-2xl"></i>
                    </div>
                    <div>
                        <span class="text-2xl font-bold text-white"><?= SITE_NAME ?></span>
                        <span class="block text-sm text-gold-400 font-medium">Islamic Spiritual Hypnotherapy</span>
                    </div>
                </div>
                <p class="text-primary-200 leading-relaxed mb-6 max-w-md">
                    Layanan hipnoterapi profesional dengan pendekatan Islami. Kami membantu Anda menemukan kedamaian
                    jiwa dan mengatasi berbagai masalah psikologis sesuai dengan nilai-nilai syariat Islam.
                </p>
                <!-- Social Links -->
                <div class="flex space-x-4">
                    <a href="#"
                        class="w-11 h-11 bg-primary-800 hover:bg-accent-600 rounded-full flex items-center justify-center transition-colors">
                        <i class="fab fa-instagram text-lg"></i>
                    </a>
                    <a href="#"
                        class="w-11 h-11 bg-primary-800 hover:bg-accent-600 rounded-full flex items-center justify-center transition-colors">
                        <i class="fab fa-facebook-f text-lg"></i>
                    </a>
                    <a href="#"
                        class="w-11 h-11 bg-primary-800 hover:bg-accent-600 rounded-full flex items-center justify-center transition-colors">
                        <i class="fab fa-youtube text-lg"></i>
                    </a>
                    <a href="#"
                        class="w-11 h-11 bg-primary-800 hover:bg-accent-600 rounded-full flex items-center justify-center transition-colors">
                        <i class="fab fa-tiktok text-lg"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="text-lg font-semibold mb-6 text-white">Menu</h4>
                <ul class="space-y-3">
                    <li>
                        <a href="<?= base_url() ?>"
                            class="text-primary-300 hover:text-gold-400 transition-colors flex items-center">
                            <i class="fas fa-chevron-right text-xs mr-2 text-accent-500"></i>
                            Beranda
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('tentang') ?>"
                            class="text-primary-300 hover:text-gold-400 transition-colors flex items-center">
                            <i class="fas fa-chevron-right text-xs mr-2 text-accent-500"></i>
                            Tentang Kami
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('layanan') ?>"
                            class="text-primary-300 hover:text-gold-400 transition-colors flex items-center">
                            <i class="fas fa-chevron-right text-xs mr-2 text-accent-500"></i>
                            Layanan
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('terapis') ?>"
                            class="text-primary-300 hover:text-gold-400 transition-colors flex items-center">
                            <i class="fas fa-chevron-right text-xs mr-2 text-accent-500"></i>
                            Terapis
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('blog') ?>"
                            class="text-primary-300 hover:text-gold-400 transition-colors flex items-center">
                            <i class="fas fa-chevron-right text-xs mr-2 text-accent-500"></i>
                            Blog
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div>
                <h4 class="text-lg font-semibold mb-6 text-white">Kontak</h4>
                <ul class="space-y-4">
                    <li class="flex items-start space-x-3">
                        <div
                            class="w-10 h-10 bg-accent-600/20 rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                            <i class="fas fa-map-marker-alt text-accent-400"></i>
                        </div>
                        <div>
                            <p class="text-primary-300 text-sm leading-relaxed">
                                Jl. Imam Bonjol No. 123<br>
                                Jakarta Pusat, DKI Jakarta
                            </p>
                        </div>
                    </li>
                    <li class="flex items-center space-x-3">
                        <div
                            class="w-10 h-10 bg-accent-600/20 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fab fa-whatsapp text-accent-400"></i>
                        </div>
                        <a href="https://wa.me/<?= ADMIN_WHATSAPP ?>"
                            class="text-primary-300 hover:text-gold-400 transition-colors">
                            +62 812-3456-7890
                        </a>
                    </li>
                    <li class="flex items-center space-x-3">
                        <div
                            class="w-10 h-10 bg-accent-600/20 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-envelope text-accent-400"></i>
                        </div>
                        <a href="mailto:<?= ADMIN_EMAIL ?>"
                            class="text-primary-300 hover:text-gold-400 transition-colors">
                            info@albashiro.com
                        </a>
                    </li>
                    <li class="flex items-center space-x-3">
                        <div
                            class="w-10 h-10 bg-accent-600/20 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-clock text-accent-400"></i>
                        </div>
                        <div class="text-primary-300 text-sm">
                            Senin - Sabtu: 09:00 - 17:00<br>
                            Minggu: Tutup
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="border-t border-primary-800 pt-8">
            <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                <p class="text-primary-400 text-sm">
                    &copy; <?= date('Y') ?> <span class="text-accent-400"><?= SITE_NAME ?></span>. All rights reserved.
                </p>
                <p class="text-primary-400 text-sm flex items-center">
                    <span>Made with</span>
                    <i class="fas fa-heart text-red-400 mx-1 text-xs"></i>
                    <span>by Bapel</span>
                </p>
            </div>
        </div>
    </div>
</footer>


<!-- AI Chatbot Widget -->
<!-- Backdrop Overlay -->
<div id="chat-backdrop" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-40 transition-opacity duration-300">
</div>

<div id="ai-chat-widget" class="fixed bottom-6 left-6 z-50">
    <!-- Chat Window -->
    <div id="ai-chat-window"
        class="hidden fixed inset-0 m-auto w-[95vw] sm:w-[90vw] md:w-[600px] h-[90vh] sm:h-[95vh] bg-white rounded-2xl sm:rounded-3xl shadow-2xl overflow-hidden border-2 border-emerald-200/30 flex flex-col backdrop-blur-sm animate-slide-up z-50">
        <!-- Header with Islamic Design -->
        <div
            class="bg-gradient-to-r from-emerald-700 via-teal-700 to-emerald-800 px-4 sm:px-6 py-3 sm:py-3 flex items-center justify-between relative overflow-hidden">
            <!-- Islamic Pattern Background -->
            <div class="absolute inset-0 opacity-10">
                <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                    <pattern id="islamic-pattern" x="0" y="0" width="40" height="40" patternUnits="userSpaceOnUse">
                        <circle cx="20" cy="20" r="2" fill="white" />
                        <circle cx="0" cy="0" r="2" fill="white" />
                        <circle cx="40" cy="0" r="2" fill="white" />
                        <circle cx="0" cy="40" r="2" fill="white" />
                        <circle cx="40" cy="40" r="2" fill="white" />
                    </pattern>
                    <rect width="100%" height="100%" fill="url(#islamic-pattern)" />
                </svg>
            </div>

            <div class="flex items-center space-x-2 sm:space-x-3 relative z-10">
                <!-- Islamic Crescent Moon Icon -->
                <div
                    class="w-9 h-9 sm:w-10 sm:h-10 bg-gradient-to-br from-amber-400 to-yellow-500 backdrop-blur-sm rounded-full flex items-center justify-center ring-2 ring-amber-300/50 shadow-lg">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-emerald-900" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M21.64 13a1 1 0 0 0-1.05-.14 8.05 8.05 0 0 1-3.37.73 8.15 8.15 0 0 1-8.14-8.1 8.59 8.59 0 0 1 .25-2A1 1 0 0 0 8 2.36a10.14 10.14 0 1 0 14 11.69 1 1 0 0 0-.36-1.05zm-9.5 6.69A8.14 8.14 0 0 1 7.08 5.22v.27a10.15 10.15 0 0 0 10.14 10.14 9.79 9.79 0 0 0 2.1-.22 8.11 8.11 0 0 1-7.18 4.32z" />
                    </svg>
                </div>
                <div>
                    <!-- Bismillah -->
                    <div class="text-amber-300 font-arabic text-sm sm:text-base mb-0.5"
                        style="font-family: 'Amiri', 'Traditional Arabic', serif; direction: rtl;">
                        بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ
                    </div>
                    <h3 class="text-white font-semibold text-xs sm:text-sm">Asisten AI Albashiro</h3>
                    <div class="flex items-center space-x-1 sm:space-x-1.5">
                        <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></span>
                        <p class="text-white/70 text-[10px] sm:text-xs">Siap Melayani 24/7</p>
                    </div>
                </div>
            </div>
            <button id="close-chat" class="text-white/60 hover:text-white transition-colors p-1 relative z-10">
                <i class="fas fa-times text-base sm:text-lg"></i>
            </button>
        </div>

        <!-- Messages Container -->
        <div id="chat-messages" class="flex-1 overflow-y-auto p-3 sm:p-4 bg-gray-50 space-y-2 sm:space-y-3 min-h-0">
            <!-- Messages will be appended here -->
        </div>

        <!-- Typing Indicator with Tasbih Animation -->
        <div id="typing-indicator" class="hidden px-3 sm:px-4 py-3 bg-gray-50">
            <div class="flex items-center space-x-3">
                <!-- Tasbih Beads Animation -->
                <div class="relative w-12 h-12">
                    <!-- Rotating Tasbih Circle -->
                    <div class="absolute inset-0 animate-spin-slow">
                        <!-- Tasbih Beads (33 beads in circle) -->
                        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-2 h-2 bg-emerald-600 rounded-full"></div>
                        <div class="absolute top-1 right-2 w-2 h-2 bg-emerald-500 rounded-full"></div>
                        <div class="absolute top-3 right-1 w-2 h-2 bg-emerald-400 rounded-full"></div>
                        <div class="absolute top-5 right-0 w-2 h-2 bg-teal-600 rounded-full"></div>
                        <div class="absolute bottom-3 right-1 w-2 h-2 bg-teal-500 rounded-full"></div>
                        <div class="absolute bottom-1 right-2 w-2 h-2 bg-teal-400 rounded-full"></div>
                        <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-2 h-2 bg-emerald-600 rounded-full">
                        </div>
                        <div class="absolute bottom-1 left-2 w-2 h-2 bg-emerald-500 rounded-full"></div>
                        <div class="absolute bottom-3 left-1 w-2 h-2 bg-emerald-400 rounded-full"></div>
                        <div class="absolute top-5 left-0 w-2 h-2 bg-teal-600 rounded-full"></div>
                        <div class="absolute top-3 left-1 w-2 h-2 bg-teal-500 rounded-full"></div>
                        <div class="absolute top-1 left-2 w-2 h-2 bg-teal-400 rounded-full"></div>
                    </div>
                    <!-- Center Glow -->
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-3 h-3 bg-amber-400 rounded-full animate-pulse-slow shadow-lg shadow-amber-400/50">
                        </div>
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-emerald-700 text-sm font-medium">بإذن الله</p>
                    <p id="ai-thinking-text" class="text-emerald-600/70 text-xs">Sedang memproses dengan penuh
                        perhatian...</p>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="p-3 sm:p-4 bg-white border-t border-gray-200">
            <form id="chat-form" class="flex space-x-2">
                <input type="text" id="chat-input" placeholder="Ketik pesan Anda..."
                    class="flex-1 px-3 sm:px-4 py-2 sm:py-2.5 border border-emerald-200 rounded-full focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-xs sm:text-sm"
                    autocomplete="off">
                <button type="submit"
                    class="w-10 h-10 sm:w-11 sm:h-11 bg-gradient-to-br from-emerald-700 to-teal-700 hover:from-emerald-600 hover:to-teal-600 text-white rounded-full flex items-center justify-center transition-all shadow-md hover:shadow-lg flex-shrink-0">
                    <i class="fas fa-paper-plane text-xs sm:text-sm"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Chat Toggle Button with Islamic Design -->
    <button id="ai-chat-toggle"
        class="w-14 h-14 sm:w-16 sm:h-16 bg-gradient-to-br from-emerald-700 via-teal-700 to-emerald-800 hover:from-emerald-600 hover:via-teal-600 hover:to-emerald-700 rounded-full flex items-center justify-center shadow-2xl hover:shadow-emerald-500/30 transition-all group relative ring-2 ring-amber-400/20">
        <!-- Crescent Moon Icon -->
        <svg class="w-7 h-7 sm:w-8 sm:h-8 text-amber-300 group-hover:scale-110 transition-transform" fill="currentColor"
            viewBox="0 0 24 24">
            <path
                d="M21.64 13a1 1 0 0 0-1.05-.14 8.05 8.05 0 0 1-3.37.73 8.15 8.15 0 0 1-8.14-8.1 8.59 8.59 0 0 1 .25-2A1 1 0 0 0 8 2.36a10.14 10.14 0 1 0 14 11.69 1 1 0 0 0-.36-1.05zm-9.5 6.69A8.14 8.14 0 0 1 7.08 5.22v.27a10.15 10.15 0 0 0 10.14 10.14 9.79 9.79 0 0 0 2.1-.22 8.11 8.11 0 0 1-7.18 4.32z" />
        </svg>
        <span
            class="absolute -top-1 -right-1 sm:-top-2 sm:-right-2 w-5 h-5 bg-emerald-500 rounded-full flex items-center justify-center ring-2 ring-white">
            <span class="w-3 h-3 bg-emerald-400 rounded-full animate-ping absolute"></span>
            <span class="w-2 h-2 bg-white rounded-full relative"></span>
        </span>
    </button>
</div>

<!-- Floating WhatsApp Button -->
<a href="https://wa.me/<?= ADMIN_WHATSAPP ?>?text=Assalamu'alaikum, saya ingin konsultasi tentang hipnoterapi"
    target="_blank"
    class="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 z-40 w-14 h-14 sm:w-16 sm:h-16 bg-green-500 hover:bg-green-600 rounded-full flex items-center justify-center shadow-2xl hover:shadow-green-500/30 transition-all group">
    <i class="fab fa-whatsapp text-white text-2xl sm:text-3xl group-hover:scale-110 transition-transform"></i>
    <span
        class="absolute -top-1 -right-1 sm:-top-2 sm:-right-2 w-5 h-5 bg-red-500 rounded-full flex items-center justify-center animate-pulse">
        <span class="text-white text-xs font-bold">1</span>
    </span>
</a>

<!-- Swiper JS - Local -->
<script src="<?= base_url('public/js/swiper-bundle.min.js') ?>"></script>

<!-- AOS Animation - Local -->
<script src="<?= base_url('public/js/aos.js') ?>"></script>
<script>
    AOS.init({
        duration: 800,
        easing: 'ease-out-cubic',
        once: true,
        offset: 50
    });
</script>



<!-- Custom Scripts - Local -->
<script src="<?= base_url('public/js/main.js') ?>"></script>

<!-- Calming Animations Styles -->
<style>
    /* Calming Animations for Islamic Chatbot */

    /* Breathing Animation - Subtle pulse like guided breathing */
    @keyframes breathe {

        0%,
        100% {
            transform: scale(1);
            opacity: 1;
        }

        50% {
            transform: scale(1.03);
            opacity: 0.95;
        }
    }

    /* Slow Spin for Tasbih Beads */
    @keyframes spin-slow {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    /* Slow Pulse for Center Glow */
    @keyframes pulse-slow {

        0%,
        100% {
            opacity: 1;
            transform: scale(1);
        }

        50% {
            opacity: 0.6;
            transform: scale(1.1);
        }
    }

    /* Gentle Fade In with Cubic Bezier */
    @keyframes gentle-fade-in {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Apply Animations */
    .animate-spin-slow {
        animation: spin-slow 8s linear infinite;
    }

    .animate-pulse-slow {
        animation: pulse-slow 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    .animate-breathe {
        animation: breathe 4s cubic-bezier(0.4, 0, 0.2, 1) infinite;
    }

    /* Smooth transitions with calming easing */
    #chat-messages>div {
        animation: gentle-fade-in 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Smooth slide up for chat window - FIXED POSITIONING */
    @keyframes slide-up {
        from {
            opacity: 0;
            transform: scale(0.95);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .animate-slide-up {
        animation: slide-up 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
</style>

</body>

</html>