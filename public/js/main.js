/**
 * Main JavaScript File
 * Contains all custom scripts for Albashiro website
 */

// ============================================
// Page Loader
// ============================================
(function () {
    // Track when page started loading
    const pageLoadStart = Date.now();
    const minLoadTime = 800; // Minimum 800ms display time

    // Fade out page loader when page is fully loaded
    window.addEventListener('load', function () {
        const loader = document.getElementById('page-loader');
        if (loader) {
            const elapsedTime = Date.now() - pageLoadStart;
            const remainingTime = Math.max(0, minLoadTime - elapsedTime);

            // Wait for minimum display time before fading out
            setTimeout(() => {
                loader.classList.add('fade-out');
                setTimeout(() => {
                    loader.style.display = 'none';
                }, 500); // Wait for fade animation
            }, remainingTime);
        }
    });
})();

// ============================================
// Mobile Menu
// ============================================
(function () {
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');

    // Toggle mobile menu
    mobileMenuBtn?.addEventListener('click', () => {
        mobileMenu.classList.toggle('hidden');
    });

    // Close mobile menu when clicking a link
    mobileMenu?.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            mobileMenu.classList.add('hidden');
        });
    });
})();

// ============================================
// Navbar Scroll Effect
// ============================================
(function () {
    const navbar = document.getElementById('navbar');

    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.classList.add('shadow-md');
        } else {
            navbar.classList.remove('shadow-md');
        }
    });
})();

// ============================================
// Back to Top Button
// ============================================
(function () {
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
})();

// ============================================
// Animated Counter
// ============================================
(function () {
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
})();

// ============================================
// Parallax Scrolling Effect
// ============================================
(function () {
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
})();

// ============================================
// FAQ Accordion
// ============================================
(function () {
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
})();

// ============================================
// Swiper Initialization
// ============================================
(function () {
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
})();

// ============================================
// Flash Message Auto-Hide
// ============================================
(function () {
    setTimeout(() => {
        const flash = document.getElementById('flash-message');
        if (flash) flash.remove();
    }, 5000);
})();

// ============================================
// Server Timing Observer
// ============================================
(function () {
    if ('performance' in window && 'PerformanceObserver' in window) {
        try {
            const observer = new PerformanceObserver((list) => {
                for (const entry of list.getEntries()) {
                    if (entry.serverTiming && entry.serverTiming.length > 0) {
                        console.groupCollapsed(`Server Timing: ${entry.name}`);
                        entry.serverTiming.forEach((timing) => {
                            console.log(
                                `%c${timing.name}`,
                                'font-weight: bold; color: #1e3a5f',
                                `${timing.duration.toFixed(2)}ms`,
                                timing.description ? `(${timing.description})` : ''
                            );
                        });
                        console.groupEnd();
                    }
                }
            });

            observer.observe({ type: 'navigation', buffered: true });
            observer.observe({ type: 'resource', buffered: true });
        } catch (e) {
            console.warn('Server Timing API not supported or failed to initialize', e);
        }
    }
})();
