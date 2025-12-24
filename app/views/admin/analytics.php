<?php
/**
 * Albashiro - Analytics Dashboard
 */

// Check authentication
if (!isset($_SESSION['user_id'])) {
    redirect('auth/login');
}

// Get analytics data
$bookingModel = $this->model('Booking');
$allBookings = $bookingModel->getAll();

// Calculate statistics
$totalBookings = count($allBookings);
$statusCounts = [
    'pending' => 0,
    'confirmed' => 0,
    'completed' => 0,
    'cancelled' => 0
];

$therapistStats = [];
$monthlyBookings = [];

foreach ($allBookings as $booking) {
    // Status counts
    if (isset($statusCounts[$booking->status])) {
        $statusCounts[$booking->status]++;
    }

    // Therapist stats
    if (!isset($therapistStats[$booking->therapist_name])) {
        $therapistStats[$booking->therapist_name] = 0;
    }
    $therapistStats[$booking->therapist_name]++;

    // Monthly bookings
    $month = date('M', strtotime($booking->created_at));
    if (!isset($monthlyBookings[$month])) {
        $monthlyBookings[$month] = 0;
    }
    $monthlyBookings[$month]++;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> | <?= SITE_NAME ?> Admin</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
    <?php include __DIR__ . '/includes/dark-mode-styles.php'; ?>
</head>

<body class="bg-gray-100 min-h-screen">
    <?php
    $activeMenu = 'analytics';
    include __DIR__ . '/includes/sidebar.php';
    ?>

    <!-- Main Content -->
    <main class="ml-64 p-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-800"><?= e($title) ?></h1>
                <p class="text-gray-500 text-sm">Booking statistics and insights</p>
            </div>
            <div class="flex items-center space-x-3">
                <select id="period-filter" class="px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="all">All Time</option>
                    <option value="month">This Month</option>
                    <option value="week">This Week</option>
                </select>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Bookings</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-1"><?= $totalBookings ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-calendar-check text-indigo-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Pending</p>
                        <h3 class="text-3xl font-bold text-yellow-600 mt-1"><?= $statusCounts['pending'] ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Confirmed</p>
                        <h3 class="text-3xl font-bold text-blue-600 mt-1"><?= $statusCounts['confirmed'] ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check-circle text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Completed</p>
                        <h3 class="text-3xl font-bold text-green-600 mt-1"><?= $statusCounts['completed'] ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check-double text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid md:grid-cols-2 gap-6 mb-8">
            <!-- Status Distribution -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Status Distribution</h2>
                <canvas id="statusChart"></canvas>
            </div>

            <!-- Monthly Trend -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Monthly Bookings</h2>
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        <!-- Therapist Performance -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Therapist Performance</h2>
            <canvas id="therapistChart"></canvas>
        </div>
    </main>

    <script>
        // Status Distribution Pie Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Confirmed', 'Completed', 'Cancelled'],
                datasets: [{
                    data: [
                        <?= $statusCounts['pending'] ?>,
                        <?= $statusCounts['confirmed'] ?>,
                        <?= $statusCounts['completed'] ?>,
                        <?= $statusCounts['cancelled'] ?>
                    ],
                    backgroundColor: ['#FCD34D', '#60A5FA', '#34D399', '#F87171']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Monthly Trend Line Chart
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_keys($monthlyBookings)) ?>,
                datasets: [{
                    label: 'Bookings',
                    data: <?= json_encode(array_values($monthlyBookings)) ?>,
                    borderColor: '#6366F1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Therapist Performance Bar Chart
        const therapistCtx = document.getElementById('therapistChart').getContext('2d');
        new Chart(therapistCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_keys($therapistStats)) ?>,
                datasets: [{
                    label: 'Total Bookings',
                    data: <?= json_encode(array_values($therapistStats)) ?>,
                    backgroundColor: '#8B5CF6'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>

    <?php include __DIR__ . '/includes/dark-mode-script.php'; ?>
</body>

</html>