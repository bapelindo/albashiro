<!-- Dark Mode Toggle -->
<div class="px-4 py-3 border-t mt-4">
    <button id="dark-mode-toggle"
        class="flex items-center justify-between w-full px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-xl transition-colors">
        <span class="flex items-center">
            <i class="fas fa-moon w-5 mr-3"></i>
            <span>Dark Mode</span>
        </span>
        <div class="relative inline-block w-10 h-6">
            <input type="checkbox" id="dark-mode-checkbox" class="sr-only">
            <div class="toggle-bg bg-gray-300 rounded-full h-6 w-10 transition-colors"></div>
            <div class="toggle-dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform"></div>
        </div>
    </button>
</div>