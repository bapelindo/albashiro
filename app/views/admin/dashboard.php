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
    <?php include __DIR__ . '/includes/dark-mode-styles.php'; ?>
</head>

<body class="bg-gray-100 min-h-screen">
    <?php
    $activeMenu = 'dashboard';
    include __DIR__ . '/includes/sidebar.php';
    ?>

    <!-- Main Content -->
    <main class="ml-64 p-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-800"><?= e($title) ?></h1>
                <p class="text-gray-500 text-sm">Selamat datang, <?= e($user->name ?? 'Admin') ?></p>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-500"><?= date('d M Y, H:i') ?></span>
                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-indigo-600"></i>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Artikel</p>
                        <p class="text-3xl font-bold text-gray-800"><?= count($posts) ?></p>
                    </div>
                    <div class="w-14 h-14 bg-indigo-100 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-newspaper text-2xl text-indigo-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Reservasi</p>
                        <p class="text-3xl font-bold text-gray-800"><?= count($bookings) ?></p>
                    </div>
                    <div class="w-14 h-14 bg-green-100 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-calendar-check text-2xl text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Reservasi Pending</p>
                        <p class="text-3xl font-bold text-gray-800">
                            <?= count(array_filter($bookings, fn($b) => $b->status === 'pending')) ?>
                        </p>
                    </div>
                    <div class="w-14 h-14 bg-yellow-100 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-clock text-2xl text-yellow-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-2xl p-6 shadow-sm mb-8">
            <h2 class="font-semibold text-gray-800 mb-4">Aksi Cepat</h2>
            <div class="flex flex-wrap gap-4">
                <a href="<?= base_url('admin/create') ?>"
                    class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Tulis Artikel Baru
                </a>
                <a href="<?= base_url('admin/bookings') ?>"
                    class="inline-flex items-center px-6 py-3 bg-green-600 text-white font-medium rounded-xl hover:bg-green-700 transition-colors">
                    <i class="fas fa-calendar-check mr-2"></i>
                    Kelola Reservasi
                </a>
                <a href="<?= base_url('admin/blog') ?>"
                    class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition-colors">
                    <i class="fas fa-list mr-2"></i>
                    Kelola Blog
                </a>
            </div>
        </div>

        <!-- Recent Posts -->
        <div class="mt-8">
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h2 class="font-semibold text-gray-800 mb-4">Artikel Terbaru</h2>
                <?php if (empty($posts)): ?>
                    <p class="text-gray-500 text-center py-8">Belum ada artikel</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-3 px-4 text-gray-600 font-medium">Judul</th>
                                    <th class="text-left py-3 px-4 text-gray-600 font-medium">Status</th>
                                    <th class="text-left py-3 px-4 text-gray-600 font-medium">Views</th>
                                    <th class="text-left py-3 px-4 text-gray-600 font-medium">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($posts, 0, 5) as $post): ?>
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="py-3 px-4">
                                            <a href="<?= base_url('admin/edit/' . $post->id) ?>"
                                                class="text-gray-800 hover:text-indigo-600 font-medium">
                                                <?= e($post->title) ?>
                                            </a>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span
                                                class="px-3 py-1 text-xs font-medium rounded-full <?= $post->status === 'published' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
                                                <?= $post->status === 'published' ? 'Published' : 'Draft' ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-gray-600"><?= number_format($post->views) ?></td>
                                        <td class="py-3 px-4 text-gray-600"><?= date('d M Y', strtotime($post->created_at)) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Booking Details Modal -->
    <div id="booking-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-800">Booking Details</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <div id="booking-details" class="p-6">
                <!-- Details will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        let calendar;

        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');

            if (calendarEl) {
                calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    events: function (info, successCallback, failureCallback) {
                        const therapistId = document.getElementById('therapist-filter').value;
                        const url = '<?= base_url('admin/getCalendarEvents') ?>?start=' + info.startStr + '&end=' + info.endStr +
                            (therapistId ? '&therapist_id=' + therapistId : '');

                        fetch(url)
                            .then(response => response.json())
                            .then(data => successCallback(data))
                            .catch(error => {
                                console.error('Error loading events:', error);
                                failureCallback(error);
                            });
                    },
                    eventClick: function (info) {
                        showBookingDetails(info.event.id);
                    },
                    eventTimeFormat: {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false
                    },
                    height: 'auto',
                    slotMinTime: '08:00:00',
                    slotMaxTime: '18:00:00'
                });

                calendar.render();

                // Therapist filter change
                document.getElementById('therapist-filter').addEventListener('change', function () {
                    calendar.refetchEvents();
                });
            }
        });

        function showBookingDetails(bookingId) {
            fetch('<?= base_url('admin/getBookingDetails/') ?>' + bookingId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const booking = data.booking;
                        const statusColors = {
                            'pending': 'bg-yellow-100 text-yellow-800',
                            'confirmed': 'bg-blue-100 text-blue-800',
                            'completed': 'bg-green-100 text-green-800',
                            'cancelled': 'bg-red-100 text-red-800'
                        };

                        document.getElementById('booking-details').innerHTML = `
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Booking Code</span>
                                    <span class="font-mono font-semibold text-indigo-600">${booking.booking_code}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Status</span>
                                    <span class="px-3 py-1 rounded-full text-xs font-medium ${statusColors[booking.status]}">${booking.status}</span>
                                </div>
                                <hr>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Client Information</h4>
                                    <div class="space-y-2">
                                        <div class="flex items-center">
                                            <i class="fas fa-user w-6 text-gray-400"></i>
                                            <span>${booking.client_name}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <i class="fab fa-whatsapp w-6 text-gray-400"></i>
                                            <span>${booking.wa_number}</span>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Appointment Details</h4>
                                    <div class="space-y-2">
                                        <div class="flex items-center">
                                            <i class="fas fa-user-md w-6 text-gray-400"></i>
                                            <span>${booking.therapist_name}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <i class="fas fa-calendar w-6 text-gray-400"></i>
                                            <span>${new Date(booking.appointment_date).toLocaleDateString('id-ID')}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <i class="fas fa-clock w-6 text-gray-400"></i>
                                            <span>${booking.appointment_time} WIB</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex space-x-3 pt-4">
                                    <a href="<?= base_url('admin/bookingDetail/') ?>${booking.booking_code}" 
                                       class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-center">
                                        <i class="fas fa-eye mr-2"></i>View Full Details
                                    </a>
                                    <button onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                        Close
                                    </button>
                                </div>
                            </div>
                        `;

                        document.getElementById('booking-modal').classList.remove('hidden');
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function closeModal() {
            document.getElementById('booking-modal').classList.add('hidden');
        }
    </script>
    <?php include __DIR__ . '/includes/dark-mode-script.php'; ?>
</body>

</html>