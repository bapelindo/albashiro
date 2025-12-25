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

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<!-- AOS Animation -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 800,
        easing: 'ease-out-cubic',
        once: true,
        offset: 50
    });
</script>

<!-- Custom Scripts -->
<script>
    // Mobile menu toggle
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');

    mobileMenuBtn?.addEventListener('click', () => {
        mobileMenu.classList.toggle('hidden');
    });

    // Close mobile menu when clicking a link
    mobileMenu?.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            mobileMenu.classList.add('hidden');
        });
    });

    // Navbar background on scroll
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.classList.add('shadow-md');
        } else {
            navbar.classList.remove('shadow-md');
        }
    });

    // Back to top button visibility
    const backToTop = document.getElementById('back-to-top');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 500) {
            backToTop.classList.remove('opacity-0', 'pointer-events-none');
            backToTop.classList.add('opacity-100');
        } else {
            backToTop.classList.add('opacity-0', 'pointer-events-none');
            backToTop.classList.remove('opacity-100');
        }
    });

    // Animated Counter
    const counters = document.querySelectorAll('.counter');
    const speed = 100; // Animation speed

    const animateCounter = (counter) => {
        const target = +counter.getAttribute('data-target');
        const increment = target / speed;
        let count = 0;

        const updateCount = () => {
            count += increment;
            if (count < target) {
                counter.innerText = Math.ceil(count);
                requestAnimationFrame(updateCount);
            } else {
                counter.innerText = target;
            }
        };

        updateCount();
    };

    // Intersection Observer for counter animation
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                if (!counter.classList.contains('animated')) {
                    counter.classList.add('animated');
                    animateCounter(counter);
                }
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(counter => {
        counterObserver.observe(counter);
    });

    // Parallax Scrolling Effect
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;

        // Hero parallax (if exists)
        const heroSection = document.querySelector('.hero-section');
        if (heroSection) {
            heroSection.style.transform = `translateY(${scrolled * 0.5}px)`;
        }

        // Decorative elements parallax
        const parallaxElements = document.querySelectorAll('[data-parallax]');
        parallaxElements.forEach(el => {
            const speed = el.dataset.parallax || 0.5;
            el.style.transform = `translateY(${scrolled * speed}px)`;
        });
    });

    // Smooth FAQ Accordion Enhancement
    document.querySelectorAll('.faq-question').forEach(button => {
        button.addEventListener('click', () => {
            const answer = button.nextElementSibling;
            const icon = button.querySelector('.faq-icon');

            // Add smooth transition
            answer.style.transition = 'max-height 0.3s ease-out, opacity 0.3s ease-out';

            // Toggle current
            if (answer.classList.contains('hidden')) {
                answer.classList.remove('hidden');
                answer.style.maxHeight = answer.scrollHeight + 'px';
                answer.style.opacity = '1';
                icon.classList.add('rotate-180');
            } else {
                answer.style.maxHeight = '0';
                answer.style.opacity = '0';
                setTimeout(() => answer.classList.add('hidden'), 300);
                icon.classList.remove('rotate-180');
            }

            // Close others
            document.querySelectorAll('.faq-question').forEach(otherBtn => {
                if (otherBtn !== button) {
                    const otherAnswer = otherBtn.nextElementSibling;
                    const otherIcon = otherBtn.querySelector('.faq-icon');
                    otherAnswer.style.maxHeight = '0';
                    otherAnswer.style.opacity = '0';
                    setTimeout(() => otherAnswer.classList.add('hidden'), 300);
                    otherIcon?.classList.remove('rotate-180');
                }
            });
        });
    });

    // Initialize Swiper for Testimonials
    if (document.querySelector('.testimonial-swiper')) {
        new Swiper('.testimonial-swiper', {
            slidesPerView: 1,
            spaceBetween: 24,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            breakpoints: {
                640: {
                    slidesPerView: 2,
                },
                1024: {
                    slidesPerView: 3,
                },
            },
        });
    }
</script>
</body>

</html>