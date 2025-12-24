<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> | <?= SITE_NAME ?> Admin</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md p-6">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div
                class="w-16 h-16 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                <i class="fas fa-spa text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800"><?= SITE_NAME ?></h1>
            <p class="text-gray-500 text-sm">Admin Panel</p>
        </div>

        <!-- Flash Messages -->
        <?php if (isset($flash) && $flash): ?>
            <div
                class="mb-6 p-4 <?= $flash['type'] === 'error' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-green-50 border-green-200 text-green-700' ?> rounded-lg border">
                <p class="text-sm"><?= $flash['message'] ?></p>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-6 text-center">Login Admin</h2>

            <form action="<?= base_url('auth/login') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" name="email" required placeholder="admin@albashiro.com"
                                class="w-full pl-12 pr-4 py-3 border border-gray-200 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" name="password" required placeholder="••••••••"
                                class="w-full pl-12 pr-4 py-3 border border-gray-200 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all">
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Login
                    </button>
                </div>
            </form>
        </div>

        <!-- Back to Site -->
        <div class="text-center mt-6">
            <a href="<?= base_url() ?>" class="text-gray-500 hover:text-indigo-600 text-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Website
            </a>
        </div>
    </div>
</body>

</html>