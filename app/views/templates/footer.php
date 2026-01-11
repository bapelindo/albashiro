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
                                Dusun Tanggung, Tanggung, Kec. Turen, Kabupaten
                                Malang, Jawa Timur 65175
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
                            +62 853-8520-4410
                        </a>
                    </li>
                    <li class="flex items-center space-x-3">
                        <div
                            class="w-10 h-10 bg-accent-600/20 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-envelope text-accent-400"></i>
                        </div>
                        <a href="mailto:<?= ADMIN_EMAIL ?>"
                            class="text-primary-300 hover:text-gold-400 transition-colors">
                            ahnrizki@gmail.com
                        </a>
                    </li>
                    <li class="flex items-center space-x-3">
                        <div
                            class="w-10 h-10 bg-accent-600/20 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-clock text-accent-400"></i>
                        </div>
                        <div class="text-primary-300 text-sm">
                            Senin - Jumat: 08:00 - 16:00<br>
                            Sabtu - Minggu: Tutup
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
                    <h3 class="text-white font-semibold text-xs sm:text-sm">Asisten AI Albashiroh</h3>
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

<!-- Floating Music Control -->
<div id="music-widget" class="fixed bottom-24 right-4 sm:bottom-28 sm:right-6 z-[100] flex flex-col items-center gap-3">

    <!-- Initial Start Button (Unified) -->
    <button id="music-start-btn"
        class="w-12 h-12 sm:w-14 sm:h-14 bg-white/90 backdrop-blur-sm hover:bg-white text-emerald-700 rounded-full flex items-center justify-center shadow-lg hover:shadow-xl transition-all border border-emerald-100 group relative z-50">
        <div class="absolute inset-0 bg-emerald-50 rounded-full opacity-0 group-hover:opacity-50 transition-opacity">
        </div>
        <i class="fas fa-music text-lg sm:text-xl relative z-10 animate-pulse-slow"></i>
        <!-- Ripple effect hint -->
        <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-20 animate-ping"></span>
    </button>

    <!-- Controls Group (Hidden initially, appears after interaction) -->
    <div id="music-controls" class="hidden flex-col items-center gap-3 transition-all duration-500">
        <!-- Play/Pause Button -->
        <button id="music-toggle"
            class="w-10 h-10 sm:w-12 sm:h-12 bg-white/90 backdrop-blur-sm hover:bg-white text-emerald-700 rounded-full flex items-center justify-center shadow-lg hover:shadow-xl transition-all border border-emerald-100 group relative overflow-hidden">

            <!-- Animated Background for Playing State -->
            <div id="music-playing-bg" class="absolute inset-0 bg-emerald-50 opacity-0 transition-opacity duration-300">
            </div>
            <div id="music-waves" class="absolute inset-0 flex items-center justify-center opacity-0">
                <span
                    class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-20 animate-ping"></span>
            </div>

            <!-- Icons -->
            <i id="music-icon-play"
                class="fas fa-play text-sm sm:text-base relative z-10 transition-transform duration-300 pl-0.5"></i>
            <i id="music-icon-pause"
                class="fas fa-pause text-sm sm:text-base absolute z-10 opacity-0 scale-50 transition-all duration-300"></i>
        </button>

        <!-- Next Track Button -->
        <button id="music-next"
            class="w-8 h-8 sm:w-10 sm:h-10 bg-white/90 backdrop-blur-sm hover:bg-white text-emerald-600 rounded-full flex items-center justify-center shadow-md hover:shadow-lg transition-all border border-emerald-100 transform hover:scale-105"
            title="Ganti Musik">
            <i class="fas fa-step-forward text-xs sm:text-sm"></i>
        </button>
    </div>
</div>

<!-- Audio Consent Overlay (Isolated) -->
<?php include __DIR__ . '/overlay.php'; ?>

<!-- Background Audio -->
<audio id="bg-music" loop preload="auto">
    <source src="" type="audio/mpeg">
    Your browser does not support the audio element.
</audio>

