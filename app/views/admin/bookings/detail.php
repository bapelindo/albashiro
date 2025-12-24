<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include __DIR__ . '/../includes/dark-mode-styles.php'; ?>
    <title><?= $title ?> - Admin Albashiro</title>
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
                        <i class="fas fa-mosque mr-2"></i>Albashiro Admin
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
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="<?= base_url('admin/bookings') ?>" class="text-purple-600 hover:text-purple-800">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar Reservasi
            </a>
        </div>

        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-file-alt text-purple-600 mr-2"></i><?= $title ?>
            </h1>
            <p class="text-gray-600 mt-2">Kode: <span
                    class="font-mono font-semibold text-purple-600"><?= e($booking->booking_code) ?></span></p>
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

        <div class="grid md:grid-cols-3 gap-6">
            <!-- Booking Details -->
            <div class="md:col-span-2 space-y-6">
                <!-- Client Information -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-user text-purple-600 mr-2"></i>Informasi Klien
                    </h2>
                    <div class="space-y-3">
                        <div class="flex">
                            <span class="w-32 text-gray-600">Nama:</span>
                            <span class="font-semibold"><?= e($booking->client_name) ?></span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-gray-600">WhatsApp:</span>
                            <a href="https://wa.me/<?= e($booking->wa_number) ?>" target="_blank"
                                class="text-green-600 hover:text-green-800">
                                <i class="fab fa-whatsapp mr-1"></i><?= e($booking->wa_number) ?>
                            </a>
                        </div>
                        <?php if ($booking->email): ?>
                            <div class="flex">
                                <span class="w-32 text-gray-600">Email:</span>
                                <a href="mailto:<?= e($booking->email) ?>" class="text-blue-600 hover:text-blue-800">
                                    <?= e($booking->email) ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Appointment Information -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-calendar-check text-purple-600 mr-2"></i>Informasi Janji Temu
                    </h2>
                    <div class="space-y-3">
                        <div class="flex">
                            <span class="w-32 text-gray-600">Terapis:</span>
                            <span class="font-semibold"><?= e($booking->therapist_name) ?></span>
                        </div>
                        <?php if ($booking->service_name): ?>
                            <div class="flex">
                                <span class="w-32 text-gray-600">Layanan:</span>
                                <span class="font-semibold"><?= e($booking->service_name) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="flex">
                            <span class="w-32 text-gray-600">Tanggal:</span>
                            <span class="font-semibold"><?= format_date_id($booking->appointment_date) ?></span>
                        </div>
                        <?php if ($booking->appointment_time): ?>
                            <div class="flex">
                                <span class="w-32 text-gray-600">Waktu:</span>
                                <span class="font-semibold"><?= substr($booking->appointment_time, 0, 5) ?> WIB</span>
                            </div>
                        <?php endif; ?>
                        <div class="flex">
                            <span class="w-32 text-gray-600">Dibuat:</span>
                            <span><?= date('d/m/Y H:i', strtotime($booking->created_at)) ?> WIB</span>
                        </div>
                    </div>
                </div>

                <!-- Reschedule History -->
                <?php if (!empty($reschedule_history)): ?>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-history text-orange-600 mr-2"></i>Riwayat Reschedule
                        </h2>
                        <div class="space-y-3">
                            <?php foreach ($reschedule_history as $history): ?>
                                <div class="bg-gray-50 p-4 rounded-lg border-l-4 border-orange-500">
                                    <div class="flex items-start justify-between mb-2">
                                        <div class="flex-1">
                                            <div class="flex items-center text-sm text-gray-600 mb-2">
                                                <i class="fas fa-calendar-alt mr-2 text-orange-500"></i>
                                                <span class="font-medium">
                                                    <?= format_date_id($history->old_date) ?>
                                                    <?php if ($history->old_time): ?>
                                                        <?= substr($history->old_time, 0, 5) ?> WIB
                                                    <?php endif; ?>
                                                </span>
                                                <i class="fas fa-arrow-right mx-3 text-gray-400"></i>
                                                <span class="font-medium text-indigo-600">
                                                    <?= format_date_id($history->new_date) ?>
                                                    <?php if ($history->new_time): ?>
                                                        <?= substr($history->new_time, 0, 5) ?> WIB
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                            <?php if (!empty($history->reason)): ?>
                                                <div class="flex items-start text-sm text-gray-600 mb-2">
                                                    <i class="fas fa-comment-alt mr-2 text-gray-400 mt-0.5"></i>
                                                    <span class="italic"><?= e($history->reason) ?></span>
                                                </div>
                                            <?php endif; ?>
                                            <div class="flex items-center text-xs text-gray-500">
                                                <i class="fas fa-user mr-2"></i>
                                                <span>By <?= e($history->rescheduled_by) ?></span>
                                                <span class="mx-2">•</span>
                                                <i class="fas fa-clock mr-2"></i>
                                                <span><?= format_date_id($history->created_at) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Problem Description -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-comment-alt text-purple-600 mr-2"></i>Keluhan
                    </h2>
                    <p class="text-gray-700 whitespace-pre-line"><?= e($booking->problem_description) ?></p>
                </div>

                <!-- Admin Notes -->
                <?php if ($booking->notes): ?>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-sticky-note text-yellow-600 mr-2"></i>Catatan Admin
                        </h2>
                        <p class="text-gray-700 whitespace-pre-line"><?= e($booking->notes) ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Status Update Form -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-edit text-purple-600 mr-2"></i>Update Status
                    </h2>

                    <form action="<?= base_url('admin/updateBookingStatus') ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="booking_id" value="<?= $booking->id ?>">

                        <!-- Current Status -->
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Status Saat Ini:</label>
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
                                class="px-4 py-2 inline-flex text-sm font-semibold rounded-full <?= $statusColors[$booking->status] ?>">
                                <?= $statusLabels[$booking->status] ?>
                            </span>
                        </div>

                        <!-- New Status -->
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Ubah Status:</label>
                            <select name="status" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="pending" <?= $booking->status === 'pending' ? 'selected' : '' ?>>Pending
                                </option>
                                <option value="confirmed" <?= $booking->status === 'confirmed' ? 'selected' : '' ?>>
                                    Dikonfirmasi</option>
                                <option value="completed" <?= $booking->status === 'completed' ? 'selected' : '' ?>>Selesai
                                </option>
                                <option value="cancelled" <?= $booking->status === 'cancelled' ? 'selected' : '' ?>>
                                    Dibatalkan</option>
                            </select>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Catatan (Opsional):</label>
                            <textarea name="notes" rows="4" placeholder="Tambahkan catatan untuk reservasi ini..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"><?= e($booking->notes ?? '') ?></textarea>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit"
                            class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
                            <i class="fas fa-save mr-2"></i>Simpan Perubahan
                        </button>

                        <!-- Reschedule Button -->
                        <?php if ($booking->status !== 'cancelled' && $booking->status !== 'completed'): ?>
                            <button type="button" onclick="openRescheduleModal()"
                                class="w-full mt-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
                                <i class="fas fa-calendar-alt mr-2"></i>Reschedule
                            </button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Reschedule Modal -->
    <div id="reschedule-modal"
        class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col">
            <!-- Modal Header (Fixed) -->
            <div class="p-6 border-b border-gray-200 flex-shrink-0">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-800">
                        <i class="fas fa-calendar-alt text-indigo-600 mr-2"></i>
                        Reschedule Booking
                    </h3>
                    <button onclick="closeRescheduleModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Content (Scrollable) -->
            <div class="p-6 overflow-y-auto flex-1">
                <form action="<?= base_url('admin/rescheduleBooking') ?>" method="POST" id="reschedule-form">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="booking_id" value="<?= $booking->id ?>">

                    <!-- Current Schedule -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-4">
                        <p class="text-sm text-gray-500 mb-1">Current Schedule:</p>
                        <p class="font-semibold text-gray-800">
                            <?= format_date_id($booking->appointment_date) ?>
                            <?php if ($booking->appointment_time): ?>
                                - <?= substr($booking->appointment_time, 0, 5) ?> WIB
                            <?php endif; ?>
                        </p>
                    </div>

                    <!-- New Date -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">New Date:</label>
                        <input type="date" name="new_date" id="new_date" required min="<?= date('Y-m-d') ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    <!-- New Time -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">New Time:</label>
                        <div class="relative">
                            <select name="new_time" id="new_time" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <option value="">Select date first...</option>
                            </select>
                            <div id="loading-slots" class="hidden absolute right-3 top-3">
                                <i class="fas fa-spinner fa-spin text-indigo-600"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Reason -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Reason for Reschedule:</label>
                        <textarea name="reason" rows="3" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="e.g., Client request, Emergency, etc."></textarea>
                    </div>
                </form>
            </div>

            <!-- Modal Footer (Fixed) -->
            <div class="p-6 border-t border-gray-200 flex justify-end space-x-3 flex-shrink-0">
                <button type="button" onclick="closeRescheduleModal()"
                    class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-times mr-2"></i>Cancel
                </button>
                <button type="submit" form="reschedule-form"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-check mr-2"></i>Confirm Reschedule
                </button>
            </div>
        </div>
    </div>

    <script>
        function openRescheduleModal() {
            document.getElementById('reschedule-modal').classList.remove('hidden');
        }

        function closeRescheduleModal() {
            document.getElementById('reschedule-modal').classList.add('hidden');
            document.getElementById('reschedule-form').reset();
        }

        // Reschedule form submission
        document.getElementById('reschedule-form')?.addEventListener('submit', function (e) {
            const newTime = document.getElementById('new_time').value;
            const newDate = document.getElementById('new_date').value;

            if (!newTime || newTime === '' || newTime === 'undefined') {
                e.preventDefault();
                alert('Please select a valid time slot');
                return false;
            }

            if (!newDate || newDate === '') {
                e.preventDefault();
                alert('Please select a date');
                return false;
            }
        });

        // Load available slots when date changes
        document.getElementById('new_date')?.addEventListener('change', function () {
            const therapistId = <?= $booking->therapist_id ?>;
            const date = this.value;
            const timeSelect = document.getElementById('new_time');
            const loading = document.getElementById('loading-slots');

            if (!date) {
                timeSelect.innerHTML = '<option value="">Select date first...</option>';
                return;
            }

            // Show loading
            loading.classList.remove('hidden');
            timeSelect.innerHTML = '<option value="">Loading...</option>';

            fetch(`<?= base_url('admin/getAvailableSlots') ?>?therapist_id=${therapistId}&date=${date}`)
                .then(response => response.json())
                .then(data => {
                    loading.classList.add('hidden');
                    if (data.success && data.slots.length > 0) {
                        timeSelect.innerHTML = '<option value="">Select time...</option>' +
                            data.slots.map(slot => `<option value="${slot.time}">${slot.display}</option>`).join('');
                    } else {
                        timeSelect.innerHTML = '<option value="">No available slots</option>';
                    }
                })
                .catch(error => {
                    console.error('Error loading slots:', error);
                    loading.classList.add('hidden');
                    timeSelect.innerHTML = '<option value="">Error loading slots</option>';
                });
        });
    </script>
</body>

</html>