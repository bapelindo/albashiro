<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - Admin Albashiroh</title>
    <style>
        <?php include __DIR__ . '/../includes/dark-mode-styles.php'; ?>
    </style>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-50">
    <!-- Admin Header -->
    <nav class="bg-gradient-to-r from-purple-800 to-indigo-700 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-8">
                    <a href="<?= base_url('admin') ?>" class="text-xl font-bold">
                        <i class="fas fa-mosque mr-2"></i>Albashiroh Admin
                    </a>
                    <div class="hidden md:flex space-x-4">
                        <a href="<?= base_url('admin') ?>" class="hover:bg-purple-700 px-3 py-2 rounded-md">
                            <i class="fas fa-home mr-1"></i>Dashboard
                        </a>
                        <a href="<?= base_url('admin/calendar') ?>" class="hover:bg-purple-700 px-3 py-2 rounded-md">
                            <i class="fas fa-calendar-alt mr-1"></i>Calendar
                        </a>
                        <a href="<?= base_url('admin/bookings') ?>" class="hover:bg-purple-700 px-3 py-2 rounded-md">
                            <i class="fas fa-calendar-check mr-1"></i>Reservasi
                        </a>
                        <a href="<?= base_url('admin/blog') ?>" class="hover:bg-purple-700 px-3 py-2 rounded-md">
                            <i class="fas fa-blog mr-1"></i>Blog
                        </a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm"><?= e($user->name) ?></span>
                    <a href="<?= base_url('auth/logout') ?>"
                        class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-md text-sm">
                        <i class="fas fa-sign-out-alt mr-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
        <?php include __DIR__ . '/../includes/dark-mode-toggle.php'; ?>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold text-gray-900">
                    <i class="fas fa-calendar-check text-purple-600 mr-2"></i><?= $title ?>
                </h1>
                <a href="<?= base_url('admin/exportBookings?status=' . $currentStatus) ?>"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-file-export mr-2"></i>Export CSV
                </a>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (isset($flash) && $flash): ?>
            <div class="mb-6">
                <div
                    class="<?= $flash['type'] === 'error' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-green-50 border-green-200 text-green-700' ?> px-6 py-4 rounded-lg border flex items-start space-x-3">
                    <i
                        class="fas <?= $flash['type'] === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle' ?> mt-0.5"></i>
                    <p><?= $flash['message'] ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Filter Tabs -->
        <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="flex border-b">
                <a href="<?= base_url('admin/bookings?status=all') ?>"
                    class="px-6 py-3 <?= $currentStatus === 'all' ? 'border-b-2 border-purple-600 text-purple-600 font-semibold' : 'text-gray-600 hover:text-purple-600' ?>">
                    <i class="fas fa-list mr-2"></i>Semua
                </a>
                <a href="<?= base_url('admin/bookings?status=pending') ?>"
                    class="px-6 py-3 <?= $currentStatus === 'pending' ? 'border-b-2 border-purple-600 text-purple-600 font-semibold' : 'text-gray-600 hover:text-purple-600' ?>">
                    <i class="fas fa-clock mr-2"></i>Pending
                </a>
                <a href="<?= base_url('admin/bookings?status=confirmed') ?>"
                    class="px-6 py-3 <?= $currentStatus === 'confirmed' ? 'border-b-2 border-purple-600 text-purple-600 font-semibold' : 'text-gray-600 hover:text-purple-600' ?>">
                    <i class="fas fa-check mr-2"></i>Dikonfirmasi
                </a>
                <a href="<?= base_url('admin/bookings?status=completed') ?>"
                    class="px-6 py-3 <?= $currentStatus === 'completed' ? 'border-b-2 border-purple-600 text-purple-600 font-semibold' : 'text-gray-600 hover:text-purple-600' ?>">
                    <i class="fas fa-check-double mr-2"></i>Selesai
                </a>
                <a href="<?= base_url('admin/bookings?status=cancelled') ?>"
                    class="px-6 py-3 <?= $currentStatus === 'cancelled' ? 'border-b-2 border-purple-600 text-purple-600 font-semibold' : 'text-gray-600 hover:text-purple-600' ?>">
                    <i class="fas fa-times mr-2"></i>Dibatalkan
                </a>
            </div>
        </div>

        <!-- Bookings Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Booking Code
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Client
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Therapist
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date & Time
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($bookings)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-2"></i>
                                    <p>Tidak ada reservasi</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($bookings as $booking): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="text-sm font-mono font-semibold text-purple-600"><?= e($booking->booking_code) ?></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900"><?= e($booking->client_name) ?></div>
                                        <div class="text-sm text-gray-500">
                                            <i class="fab fa-whatsapp mr-1"></i><?= e($booking->wa_number) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900"><?= e($booking->therapist_name) ?></div>
                                        <?php if ($booking->service_name): ?>
                                            <div class="text-xs text-gray-500"><?= e($booking->service_name) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <i
                                                class="fas fa-calendar mr-1"></i><?= date('d/m/Y', strtotime($booking->appointment_date)) ?>
                                        </div>
                                        <?php if ($booking->appointment_time): ?>
                                            <div class="text-sm text-gray-500">
                                                <i class="fas fa-clock mr-1"></i><?= substr($booking->appointment_time, 0, 5) ?> WIB
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'confirmed' => 'bg-blue-100 text-blue-800',
                                            'completed' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800'
                                        ];
                                        $statusLabels = [
                                            'pending' => 'Pending',
                                            'confirmed' => 'Dikonfirmasi',
                                            'completed' => 'Selesai',
                                            'cancelled' => 'Dibatalkan'
                                        ];
                                        ?>
                                        <span
                                            class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusColors[$booking->status] ?>">
                                            <?= $statusLabels[$booking->status] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="<?= base_url('admin/bookingDetail/' . $booking->booking_code) ?>"
                                            class="text-purple-600 hover:text-purple-900 mr-3">
                                            <i class="fas fa-eye mr-1"></i>Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php include __DIR__ . '/../includes/dark-mode-script.php'; ?>
</body>

</html>