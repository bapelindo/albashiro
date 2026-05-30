<!DOCTYPE html>
<html lang="id" class="scroll-smooth overflow-x-hidden">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google-site-verification" content="LO79aX08NpEkKkXAqI0NyCk6LAubHGmNbXTjBOQZ8vM" />

    <!-- DNS Preconnect for Faster Loading (Core Web Vitals) -->
    <link rel="preconnect" href="https://cdn.tailwindcss.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>

    <!-- Fallback Prefetch -->
    <link rel="dns-prefetch" href="https://cdn.tailwindcss.com">
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://unpkg.com">

    <!-- SEO Meta Tags -->
    <meta name="description"
        content="<?= e($meta_description ?? 'Klinik Kamu - Pusat Hypnotherapy Islami & Spiritual Hypnotherapy di Jakarta. Temukan kedamaian jiwa, atasi trauma, dan kecemasan dengan terapi berlandaskan nilai syariah dan Tauhid bersama ahlinya di Klinik Kamu.') ?>">
    <meta name="keywords"
        content="<?= e($meta_keywords ?? 'Klinik Kamu, klinik-kamu, klinik-kamu terdekat, pusat hipnoterapi klinik-kamu, hipnoterapi islami, spiritual hypnotherapy, hipnoterapi syariah, ruqyah syariah terpadu, konseling islam, psikologi islam, terapi trauma jakarta, atasi kecemasan, pengobatan mental islami') ?>">
    <meta name="author" content="Tim Klinik Kamu - <?= SITE_NAME ?>">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">

    <!-- Internationalization / Geo-targeting -->
    <link rel="alternate" hreflang="id-ID"
        href="<?= e($canonical_url ?? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") ?>" />
    <link rel="alternate" hreflang="x-default"
        href="<?= e($canonical_url ?? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") ?>" />

    <link rel="canonical"
        href="<?= e($canonical_url ?? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:site_name" content="Klinik Kamu">
    <meta property="og:type" content="website">
    <meta property="og:url"
        content="<?= e($canonical_url ?? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") ?>">
    <meta property="og:title" content="<?= e($title ?? 'Beranda') ?> | Klinik Kamu - <?= SITE_NAME ?>">
    <meta property="og:description"
        content="<?= e($meta_description ?? 'Klinik Kamu: Spesialis Islamic Spiritual Hypnotherapy. Solusi tepat untuk kesehatan mental dengan pendekatan holistik Islami. Hubungi Klinik Kamu sekarang.') ?>">
    <meta property="og:image" content="<?= e($og_image ?? base_url('public/images/og-image.jpg')) ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Klinik Klinik Kamu - Islamic Hypnotherapy">
    <meta property="og:locale" content="id_ID">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url"
        content="<?= e($canonical_url ?? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") ?>">
    <meta name="twitter:title" content="<?= e($title ?? 'Beranda') ?> | Klinik Kamu">
    <meta name="twitter:description"
        content="<?= e($meta_description ?? 'Klinik Kamu: Spesialis Islamic Spiritual Hypnotherapy. Temukan kedamaian dan solusi kesehatan mental berlandaskan tauhid.') ?>">
    <meta name="twitter:image" content="<?= e($og_image ?? base_url('public/images/og-image.jpg')) ?>">

    <meta name="csrf-token" content="<?= csrf_token() ?>">

    <!-- PWA & Mobile Optimization -->
    <meta name="theme-color" content="#1e3a5f">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Klinik Kamu">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= base_url('public/images/favicon.svg') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('public/images/favicon.svg') ?>">

    <title><?= e($title ?? 'Beranda') ?> | Klinik Kamu - <?= SITE_TAGLINE ?></title>

    <!-- JSON-LD Structured Data Schema Markup (World-Class SEO) -->
    <script type="application/ld+json">
    [{
      "@context": "https://schema.org",
      "@type": "WebSite",
      "name": "Klinik Kamu",
      "alternateName": "Klinik Kamu Islamic Hypnotherapy",
      "url": "<?= base_url() ?>",
      "potentialAction": {
        "@type": "SearchAction",
        "target": "<?= base_url() ?>search?q={search_term_string}",
        "query-input": "required name=search_term_string"
      }
    },
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "Klinik Kamu",
      "url": "<?= base_url() ?>",
      "logo": "<?= base_url('public/images/logo.png') ?>",
      "contactPoint": {
        "@type": "ContactPoint",
        "telephone": "<?= ADMIN_WHATSAPP ?>",
        "contactType": "Customer Service",
        "areaServed": "ID",
        "availableLanguage": "Indonesian"
      },
      "sameAs": [
        "https://www.facebook.com/klinik-kamu",
        "https://www.instagram.com/klinik-kamu",
        "https://www.tiktok.com/@klinik-kamu",
        "https://www.youtube.com/@klinik-kamu"
      ]
    },
    {
      "@context": "https://schema.org",
      "@type": "HealthAndBeautyBusiness",
      "name": "Klinik Kamu Islamic Spiritual Hypnotherapy",
      "image": [
        "<?= base_url('public/images/logo.png') ?>",
        "<?= base_url('public/images/og-image.jpg') ?>"
      ],
      "@id": "<?= base_url() ?>#localbusiness",
      "url": "<?= base_url() ?>",
      "telephone": "<?= ADMIN_WHATSAPP ?>",
      "priceRange": "$$",
      "description": "Pusat Hypnotherapy Islami terpercaya di Jakarta. Klinik Kamu membantu mengatasi masalah psikologis, trauma, dan kecemasan dengan pendekatan spiritual syariah.",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "Jl. Klinik Kamu No. 1",
        "addressLocality": "Jakarta",
        "postalCode": "12345",
        "addressCountry": "ID"
      },
      "geo": {
        "@type": "GeoCoordinates",
        "latitude": -6.2088,
        "longitude": 106.8456
      },
      "openingHoursSpecification": {
        "@type": "OpeningHoursSpecification",
        "dayOfWeek": [
          "Monday",
          "Tuesday",
          "Wednesday",
          "Thursday",
          "Friday",
          "Saturday"
        ],
        "opens": "09:00",
        "closes": "17:00"
      },
      "sameAs": [
        "https://www.facebook.com/klinik-kamu",
        "https://www.instagram.com/klinik-kamu"
      ]
    }]
    </script>

    <!-- Tailwind CSS CDN (Development only - compile for production) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Global BASE_URL for JavaScript -->
    <script>
        window.BASE_URL = '<?= base_url() ?>';
    </script>

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



    <!-- Local Fonts CSS -->
    <link rel="stylesheet" href="<?= base_url('public/css/fonts.css') ?>">

    <!-- Font Awesome - Local -->
    <link rel="stylesheet" href="<?= base_url('public/css/fontawesome.min.css') ?>">

    <!-- Swiper CSS - Local -->
    <link rel="stylesheet" href="<?= base_url('public/css/swiper-bundle.min.css') ?>">

    <!-- AOS Animation - Local -->
    <link rel="stylesheet" href="<?= base_url('public/css/aos.css') ?>">

    <!-- Custom Styles - Local -->
    <link rel="stylesheet" href="<?= base_url('public/css/custom.css') ?>">
