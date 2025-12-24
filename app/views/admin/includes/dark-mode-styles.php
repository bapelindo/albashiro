<style>
    :root {
        --bg-primary: #f3f4f6;
        --bg-secondary: #ffffff;
        --text-primary: #1f2937;
        --text-secondary: #6b7280;
        --border-color: #e5e7eb;
    }

    [data-theme="dark"] {
        --bg-primary: #1f2937;
        --bg-secondary: #374151;
        --text-primary: #f9fafb;
        --text-secondary: #d1d5db;
        --border-color: #4b5563;
    }

    [data-theme="dark"] body {
        background-color: var(--bg-primary);
        color: var(--text-primary);
    }

    /* Background colors */
    [data-theme="dark"] .bg-white {
        background-color: var(--bg-secondary) !important;
    }

    [data-theme="dark"] .bg-gray-50 {
        background-color: #1f2937 !important;
    }

    [data-theme="dark"] .bg-gray-100 {
        background-color: #1f2937 !important;
    }

    [data-theme="dark"] .bg-gray-200 {
        background-color: #374151 !important;
    }

    /* Text colors */
    [data-theme="dark"] .text-gray-500,
    [data-theme="dark"] .text-gray-600 {
        color: var(--text-secondary) !important;
    }

    [data-theme="dark"] .text-gray-700,
    [data-theme="dark"] .text-gray-800,
    [data-theme="dark"] .text-gray-900 {
        color: var(--text-primary) !important;
    }

    /* Borders */
    [data-theme="dark"] .border-gray-100,
    [data-theme="dark"] .border-gray-200 {
        border-color: var(--border-color) !important;
    }

    /* Sidebar */
    [data-theme="dark"] aside {
        background-color: #1f2937 !important;
        border-right: 1px solid #374151;
    }

    /* Cards & Panels */
    [data-theme="dark"] .shadow-sm,
    [data-theme="dark"] .shadow-lg {
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.3) !important;
    }

    /* Tables */
    [data-theme="dark"] table thead {
        background-color: #1f2937 !important;
    }

    [data-theme="dark"] table tbody tr:hover {
        background-color: #374151 !important;
    }

    [data-theme="dark"] table tbody tr {
        border-color: #4b5563 !important;
    }

    /* Forms */
    [data-theme="dark"] input[type="text"],
    [data-theme="dark"] input[type="email"],
    [data-theme="dark"] input[type="password"],
    [data-theme="dark"] input[type="datetime-local"],
    [data-theme="dark"] input[type="file"],
    [data-theme="dark"] textarea,
    [data-theme="dark"] select {
        background-color: #374151 !important;
        border-color: #4b5563 !important;
        color: #f9fafb !important;
    }

    [data-theme="dark"] input::placeholder,
    [data-theme="dark"] textarea::placeholder {
        color: #9ca3af !important;
    }

    /* Buttons */
    [data-theme="dark"] .bg-gray-100:not(.bg-indigo-100):not(.bg-green-100):not(.bg-yellow-100) {
        background-color: #374151 !important;
        color: #f9fafb !important;
    }

    /* Hover states */
    [data-theme="dark"] .hover\:bg-gray-50:hover {
        background-color: #374151 !important;
    }

    [data-theme="dark"] .hover\:bg-gray-100:hover {
        background-color: #4b5563 !important;
    }

    /* TinyMCE */
    [data-theme="dark"] .tox .tox-edit-area__iframe {
        background-color: #374151 !important;
    }

    body {
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    /* Toast Notification */
    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 16px 24px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        z-index: 9999;
        animation: slideIn 0.3s ease-out;
        max-width: 400px;
    }

    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .toast.success {
        background: #10b981;
        color: white;
    }

    .toast.error {
        background: #ef4444;
        color: white;
    }

    .toast.info {
        background: #3b82f6;
        color: white;
    }

    /* Additional dark mode fixes */
    [data-theme="dark"] .rounded-2xl.bg-white,
    [data-theme="dark"] .rounded-xl.bg-white {
        background-color: #374151 !important;
    }

    [data-theme="dark"] .text-indigo-700 {
        color: #a5b4fc !important;
    }

    [data-theme="dark"] .bg-indigo-50 {
        background-color: #312e81 !important;
    }

    [data-theme="dark"] .bg-green-100 {
        background-color: #065f46 !important;
    }

    [data-theme="dark"] .bg-yellow-100 {
        background-color: #78350f !important;
    }

    [data-theme="dark"] .bg-red-50 {
        background-color: #7f1d1d !important;
    }

    [data-theme="dark"] .text-green-700 {
        color: #86efac !important;
    }

    [data-theme="dark"] .text-yellow-700 {
        color: #fde047 !important;
    }

    [data-theme="dark"] .text-red-600 {
        color: #fca5a5 !important;
    }
</style>