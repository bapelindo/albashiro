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
        class="hidden fixed inset-0 m-auto w-[95vw] sm:w-[90vw] md:w-[600px] h-[90vh] sm:h-[95vh] bg-white rounded-2xl sm:rounded-3xl shadow-2xl overflow-hidden border-2 border-primary-200/30 flex flex-col backdrop-blur-sm animate-slide-up z-50">
        <!-- Header -->
        <div
            class="bg-gradient-to-r from-primary-800 to-primary-900 px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between">
            <div class="flex items-center space-x-2 sm:space-x-3">
                <div
                    class="w-9 h-9 sm:w-10 sm:h-10 bg-white/10 backdrop-blur-sm rounded-full flex items-center justify-center ring-2 ring-white/20">
                    <i class="fas fa-robot text-white text-base sm:text-lg"></i>
                </div>
                <div>
                    <h3 class="text-white font-semibold text-xs sm:text-sm">Asisten AI Albashiro</h3>
                    <div class="flex items-center space-x-1 sm:space-x-1.5">
                        <span class="w-1.5 h-1.5 bg-green-400 rounded-full"></span>
                        <p class="text-white/70 text-[10px] sm:text-xs">Online 24/7</p>
                    </div>
                </div>
            </div>
            <button id="close-chat" class="text-white/60 hover:text-white transition-colors p-1">
                <i class="fas fa-times text-base sm:text-lg"></i>
            </button>
        </div>

        <!-- Messages Container -->
        <div id="chat-messages" class="flex-1 overflow-y-auto p-3 sm:p-4 bg-gray-50 space-y-2 sm:space-y-3 min-h-0">
            <!-- Messages will be appended here -->
        </div>

        <!-- Typing Indicator -->
        <div id="typing-indicator" class="hidden px-3 sm:px-4 py-2 bg-gray-50">
            <div class="flex items-center space-x-2 text-primary-600">
                <div class="flex space-x-1">
                    <div class="w-2 h-2 bg-primary-600 rounded-full animate-bounce" style="animation-delay: 0s"></div>
                    <div class="w-2 h-2 bg-primary-600 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    <div class="w-2 h-2 bg-primary-600 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
                </div>
                <span class="text-xs">AI sedang mengetik...</span>
            </div>
        </div>

        <!-- Input Area -->
        <div class="p-3 sm:p-4 bg-white border-t border-gray-200">
            <form id="chat-form" class="flex space-x-2">
                <input type="text" id="chat-input" placeholder="Ketik pesan Anda..."
                    class="flex-1 px-3 sm:px-4 py-2 sm:py-2.5 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent text-xs sm:text-sm"
                    autocomplete="off">
                <button type="submit"
                    class="w-10 h-10 sm:w-11 sm:h-11 bg-primary-800 hover:bg-primary-900 text-white rounded-full flex items-center justify-center transition-all shadow-md hover:shadow-lg flex-shrink-0">
                    <i class="fas fa-paper-plane text-xs sm:text-sm"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Chat Toggle Button -->
    <button id="ai-chat-toggle"
        class="w-14 h-14 sm:w-16 sm:h-16 bg-gradient-to-br from-primary-800 to-primary-900 hover:from-primary-700 hover:to-primary-800 rounded-full flex items-center justify-center shadow-2xl hover:shadow-primary-500/30 transition-all group relative">
        <i class="fas fa-robot text-white text-2xl sm:text-3xl group-hover:scale-110 transition-transform"></i>
        <span
            class="absolute -top-1 -right-1 sm:-top-2 sm:-right-2 w-5 h-5 bg-green-500 rounded-full flex items-center justify-center">
            <span class="w-3 h-3 bg-green-400 rounded-full animate-ping absolute"></span>
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

<!-- Back to Top Button -->
<button id="back-to-top"
    class="fixed bottom-4 left-4 sm:bottom-6 sm:left-6 z-40 w-11 h-11 sm:w-12 sm:h-12 bg-primary-800 hover:bg-primary-700 rounded-full items-center justify-center shadow-lg transition-all opacity-0 pointer-events-none hidden md:flex"
    onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
    <i class="fas fa-chevron-up text-white text-sm sm:text-base"></i>
</button>

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
</body>

</html>