<!DOCTYPE html>
<html lang="id" class="scroll-smooth overflow-x-hidden">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Albashiro - Islamic Spiritual Hypnotherapy. Temukan kedamaian jiwa dengan hipnoterapi yang berlandaskan nilai-nilai Islam.">
    <meta name="keywords"
        content="hipnoterapi islami, hipnoterapi syariah, konseling islam, terapi trauma, kecemasan, Jakarta">

    <title><?= e($title ?? 'Beranda') ?> | <?= SITE_NAME ?> - <?= SITE_TAGLINE ?></title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        // New Hypnotherapy Color Palette
                        primary: {
                            50: '#f0f4f8',
                            100: '#d9e2ec',
                            200: '#bcccdc',
                            300: '#9fb3c8',
                            400: '#829ab1',
                            500: '#627d98',
                            600: '#486581',
                            700: '#334e68',
                            800: '#243b53',
                            900: '#1e3a5f',
                        },
                        accent: {
                            50: '#f5f3ff',
                            100: '#ede9fe',
                            200: '#ddd6fe',
                            300: '#c4b5fd',
                            400: '#a78bfa',
                            500: '#8b5cf6',
                            600: '#7c3aed',
                            700: '#6b5b95',
                            800: '#5b21b6',
                            900: '#4c1d95',
                        },
                        lavender: {
                            50: '#faf8fc',
                            100: '#f3eef8',
                            200: '#e9e0f3',
                            300: '#d9c9e8',
                            400: '#c4a8d8',
                            500: '#b8a9c9',
                            600: '#9d7fb8',
                            700: '#8a6aa5',
                            800: '#735689',
                            900: '#5f4770',
                        },
                        calm: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#a8d5e5',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                        cream: {
                            50: '#fefdfb',
                            100: '#faf8f5',
                            200: '#f5f0e8',
                            300: '#ebe3d5',
                            400: '#ddd0bb',
                            500: '#cbb89a',
                            600: '#b49a73',
                            700: '#96795a',
                            800: '#7a634c',
                            900: '#655241',
                        },
                        gold: {
                            50: '#fdfbf7',
                            100: '#fcf7eb',
                            200: '#f8ecd3',
                            300: '#f2ddb2',
                            400: '#e8c77e',
                            500: '#c9a959',
                            600: '#b8924a',
                            700: '#99763f',
                            800: '#7d5f38',
                            900: '#674e31',
                        }
                    },
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                        'arabic': ['Amiri', 'serif'],
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Amiri:wght@400;700&display=swap"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Swiper CSS for Carousel -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #6b5b95;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #5b4a85;
        }

        /* Islamic Pattern Background - Subtle */
        .islamic-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M40 0L40 80M0 40L80 40' stroke='%236b5b95' stroke-width='0.3' fill='none' opacity='0.08'/%3E%3Ccircle cx='40' cy='40' r='20' stroke='%236b5b95' stroke-width='0.3' fill='none' opacity='0.05'/%3E%3C/svg%3E");
        }

        /* Gradient Text */
        .gradient-text {
            background: linear-gradient(135deg, #1e3a5f 0%, #6b5b95 50%, #c9a959 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Glass Effect */
        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        /* Smooth Hover Transitions */
        .hover-lift {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hover-lift:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px rgba(30, 58, 95, 0.15);
        }

        /* Hypnotic Glow */
        .hypno-glow {
            box-shadow: 0 0 40px rgba(107, 91, 149, 0.2);
        }

        /* Star Animation */
        @keyframes twinkle {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .star-animate {
            animation: twinkle 2s ease-in-out infinite;
        }

        /* Pulse Animation */
        @keyframes gentle-pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 0.8;
            }

            50% {
                transform: scale(1.05);
                opacity: 1;
            }
        }

        .gentle-pulse {
            animation: gentle-pulse 4s ease-in-out infinite;
        }

        /* Swiper Custom Styles */
        .swiper-pagination-bullet {
            background: #6b5b95 !important;
        }

        .swiper-pagination-bullet-active {
            background: #1e3a5f !important;
        }

        .swiper-button-next,
        .swiper-button-prev {
            color: #1e3a5f !important;
        }
    </style>
</head>

<body class="font-sans antialiased text-gray-800 bg-cream-50 overflow-x-hidden w-full">

    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 glass border-b border-gray-100" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <a href="<?= base_url() ?>" class="flex items-center space-x-3 group">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-primary-800 to-accent-700 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-shadow">
                        <i class="fas fa-spa text-white text-xl"></i>
                    </div>
                    <div>
                        <span class="text-2xl font-bold text-primary-900"><?= SITE_NAME ?></span>
                        <span class="block text-xs text-accent-600 font-medium -mt-1">Islamic Hypnotherapy</span>
                    </div>
                </a>

                <!-- Desktop Menu - Multi-page links -->
                <div class="hidden lg:flex items-center space-x-1">
                    <a href="<?= base_url() ?>"
                        class="px-4 py-2 text-gray-600 hover:text-primary-800 hover:bg-primary-50 rounded-lg font-medium transition-all <?= ($title ?? '') === 'Beranda' ? 'text-primary-800 bg-primary-50' : '' ?>">Beranda</a>
                    <a href="<?= base_url('tentang') ?>"
                        class="px-4 py-2 text-gray-600 hover:text-primary-800 hover:bg-primary-50 rounded-lg font-medium transition-all <?= ($title ?? '') === 'Tentang Kami' ? 'text-primary-800 bg-primary-50' : '' ?>">Tentang</a>
                    <a href="<?= base_url('layanan') ?>"
                        class="px-4 py-2 text-gray-600 hover:text-primary-800 hover:bg-primary-50 rounded-lg font-medium transition-all <?= ($title ?? '') === 'Layanan' ? 'text-primary-800 bg-primary-50' : '' ?>">Layanan</a>
                    <a href="<?= base_url('terapis') ?>"
                        class="px-4 py-2 text-gray-600 hover:text-primary-800 hover:bg-primary-50 rounded-lg font-medium transition-all <?= ($title ?? '') === 'Terapis Kami' ? 'text-primary-800 bg-primary-50' : '' ?>">Terapis</a>
                    <a href="<?= base_url('blog') ?>"
                        class="px-4 py-2 text-gray-600 hover:text-primary-800 hover:bg-primary-50 rounded-lg font-medium transition-all <?= ($title ?? '') === 'Blog' || str_contains($title ?? '', 'Blog') ? 'text-primary-800 bg-primary-50' : '' ?>">Blog</a>
                    <a href="<?= base_url('kontak') ?>"
                        class="px-4 py-2 text-gray-600 hover:text-primary-800 hover:bg-primary-50 rounded-lg font-medium transition-all <?= ($title ?? '') === 'Kontak' ? 'text-primary-800 bg-primary-50' : '' ?>">Kontak</a>
                    <a href="<?= base_url('reservasi') ?>"
                        class="ml-4 inline-flex items-center px-6 py-3 bg-gradient-to-r from-primary-800 to-accent-700 text-white font-semibold rounded-full shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all">
                        <i class="fab fa-whatsapp mr-2"></i>
                        Reservasi
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <button class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors" id="mobile-menu-btn">
                    <i class="fas fa-bars text-2xl text-gray-600"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div class="lg:hidden hidden bg-white border-t border-gray-100 shadow-lg" id="mobile-menu">
            <div class="px-4 py-6 space-y-2">
                <a href="<?= base_url() ?>"
                    class="block py-3 px-4 text-gray-600 hover:bg-primary-50 hover:text-primary-800 rounded-lg font-medium transition-colors">Beranda</a>
                <a href="<?= base_url('tentang') ?>"
                    class="block py-3 px-4 text-gray-600 hover:bg-primary-50 hover:text-primary-800 rounded-lg font-medium transition-colors">Tentang
                    Kami</a>
                <a href="<?= base_url('layanan') ?>"
                    class="block py-3 px-4 text-gray-600 hover:bg-primary-50 hover:text-primary-800 rounded-lg font-medium transition-colors">Layanan</a>
                <a href="<?= base_url('terapis') ?>"
                    class="block py-3 px-4 text-gray-600 hover:bg-primary-50 hover:text-primary-800 rounded-lg font-medium transition-colors">Terapis</a>
                <a href="<?= base_url('blog') ?>"
                    class="block py-3 px-4 text-gray-600 hover:bg-primary-50 hover:text-primary-800 rounded-lg font-medium transition-colors">Blog</a>
                <a href="<?= base_url('kontak') ?>"
                    class="block py-3 px-4 text-gray-600 hover:bg-primary-50 hover:text-primary-800 rounded-lg font-medium transition-colors">Kontak</a>
                <a href="<?= base_url('reservasi') ?>"
                    class="block py-3 px-4 bg-gradient-to-r from-primary-800 to-accent-700 text-white text-center font-semibold rounded-lg mt-4">
                    <i class="fab fa-whatsapp mr-2"></i>Reservasi Sekarang
                </a>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if (isset($flash) && $flash): ?>
        <div class="fixed top-24 right-4 z-50 max-w-md" id="flash-message">
            <div
                class="<?= $flash['type'] === 'error' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-green-50 border-green-200 text-green-700' ?> px-6 py-4 rounded-xl border shadow-lg flex items-start space-x-3">
                <i class="fas <?= $flash['type'] === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle' ?> mt-0.5"></i>
                <div class="flex-1">
                    <p class="text-sm"><?= $flash['message'] ?></p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <script>
            setTimeout(() => {
                const flash = document.getElementById('flash-message');
                if (flash) flash.remove();
            }, 5000);
        </script>
    <?php endif; ?>

    <!-- Main Content -->
    <main>