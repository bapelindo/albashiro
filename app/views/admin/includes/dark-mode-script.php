<script>
    // ===== DARK MODE =====
    const darkModeToggle = document.getElementById('dark-mode-toggle');
    const darkModeCheckbox = document.getElementById('dark-mode-checkbox');

    // Load saved theme
    const savedTheme = localStorage.getItem('adminTheme') || 'light';
    if (savedTheme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
        if (darkModeCheckbox) darkModeCheckbox.checked = true;
        const toggleBg = document.querySelector('.toggle-bg');
        const toggleDot = document.querySelector('.toggle-dot');
        if (toggleBg) toggleBg.classList.add('bg-indigo-600');
        if (toggleDot) toggleDot.style.transform = 'translateX(16px)';
    }

    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('adminTheme', newTheme);
            if (darkModeCheckbox) darkModeCheckbox.checked = newTheme === 'dark';

            const toggleBg = document.querySelector('.toggle-bg');
            const toggleDot = document.querySelector('.toggle-dot');

            if (newTheme === 'dark') {
                if (toggleBg) toggleBg.classList.add('bg-indigo-600');
                if (toggleDot) toggleDot.style.transform = 'translateX(16px)';
            } else {
                if (toggleBg) toggleBg.classList.remove('bg-indigo-600');
                if (toggleDot) toggleDot.style.transform = 'translateX(0)';
            }

            if (typeof showToast === 'function') {
                showToast(`Dark mode ${newTheme === 'dark' ? 'enabled' : 'disabled'}`, 'info');
            }
        });
    }

    // ===== TOAST NOTIFICATIONS =====
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} mr-3"></i>
                <span>${message}</span>
            </div>
        `;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
</script>