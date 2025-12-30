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

    // Early return if chat elements don't exist (not on chat page)
    if (!chatToggle || !chatWindow) {
        return;
    }

    let isFirstOpen = true;
    let isProcessing = false; // Prevent multiple simultaneous requests

    // Get CSRF token from meta tag or generate
    const getCSRFToken = () => {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            return metaTag.getAttribute('content');
        }
        // Fallback: get from PHP session via hidden input or cookie
        return document.querySelector('input[name="csrf_token"]')?.value || '';
    };

    // Get base URL for API calls (handles both localhost and Vercel)
    const getBaseUrl = () => {
        // On Vercel, base is just '/', on localhost it's '/albashiro'
        const path = window.location.pathname;
        if (path.startsWith('/albashiro')) {
            return '/albashiro';
        }
        return '';
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
            const response = await fetch(getBaseUrl() + '/chat/welcome', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            // Check if response is OK and is JSON
            if (!response.ok || !response.headers.get('content-type')?.includes('application/json')) {
                throw new Error('Invalid response from server');
            }

            const data = await response.json();

            if (data.success) {
                appendMessage('ai', data.message);
            }
        } catch (error) {
            console.error('Error fetching welcome message:', error);
            // Fallback to default welcome message
            appendMessage('ai', 'Assalamu\'alaikum! Selamat datang di Albashiro. Ada yang bisa saya bantu?');
        }
    };

    // Send message with streaming
    chatForm?.addEventListener('submit', async (e) => {
        e.preventDefault();

        const message = chatInput.value.trim();
        if (!message) return;

        // Prevent multiple simultaneous requests
        if (isProcessing) {
            console.log('Request already in progress, please wait...');
            return;
        }
        isProcessing = true;

        // Display user message
        appendMessage('user', message);
        chatInput.value = '';

        // Show typing indicator
        typingIndicator.classList.remove('hidden');
        scrollToBottom();

        // Bubble elements (created when first token arrives)
        let messageDiv = null;
        let bubble = null;
        let textElement = null;
        let fullResponse = '';
        let displayedResponse = '';

        // Smooth character-by-character typing animation
        let typingTimer = null;
        let isTyping = false;
        const typingSpeed = 30; // milliseconds per character (smooth like greeting)

        // Character-by-character typing function
        const typeCharacters = () => {
            if (displayedResponse.length < fullResponse.length) {
                isTyping = true;
                // Type multiple characters at once for smoother feel
                const charsToAdd = Math.min(2, fullResponse.length - displayedResponse.length);
                displayedResponse = fullResponse.substring(0, displayedResponse.length + charsToAdd);
                textElement.innerHTML = parseMarkdown(displayedResponse);
                scrollToBottom();

                typingTimer = setTimeout(typeCharacters, typingSpeed);
            } else {
                isTyping = false;
                // Final render with full markdown
                textElement.innerHTML = parseMarkdown(fullResponse);
                scrollToBottom();
            }
        };

        try {
            // Hybrid: Node.js (Vercel) fetches context from PHP, then streams
            // Localhost: Direct PHP streaming
            const isLocalhost = window.location.hostname === 'localhost' ||
                window.location.hostname === '127.0.0.1' ||
                window.location.hostname === 'albashiro.bapel.my.id';

            const streamEndpoint = isLocalhost
                ? getBaseUrl() + '/chat/stream'  // PHP direct
                : '/api/stream';                  // Node.js proxy (calls PHP internally)

            console.log('[CLIENT DEBUG] Using endpoint:', streamEndpoint);

            // Add timeout to prevent hanging requests
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 90000); // 90 second timeout

            const response = await fetch(streamEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    message: message,
                    csrf_token: getCSRFToken()
                }),
                signal: controller.signal
            });

            clearTimeout(timeoutId); // Clear timeout if request succeeds

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            // Read the stream
            const reader = response.body.getReader();
            const decoder = new TextDecoder();
            let buffer = '';

            console.log('[CLIENT DEBUG] Starting to read SSE stream...');

            while (true) {
                const { done, value } = await reader.read();

                if (done) {
                    console.log('[CLIENT DEBUG] Stream reading completed');
                    break;
                }

                // Decode chunk
                buffer += decoder.decode(value, { stream: true });

                // Process complete SSE messages (separated by \n\n)
                const messages = buffer.split('\n\n');
                buffer = messages.pop() || ''; // Keep incomplete message in buffer

                for (let msg of messages) {
                    msg = msg.trim();
                    if (!msg) continue;

                    console.log('[CLIENT DEBUG] Received SSE message:', msg.substring(0, 100));

                    // Robust parsing: Find 'data: ' anywhere (handles prepended warnings/whitespace)
                    const dataIndex = msg.indexOf('data: ');
                    if (dataIndex === -1) {
                        console.warn('[CLIENT DEBUG] No "data:" prefix found in message');
                        continue;
                    }

                    try {
                        const jsonStr = msg.substring(dataIndex + 6); // Extract JSON after 'data: '
                        const data = JSON.parse(jsonStr);

                        console.log('[CLIENT DEBUG] Parsed data:', data);

                        if (data.error) {
                            // Hide typing indicator
                            typingIndicator.classList.add('hidden');

                            // Create bubble for error if not exists
                            if (!messageDiv) {
                                messageDiv = document.createElement('div');
                                messageDiv.className = 'flex justify-start animate-fade-in';
                                bubble = document.createElement('div');
                                bubble.className = 'max-w-[85%] px-5 py-3 rounded-2xl shadow-md transition-all hover:shadow-lg bg-emerald-50 text-emerald-900 border-2 border-emerald-100 rounded-bl-sm';
                                bubble.innerHTML = '<p class="text-sm leading-relaxed whitespace-pre-wrap"></p>';
                                messageDiv.appendChild(bubble);
                                chatMessages.appendChild(messageDiv);
                                textElement = bubble.querySelector('p');
                            }

                            textElement.textContent = data.message || 'Maaf, terjadi kesalahan.';
                            break;
                        }

                        if (data.status) {
                            // UPDATE EXISTING INDICATOR TEXT
                            // Instead of creating a duplicate pill, update the footer indicator
                            const thinkingText = document.getElementById('ai-thinking-text');
                            if (thinkingText) {
                                thinkingText.textContent = data.status;
                            }
                            continue; // Skip processing other data types
                        }

                        if (data.token) {
                            // Create bubble on first token
                            if (!messageDiv) {
                                // Hide typing indicator when first token arrives
                                typingIndicator.classList.add('hidden');

                                messageDiv = document.createElement('div');
                                messageDiv.className = 'flex justify-start animate-fade-in';

                                bubble = document.createElement('div');
                                bubble.className = 'max-w-[85%] px-5 py-3 rounded-2xl shadow-md transition-all hover:shadow-lg bg-emerald-50 text-emerald-900 border-2 border-emerald-100 rounded-bl-sm';
                                bubble.innerHTML = '<p class="text-sm leading-relaxed whitespace-pre-wrap"></p>';

                                messageDiv.appendChild(bubble);
                                chatMessages.appendChild(messageDiv);
                                scrollToBottom();

                                textElement = bubble.querySelector('p');
                            }

                            fullResponse += data.token;

                            // Start typing animation if not already typing
                            if (!isTyping) {
                                typeCharacters();
                            }
                        }

                        if (data.done && data.metadata) {
                            // Stream complete - let typing finish naturally
                        }
                    } catch (parseError) {
                    }
                }
            }

            // Wait for typing to complete
            const waitForTyping = setInterval(() => {
                if (!isTyping && displayedResponse.length === fullResponse.length) {
                    clearInterval(waitForTyping);
                    if (textElement && fullResponse) {
                        textElement.innerHTML = parseMarkdown(fullResponse);

                        // VOICE BUTTON (Safe Layout: Inside bubble at bottom right)
                        const voiceBtn = document.createElement('button');
                        voiceBtn.className = 'float-right ml-2 mt-1 text-emerald-400 hover:text-emerald-600 transition-colors p-1';
                        voiceBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" /></svg>';
                        voiceBtn.onclick = (e) => {
                            e.stopPropagation();
                            window.speakText(fullResponse, voiceBtn);
                        };

                        // Append icon to the bubble content (last paragraph)
                        const lastP = textElement.querySelector('p:last-child');
                        if (lastP) lastP.appendChild(voiceBtn);
                        else textElement.appendChild(voiceBtn);

                        scrollToBottom();
                    }
                }
            }, 100);

            // Safety: Clear interval after 30 seconds to prevent memory leak
            setTimeout(() => clearInterval(waitForTyping), 30000);

        } catch (error) {
            console.error('Error sending message:', error);
            typingIndicator.classList.add('hidden');

            // Show error in the bubble (create if doesn't exist)
            if (!messageDiv) {
                messageDiv = document.createElement('div');
                messageDiv.className = 'flex justify-start animate-fade-in';
                bubble = document.createElement('div');
                bubble.className = 'max-w-[85%] px-5 py-3 rounded-2xl shadow-md bg-red-50 text-red-900 border-2 border-red-100 rounded-bl-sm';
                bubble.innerHTML = '<p class="text-sm leading-relaxed whitespace-pre-wrap"></p>';
                messageDiv.appendChild(bubble);
                chatMessages.appendChild(messageDiv);
                textElement = bubble.querySelector('p');
            }

            if (textElement) {
                textElement.textContent = 'Maaf, saat ini Asisten AI sedang istirahat (Offline). Silakan coba lagi beberapa saat nanti. 🙏';
            }
        } finally {
            // Always reset processing flag
            isProcessing = false;
        }

        scrollToBottom();
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
            // DETECT ENGAGEMENT BLOCK (Quick Replies)
            let contentPart = text;
            let quickReplies = [];

            if (text.includes('[ENGAGEMENT]')) {
                const parts = text.split('[ENGAGEMENT]');
                contentPart = parts[0].trim(); // Actual message content
                const engagementPart = parts[1]; // Questions block

                // Parse questions (e.g., "1. Question ...")
                const matches = engagementPart.match(/\d\.\s+(.+)/g);
                if (matches) {
                    quickReplies = matches.map(m => m.replace(/^\d\.\s+/, '').trim());
                }
            }

            bubble.innerHTML = ''; // Start empty
            messageDiv.appendChild(bubble);
            chatMessages.appendChild(messageDiv);
            scrollToBottom();

            // Typing effect
            let index = 0;
            const typingSpeed = 8; // milliseconds per character

            const typeWriter = () => {
                if (index < contentPart.length) {
                    // Use parseMarkdown for formatting
                    const currentText = contentPart.substring(0, index + 1);
                    bubble.innerHTML = `<p class="text-sm leading-relaxed whitespace-pre-wrap">${parseMarkdown(currentText)}</p>`;
                    index++;
                    setTimeout(typeWriter, typingSpeed);
                    scrollToBottom();
                } else {
                    // Final render with full markdown
                    bubble.innerHTML = `<p class="text-sm leading-relaxed whitespace-pre-wrap">${parseMarkdown(contentPart)}</p>`;

                    // RENDER QUICK REPLY BUTTONS
                    if (quickReplies.length > 0) {
                        const btnContainer = document.createElement('div');
                        btnContainer.className = 'mt-3 flex flex-wrap gap-2 animate-fade-in pl-1';

                        quickReplies.forEach(q => {
                            const btn = document.createElement('button');
                            btn.className = 'px-3 py-1.5 bg-white border border-emerald-200 text-emerald-700 text-xs rounded-full shadow-sm hover:bg-emerald-50 hover:shadow-md transition-all cursor-pointer whitespace-nowrap';
                            btn.textContent = q;
                            btn.onclick = () => {
                                chatInput.value = q;
                                chatForm.dispatchEvent(new Event('submit'));
                                btnContainer.remove(); // Click once
                            };
                            btnContainer.appendChild(btn);
                        });

                        // Append container OUTSIDE bubble, inside messageDiv
                        messageDiv.appendChild(btnContainer);
                        // Also auto smooth scroll to buttons
                    }

                    // VOICE BUTTON
                    // VOICE BUTTON (Safe Layout: Inside bubble at bottom right)
                    const voiceBtn = document.createElement('button');
                    voiceBtn.className = 'float-right ml-2 mt-1 text-emerald-400 hover:text-emerald-600 transition-colors p-1';
                    voiceBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" /></svg>';
                    voiceBtn.onclick = (e) => {
                        e.stopPropagation();
                        window.speakText(contentPart, voiceBtn);
                    };

                    // Append icon to the bubble content (last paragraph)
                    const lastP = bubble.querySelector('p:last-child');
                    if (lastP) lastP.appendChild(voiceBtn);
                    else bubble.appendChild(voiceBtn);

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
        // Headings: ### text
        text = text.replace(/^### (.+)$/gm, '<h3 class="text-base font-bold mt-3 mb-2">$1</h3>');
        text = text.replace(/^## (.+)$/gm, '<h2 class="text-lg font-bold mt-4 mb-2">$1</h2>');
        text = text.replace(/^# (.+)$/gm, '<h1 class="text-xl font-bold mt-4 mb-2">$1</h1>');

        // Code blocks: ```code```
        text = text.replace(/```(.+?)```/gs, '<code class="block bg-gray-100 text-gray-800 px-3 py-2 rounded my-2 text-xs font-mono">$1</code>');

        // Inline code: `code`
        text = text.replace(/`(.+?)`/g, '<code class="bg-gray-100 text-gray-800 px-1.5 py-0.5 rounded text-xs font-mono">$1</code>');

        // Bold: **text** or __text__
        text = text.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        text = text.replace(/__(.+?)__/g, '<strong>$1</strong>');

        // Italic: *text* or _text_
        text = text.replace(/\*(.+?)\*/g, '<em>$1</em>');
        text = text.replace(/_(.+?)_/g, '<em>$1</em>');

        // Strikethrough: ~~text~~
        text = text.replace(/~~(.+?)~~/g, '<del class="opacity-70">$1</del>');

        // Blockquote: > text
        text = text.replace(/^> (.+)$/gm, '<blockquote class="border-l-4 border-emerald-300 pl-3 py-1 my-2 italic text-emerald-700 bg-emerald-50/50">$1</blockquote>');

        // Links: [text](url)
        text = text.replace(/\[(.+?)\]\((.+?)\)/g, '<a href="$2" target="_blank" class="underline text-accent-600">$1</a>');

        // Unordered lists: * item or - item
        text = text.replace(/^\* (.+)$/gm, '<li class="ml-4">$1</li>');
        text = text.replace(/^- (.+)$/gm, '<li class="ml-4">$1</li>');

        // Wrap consecutive <li> in <ul>
        text = text.replace(/(<li.*<\/li>\n?)+/g, '<ul class="list-disc my-2">$&</ul>');

        // Line breaks
        text = text.replace(/\n/g, '<br>');

        // TRUST BADGES (Sumber: ...)
        // Pattern: (Sumber: Alodokter - Anxiety)
        text = text.replace(/(\(Sumber: .+?\))/g, '<span class="inline-block px-2 py-0.5 mx-1 bg-teal-50 text-teal-700 text-[10px] font-medium uppercase tracking-wide rounded-full border border-teal-100">$1</span>');

        return text;
    };

    // Scroll to bottom of chat (debounced for performance)
    let scrollTimeout;
    const scrollToBottom = () => {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
            if (chatMessages) {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        }, 50); // Debounce 50ms
    };

    // VOICE SYNTHESIS (BETA)
    let speechSynth = window.speechSynthesis;
    let speaking = false;

    window.speakText = (text, btnElement) => {
        // SVG Icons
        const iconSpeaker = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" /></svg>';
        const iconStop = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" /></svg>';

        if (speaking) {
            speechSynth.cancel();
            speaking = false;
            // Reset all buttons
            document.querySelectorAll('.voice-active').forEach(btn => {
                btn.innerHTML = iconSpeaker;
                btn.classList.remove('text-amber-500', 'voice-active');
                btn.classList.add('text-emerald-400');
            });
            return;
        }

        // Clean text for speech
        let cleanText = text.replace(/\[ENGAGEMENT\][\s\S]*/, '');
        cleanText = cleanText.replace(/\(Sumber: .+?\)/g, '');
        cleanText = cleanText.replace(/[*_#`]/g, '');
        cleanText = cleanText.replace(/<[^>]*>/g, '');

        const utterance = new SpeechSynthesisUtterance(cleanText);
        utterance.lang = 'id-ID';
        utterance.rate = 1.0;
        utterance.pitch = 1.0;

        utterance.onstart = () => {
            speaking = true;
            if (btnElement) {
                btnElement.innerHTML = iconStop;
                btnElement.classList.remove('text-emerald-400');
                btnElement.classList.add('text-amber-500', 'voice-active');
            }
        };

        utterance.onend = () => {
            speaking = false;
            if (btnElement) {
                btnElement.innerHTML = iconSpeaker;
                btnElement.classList.remove('text-amber-500', 'voice-active');
                btnElement.classList.add('text-emerald-400');
            }
        };

        utterance.onerror = () => {
            speaking = false;
            if (btnElement) {
                btnElement.innerHTML = iconSpeaker;
                btnElement.classList.remove('text-amber-500', 'voice-active');
                btnElement.classList.add('text-emerald-400');
            }
        };

        speechSynth.speak(utterance);
    };
})();


