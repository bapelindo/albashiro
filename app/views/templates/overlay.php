<!-- Audio Consent Overlay (Premium Component - Site Theme V7 - Optimized) -->
<style>
    /* Import Google Fonts directly to ensure they load if not already cached */
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap');

    #welcome-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        /* Light Overlay Background with Reduced Blur (1px per request) */
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(1px);
        -webkit-backdrop-filter: blur(1px);
        z-index: 2147483647;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        opacity: 0;
        transition: opacity 1.2s ease-out;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    /* The "Kotak" (Card) - Matching .glass and .skeleton-card styles */
    .overlay-card {
        position: relative;
        text-align: center;
        padding: 3.5rem 2.5rem;
        max-width: 550px;
        width: 90%;
        display: flex;
        flex-direction: column;
        align-items: center;

        /* Glass Effect matching site .glass class */
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(255, 255, 255, 1);
        border-radius: 24px;
        /* Soft shadow matching .hover-lift hover state style */
        box-shadow: 0 25px 50px rgba(30, 58, 95, 0.15);
        backdrop-filter: blur(20px);
    }

    /* Islamic Pattern Background - Matches .islamic-pattern */
    .overlay-pattern {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        /* Authentic Pattern from Custom CSS */
        background-image: url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M40 0L40 80M0 40L80 40' stroke='%236b5b95' stroke-width='0.3' fill='none' opacity='0.08'/%3E%3Ccircle cx='40' cy='40' r='20' stroke='%236b5b95' stroke-width='0.3' fill='none' opacity='0.05'/%3E%3C/svg%3E");
        z-index: 0;
        border-radius: 24px;
        pointer-events: none;
    }

    /* Logo Styling */
    .overlay-logo-container {
        position: relative;
        z-index: 1;
        width: 130px;
        height: 130px;
        margin-bottom: 2rem;
        margin-top: -1rem;

        /* White Halo */
        background: #ffffff;
        border-radius: 50%;
        padding: 6px;
        box-shadow: 0 10px 30px rgba(107, 91, 149, 0.15);
        /* Tinted Shadow */
        animation: pulse-glow 3s infinite ease-in-out;
    }

    .overlay-logo-img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    /* Title - Matches .gradient-text */
    .overlay-title {
        position: relative;
        z-index: 1;
        font-family: 'Playfair Display', serif;
        font-size: 3rem;
        font-weight: 700;
        line-height: 1.2;

        /* Site Gradient: Blue #1e3a5f -> Purple #6b5b95 -> Gold #c9a959 */
        background: linear-gradient(135deg, #1e3a5f 0%, #6b5b95 50%, #c9a959 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;

        margin-bottom: 0.5rem;
    }

    .overlay-subtitle-main {
        position: relative;
        z-index: 1;
        font-family: 'Playfair Display', serif;
        font-size: 1.5rem;
        font-weight: 600;
        font-style: italic;
        color: #1e3a5f;
        /* Primary Blue */
        margin-bottom: 1.5rem;
    }

    .overlay-text-body {
        position: relative;
        z-index: 1;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 1rem;
        color: #4b5563;
        /* Gray-600 */
        margin-bottom: 2.5rem;
        line-height: 1.6;
        max-width: 85%;
    }

    /* Button - Matches Site Primary Theme */
    .overlay-btn {
        position: relative;
        z-index: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 1rem 3rem;

        /* Primary Purple Background */
        background: #6b5b95;
        border: none;
        border-radius: 9999px;

        color: #ffffff;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 600;
        font-size: 0.95rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        cursor: pointer;

        box-shadow: 0 10px 20px rgba(107, 91, 149, 0.25);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .overlay-btn:hover {
        background: #5b4a85;
        /* Darker Purple hover */
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(107, 91, 149, 0.35);
    }

    /* Animations */
    .animate-in {
        opacity: 0;
        transform: translateY(20px);
        animation: fadeInUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
    }

    .delay-1 {
        animation-delay: 0.2s;
    }

    .delay-2 {
        animation-delay: 0.4s;
    }

    .delay-3 {
        animation-delay: 0.6s;
    }

    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes pulse-glow {

        0%,
        100% {
            transform: scale(1);
            box-shadow: 0 10px 30px rgba(107, 91, 149, 0.15);
        }

        50% {
            transform: scale(1.02);
            box-shadow: 0 15px 40px rgba(107, 91, 149, 0.25);
        }
    }
</style>

<div id="welcome-overlay">
    <!-- The "Kotak" (Card) -->
    <div class="overlay-card">
        <div class="overlay-pattern"></div>

        <!-- Logo -->
        <div class="overlay-logo-container animate-in">
            <img src="<?= base_url('public/images/favicon.jpg') ?>" alt="Albashiro Logo" class="overlay-logo-img">
        </div>

        <h1 class="overlay-title animate-in delay-1">Ahlan Wa Sahlan</h1>

        <div class="overlay-subtitle-main animate-in delay-2">
            Sentuhan Kedamaian Hati
        </div>

        <div class="overlay-text-body animate-in delay-2">
            Nikmati pengalaman spiritual yang menenangkan dengan mengaktifkan audio.
        </div>

        <button id="welcome-enter-btn" class="overlay-btn animate-in delay-3">
            <span>Masuk Website</span>
        </button>
    </div>
</div>

<script>
    (function () {
        // Run immediately
        const overlay = document.getElementById('welcome-overlay');
        const btn = document.getElementById('welcome-enter-btn');

        // SMART SESSION LOGIC
        try {
            const nav = performance.getEntriesByType("navigation");
            if (nav.length > 0 && nav[0].type === "reload") {
                sessionStorage.removeItem('has_entered_site');
            }
        } catch (e) { }

        const hasEntered = sessionStorage.getItem('has_entered_site') === 'true';

        if (!hasEntered) {
            document.body.style.overflow = 'hidden';
            window.addEventListener('load', () => {
                setTimeout(() => {
                    overlay.style.display = 'flex';
                    overlay.style.opacity = '1';
                    const loader = document.getElementById('page-loader');
                    if (loader) loader.style.display = 'none';
                }, 1000);
            });
        } else {
            overlay.style.display = 'none';
        }

        if (btn) {
            btn.addEventListener('click', () => {
                // Event Bus Pattern: Trigger Global Music Request
                // This ensures footer.php handles lazy loading the audio track safely.
                window.dispatchEvent(new CustomEvent('request-music-start'));

                // Cleanup Overlay immediately
                sessionStorage.setItem('has_entered_site', 'true');
                overlay.style.opacity = '0';
                overlay.style.pointerEvents = 'none';
                document.body.style.overflow = '';
                setTimeout(() => {
                    overlay.style.display = 'none';
                }, 1200);
            });
        }
    })();
</script>