<!-- Sidebar -->
<aside class="fixed left-0 top-0 w-64 h-full bg-white shadow-lg z-50">
    <div class="p-6 border-b">
        <div class="flex items-center space-x-3">
            <div
                class="w-10 h-10 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-spa text-white"></i>
            </div>
            <div>
                <span class="font-bold text-gray-800"><?= SITE_NAME ?></span>
                <span class="block text-xs text-gray-500">Admin Panel</span>
            </div>
        </div>
    </div>

    <nav class="p-4 overflow-y-auto" style="max-height: calc(100vh - 120px);">
        <ul class="space-y-2">
            <li>
                <a href="<?= base_url('admin') ?>"
                    class="flex items-center px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 rounded-xl transition-colors <?= (isset($activeMenu) && $activeMenu === 'dashboard') ? 'bg-indigo-50 text-indigo-700' : '' ?>">
                    <i class="fas fa-home w-5 mr-3"></i>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="<?= base_url('admin/calendar') ?>"
                    class="flex items-center px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 rounded-xl transition-colors <?= (isset($activeMenu) && $activeMenu === 'calendar') ? 'bg-indigo-50 text-indigo-700' : '' ?>">
                    <i class="fas fa-calendar-alt w-5 mr-3"></i>
                    Calendar
                </a>
            </li>
            <li>
                <a href="<?= base_url('admin/availability') ?>"
                    class="flex items-center px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 rounded-xl transition-colors <?= (isset($activeMenu) && $activeMenu === 'availability') ? 'bg-indigo-50 text-indigo-700' : '' ?>">
                    <i class="fas fa-clock w-5 mr-3"></i>
                    Availability
                </a>
            </li>
            <li>
                <a href="<?= base_url('admin/bookings') ?>"
                    class="flex items-center px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 rounded-xl transition-colors <?= (isset($activeMenu) && $activeMenu === 'bookings') ? 'bg-indigo-50 text-indigo-700' : '' ?>">
                    <i class="fas fa-calendar-check w-5 mr-3"></i>
                    Reservasi
                </a>
            </li>
            <li>
                <a href="<?= base_url('admin/blog') ?>"
                    class="flex items-center px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 rounded-xl transition-colors <?= (isset($activeMenu) && $activeMenu === 'blog') ? 'bg-indigo-50 text-indigo-700' : '' ?>">
                    <i class="fas fa-newspaper w-5 mr-3"></i>
                    Blog
                </a>
            </li>
            <li>
                <a href="<?= base_url('admin/gallery') ?>"
                    class="flex items-center px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 rounded-xl transition-colors <?= (isset($activeMenu) && $activeMenu === 'gallery') ? 'bg-indigo-50 text-indigo-700' : '' ?>">
                    <i class="fas fa-images w-5 mr-3"></i>
                    Gallery
                </a>
            </li>
            <li>
                <a href="<?= base_url('admin/analytics') ?>"
                    class="flex items-center px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 rounded-xl transition-colors <?= (isset($activeMenu) && $activeMenu === 'analytics') ? 'bg-indigo-50 text-indigo-700' : '' ?>">
                    <i class="fas fa-chart-bar w-5 mr-3"></i>
                    Analytics
                </a>
            </li>
            <li>
                <a href="<?= base_url('admin/aiPerformance') ?>"
                    class="flex items-center px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 rounded-xl transition-colors <?= (isset($activeMenu) && $activeMenu === 'ai-performance') ? 'bg-indigo-50 text-indigo-700' : '' ?>">
                    <i class="fas fa-tachometer-alt w-5 mr-3"></i>
                    AI Performance
                </a>
            </li>
            <li>
                <a href="<?= base_url('admin/knowledge_suggestions') ?>"
                    class="flex items-center px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 rounded-xl transition-colors <?= (isset($activeMenu) && $activeMenu === 'knowledge') ? 'bg-indigo-50 text-indigo-700' : '' ?>">
                    <i class="fas fa-brain w-5 mr-3"></i>
                    Auto-Learning
                    <?php
                    // Show notification badge if there are pending suggestions
                    try {
                        $db = Database::getInstance();
                        $pendingCount = $db->query("SELECT COUNT(*) as count FROM knowledge_suggestions WHERE status = 'pending'")->fetch();
                        if ($pendingCount && $pendingCount->count > 0) {
                            echo '<span class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full">' . $pendingCount->count . '</span>';
                        }
                    } catch (Exception $e) {
                        // Silently fail if table doesn't exist yet
                    }
                    ?>
                </a>
            </li>
            <li class="pt-4 border-t mt-4">
                <a href="<?= base_url() ?>" target="_blank"
                    class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-xl transition-colors">
                    <i class="fas fa-external-link-alt w-5 mr-3"></i>
                    Lihat Website
                </a>
            </li>
            <li>
                <a href="<?= base_url('auth/logout') ?>"
                    class="flex items-center px-4 py-3 text-red-600 hover:bg-red-50 rounded-xl transition-colors">
                    <i class="fas fa-sign-out-alt w-5 mr-3"></i>
                    Logout
                </a>
            </li>
        </ul>
        <?php include __DIR__ . '/dark-mode-toggle.php'; ?>
    </nav>
</aside>