</head>

<body class="font-sans antialiased text-gray-800 bg-cream-50 overflow-x-hidden w-full">

    <!-- Inline Page Loader - Shows Immediately -->
    <div id="page-loader">
        <div class="loader-content">
            <div class="loader-logo">
                <!-- Animated circles background -->
                <div class="loader-circle-1"></div>
                <div class="loader-circle-2"></div>

                <!-- Inline SVG logo optimized - langsung ter-load tanpa HTTP request -->
                <img src="<?= base_url('public/images/logo.svg') ?>" alt="Klinik Kamu Logo" class="w-full h-full object-contain">
            </div>
            <p class="loader-title"><?= SITE_NAME ?></p>
            <p class="loader-subtitle">Islamic Spiritual Hypnotherapy</p>
            <div class="loader-dots">
                <div class="dot-pulse-1"></div>
                <div class="dot-pulse-2"></div>
                <div class="dot-pulse-3"></div>
            </div>
        </div>
    </div>


    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 glass border-b border-gray-100" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <a href="<?= base_url() ?>" class="flex items-center space-x-3 group">
                    <div
                        class="w-12 h-12 rounded-xl overflow-hidden shadow-lg group-hover:shadow-xl transition-shadow bg-white p-1.5">
                        <!-- Inline SVG Logo - No HTTP request needed -->
                        <img src="<?= base_url('public/images/logo.svg') ?>" alt="Klinik Kamu Logo" class="w-full h-full object-contain">
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
                    <a href="<?= base_url('galeri') ?>"
                        class="px-4 py-2 text-gray-600 hover:text-primary-800 hover:bg-primary-50 rounded-lg font-medium transition-all <?= ($title ?? '') === 'Galeri' ? 'text-primary-800 bg-primary-50' : '' ?>">Galeri</a>
                    <a href="<?= base_url('blog') ?>"
                        class="px-4 py-2 text-gray-600 hover:text-primary-800 hover:bg-primary-50 rounded-lg font-medium transition-all <?= ($title ?? '') === 'Blog' || str_contains($title ?? '', 'Blog') ? 'text-primary-800 bg-primary-50' : '' ?>">Blog</a>
                    <a href="<?= base_url('kontak') ?>"
                        class="px-4 py-2 text-gray-600 hover:text-primary-800 hover:bg-primary-50 rounded-lg font-medium transition-all <?= ($title ?? '') === 'Kontak' ? 'text-primary-800 bg-primary-50' : '' ?>">Hubungi Kami</a>
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
                <a href="<?= base_url('galeri') ?>"
                    class="block py-3 px-4 text-gray-600 hover:bg-primary-50 hover:text-primary-800 rounded-lg font-medium transition-colors">Galeri</a>
                <a href="<?= base_url('blog') ?>"
                    class="block py-3 px-4 text-gray-600 hover:bg-primary-50 hover:text-primary-800 rounded-lg font-medium transition-colors">Blog</a>
                <a href="<?= base_url('kontak') ?>"
                    class="block py-3 px-4 text-gray-600 hover:bg-primary-50 hover:text-primary-800 rounded-lg font-medium transition-colors">Hubungi Kami</a>
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

    <?php endif; ?>

    <!-- Main Content -->
    <main>