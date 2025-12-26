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
// AI Chatbot
// ============================================
(function () {
    const chatToggle = document.getElementById('ai-chat-toggle');
    const chatWindow = document.getElementById('ai-chat-window');
    const closeChat = document.getElementById('close-chat');
    const chatForm = document.getElementById('chat-form');
    const chatInput = document.getElementById('chat-input');
    const chatMessages = document.getElementById('chat-messages');
    const typingIndicator = document.getElementById('typing-indicator');
    const chatBackdrop = document.getElementById('chat-backdrop');

    let isFirstOpen = true;

    // Get CSRF token from meta tag or generate
    const getCSRFToken = () => {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            return metaTag.getAttribute('content');
        }
        // Fallback: get from PHP session via hidden input or cookie
        return document.querySelector('input[name="csrf_token"]')?.value || '';
    };


    // Toggle chat window
    chatToggle?.addEventListener('click', (e) => {
        e.stopPropagation(); // Prevent triggering document click
        const isHidden = chatWindow.classList.contains('hidden');

        if (isHidden) {
            chatWindow.classList.remove('hidden');
            chatBackdrop.classList.remove('hidden');
            chatInput.focus();

            // Show welcome message on first open
            if (isFirstOpen) {
                isFirstOpen = false;
                fetchWelcomeMessage();
            }
        } else {
            chatWindow.classList.add('hidden');
            chatBackdrop.classList.add('hidden');
        }
    });

    // Close chat window
    closeChat?.addEventListener('click', () => {
        chatWindow.classList.add('hidden');
        chatBackdrop.classList.add('hidden');
    });

    // Close chat when clicking backdrop
    chatBackdrop?.addEventListener('click', () => {
        chatWindow.classList.add('hidden');
        chatBackdrop.classList.add('hidden');
    });

    // Prevent chat window clicks from closing it
    chatWindow?.addEventListener('click', (e) => {
        e.stopPropagation();
    });

    // Fetch welcome message
    const fetchWelcomeMessage = async () => {
        try {
            const response = await fetch('/albashiro/chat/welcome', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                appendMessage('ai', data.message);
            }
        } catch (error) {
            console.error('Error fetching welcome message:', error);
            appendMessage('ai', 'Assalamu\'alaikum! Selamat datang di Albashiro. Ada yang bisa saya bantu?');
        }
    };

    // Send message
    chatForm?.addEventListener('submit', async (e) => {
        e.preventDefault();

        const message = chatInput.value.trim();
        if (!message) return;

        // Display user message
        appendMessage('user', message);
        chatInput.value = '';

        // Show typing indicator
        typingIndicator.classList.remove('hidden');
        scrollToBottom();

        try {
            const response = await fetch('/albashiro/chat/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    message: message,
                    csrf_token: getCSRFToken()
                })
            });

            const data = await response.json();

            // Debug logging
            console.log('API Response:', data);

            // Hide typing indicator
            typingIndicator.classList.add('hidden');

            if (data.success && data.response) {
                appendMessage('ai', data.response);
            } else {
                appendMessage('ai', data.message || 'Maaf, terjadi kesalahan. Silakan coba lagi.');
            }
        } catch (error) {
            console.error('Error sending message:', error);
            typingIndicator.classList.add('hidden');
            appendMessage('ai', 'Maaf, koneksi terputus. Silakan coba lagi atau hubungi admin via WhatsApp.');
        }
    });

    // Append message to chat with typing effect
    const appendMessage = (sender, text) => {
        // Safety check
        if (!text) {
            console.error('appendMessage called with undefined text');
            text = 'Error: No response received';
        }

        const messageDiv = document.createElement('div');
        messageDiv.className = `flex ${sender === 'user' ? 'justify-end' : 'justify-start'} animate-fade-in`;

        const bubble = document.createElement('div');
        bubble.className = `max-w-[85%] px-5 py-3 rounded-2xl shadow-md transition-all hover:shadow-lg ${sender === 'user'
            ? 'bg-gradient-to-br from-teal-600 to-emerald-700 text-white rounded-br-sm'
            : 'bg-emerald-50 text-emerald-900 border-2 border-emerald-100 rounded-bl-sm'
            }`;

        // For AI messages, add gentle typing effect
        if (sender === 'ai') {
            bubble.innerHTML = ''; // Start empty
            messageDiv.appendChild(bubble);
            chatMessages.appendChild(messageDiv);
            scrollToBottom();

            // Typing effect
            let index = 0;
            const typingSpeed = 8; // milliseconds per character (faster, more natural)

            const typeWriter = () => {
                if (index < text.length) {
                    // Use parseMarkdown for formatting
                    const currentText = text.substring(0, index + 1);
                    bubble.innerHTML = `<p class="text-sm leading-relaxed whitespace-pre-wrap">${parseMarkdown(currentText)}</p>`;
                    index++;
                    setTimeout(typeWriter, typingSpeed);
                    scrollToBottom();
                } else {
                    // Final render with full markdown
                    bubble.innerHTML = `<p class="text-sm leading-relaxed whitespace-pre-wrap">${parseMarkdown(text)}</p>`;
                    scrollToBottom();
                }
            };

            typeWriter();
        } else {
            // User messages appear instantly
            const formattedText = text.replace(/\n/g, '<br>');
            bubble.innerHTML = `<p class="text-sm leading-relaxed whitespace-pre-wrap">${formattedText}</p>`;
            messageDiv.appendChild(bubble);
            chatMessages.appendChild(messageDiv);
            scrollToBottom();
        }
    };

    // Simple markdown parser
    const parseMarkdown = (text) => {
        // Bold: **text** or __text__
        text = text.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        text = text.replace(/__(.+?)__/g, '<strong>$1</strong>');

        // Italic: *text* or _text_
        text = text.replace(/\*(.+?)\*/g, '<em>$1</em>');
        text = text.replace(/_(.+?)_/g, '<em>$1</em>');

        // Links: [text](url)
        text = text.replace(/\[(.+?)\]\((.+?)\)/g, '<a href="$2" target="_blank" class="underline text-accent-600">$1</a>');

        // Unordered lists: * item or - item
        text = text.replace(/^\* (.+)$/gm, '<li class="ml-4">$1</li>');
        text = text.replace(/^- (.+)$/gm, '<li class="ml-4">$1</li>');

        // Wrap consecutive <li> in <ul>
        text = text.replace(/(<li.*<\/li>\n?)+/g, '<ul class="list-disc my-2">$&</ul>');

        // Line breaks
        text = text.replace(/\n/g, '<br>');

        return text;
    };

    // Scroll to bottom of chat
    const scrollToBottom = () => {
        setTimeout(() => {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }, 100);
    };
})();