<!-- Floating WhatsApp Button -->
<a href="https://wa.me/<?= ADMIN_WHATSAPP ?>?text=Assalamu'alaikum, saya ingin konsultasi tentang hipnoterapi"
    target="_blank"
    class="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 z-[90] w-14 h-14 sm:w-16 sm:h-16 bg-green-500 hover:bg-green-600 rounded-full flex items-center justify-center shadow-2xl hover:shadow-green-500/30 transition-all group">
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

    // Background Music Control
    document.addEventListener('DOMContentLoaded', function () {
        const music = document.getElementById('bg-music');
        const source = music.querySelector('source');

        // Widget Elements
        const startBtn = document.getElementById('music-start-btn');
        const controlsDiv = document.getElementById('music-controls');
        const toggleBtn = document.getElementById('music-toggle');
        const nextBtn = document.getElementById('music-next');

        // UI Icons/Effects
        const iconPlay = document.getElementById('music-icon-play');
        const iconPause = document.getElementById('music-icon-pause');
        const playingBg = document.getElementById('music-playing-bg');
        const waves = document.getElementById('music-waves');

        // Playlist Configuration
        const playlist = [
            '<?= base_url("public/sound/1.mpeg") ?>',
            '<?= base_url("public/sound/2.mpeg") ?>'
        ];

        // State Management
        let isPlaying = localStorage.getItem('musicPlaying') === 'true';
        let storedTime = localStorage.getItem('musicTime');
        let currentTrackIndex = parseInt(localStorage.getItem('musicTrackIndex') || '0');

        // Validate track index
        if (currentTrackIndex >= playlist.length || currentTrackIndex < 0) {
            currentTrackIndex = 0;
        }

        // Initialize Audio Source - Lazy Loaded
        function loadTrack(index) {
            source.src = playlist[index];
            music.load();
        }

        // NO IMMEDIATE LOAD - Fixes "Blocking Skeleton" issue
        // loadTrack(currentTrackIndex); <-- REMOVED

        music.volume = 0.3;

        // Restore playback position
        if (storedTime) {
            music.currentTime = parseFloat(storedTime);
        }

        // Persist playback position
        setInterval(() => {
            if (!music.paused) {
                localStorage.setItem('musicTime', music.currentTime);
            }
        }, 1000);

        window.addEventListener('beforeunload', () => {
            localStorage.setItem('musicTime', music.currentTime);
        });

        // UI Updates
        function updateMusicUI(playing) {
            if (playing) {
                // Expanding to 2 buttons
                showControls();

                iconPlay.classList.add('opacity-0', 'scale-50');
                iconPause.classList.remove('opacity-0', 'scale-50');
                playingBg.classList.remove('opacity-0');
                waves.classList.remove('opacity-0');
                toggleBtn.classList.add('ring-2', 'ring-emerald-400', 'ring-offset-2');
            } else {
                // Collapsing to 1 button
                showStartButton();

                iconPlay.classList.remove('opacity-0', 'scale-50');
                iconPause.classList.add('opacity-0', 'scale-50');
                playingBg.classList.add('opacity-0');
                waves.classList.add('opacity-0');
                toggleBtn.classList.remove('ring-2', 'ring-emerald-400', 'ring-offset-2');
            }
        }

        // UI Actions
        function showControls() {
            startBtn.classList.add('hidden');
            controlsDiv.classList.remove('hidden');
            controlsDiv.classList.add('flex', 'animate-fade-in-up');
        }

        function showStartButton() {
            startBtn.classList.remove('hidden');
            controlsDiv.classList.add('hidden');
            controlsDiv.classList.remove('flex');
        }

        function collapseControls() {
            if (!controlsDiv.classList.contains('hidden')) {
                showStartButton();
            }
        }

        // Logic to run when Main/Start Button is clicked
        function handleStartClick() {
            showControls();
            if (music.paused) {
                playMusic();
            }
        }

        // Playback Control - Lazy Loading Implemented
        function playMusic() {
            // Check if track is loaded. If not (src is empty), load it now.
            if (!source.getAttribute('src')) {
                loadTrack(currentTrackIndex);
            }

            const playPromise = music.play();
            if (playPromise !== undefined) {
                playPromise.then(_ => {
                    isPlaying = true;
                    localStorage.setItem('musicPlaying', 'true');
                    updateMusicUI(true);
                })
                    .catch(error => {
                        console.log("Autoplay prevented:", error);
                        isPlaying = false;
                        localStorage.setItem('musicPlaying', 'false');
                        updateMusicUI(false);
                    });
            }
        }

        function pauseMusic() {
            music.pause();
            isPlaying = false;
            localStorage.setItem('musicPlaying', 'false');
            updateMusicUI(false);
            // Default behavior: collapse when explicitly paused by user
            showStartButton();
        }

        function toggleMusic() {
            if (music.paused) {
                playMusic();
            } else {
                pauseMusic();
            }
        }

        function nextTrack() {
            currentTrackIndex = (currentTrackIndex + 1) % playlist.length;
            localStorage.setItem('musicTrackIndex', currentTrackIndex);
            localStorage.setItem('musicTime', '0');
            loadTrack(currentTrackIndex);
            playMusic();
        }

        // Event Listeners
        startBtn.addEventListener('click', handleStartClick);
        toggleBtn.addEventListener('click', toggleMusic);
        nextBtn.addEventListener('click', nextTrack);

        // Global Event Bus for Overlay to trigger music without knowing logic
        window.addEventListener('request-music-start', () => {
            playMusic();
        });

        // Auto-Collapse Listeners
        window.addEventListener('scroll', () => {
            // Only collapse if user has scrolled down a bit to avoid accidental collapse on minor moves
            if (window.scrollY > 100) {
                collapseControls();
            }
        }, { passive: true });

        // Collapse when opening Chatbot
        const chatToggle = document.getElementById('ai-chat-toggle');
        if (chatToggle) {
            chatToggle.addEventListener('click', collapseControls);
        }

        // Initialization Logic
        if (isPlaying) {
            playMusic(); // This handles lazy load inside function
            showControls();
        } else {
            updateMusicUI(false);
            showStartButton();
        }
    });
</script>

<!-- Custom Scripts - Local -->
<?php $appVersion = '1.0.1'; // Bump version to clear cache ?>
<script src="<?= base_url('public/js/main.js?v=' . $appVersion) ?>"></script>

<style>
    /* Additional animations for music widget */
    @keyframes fade-in-up {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in-up {
        animation: fade-in-up 0.3s ease-out forwards;
    }
</style>

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