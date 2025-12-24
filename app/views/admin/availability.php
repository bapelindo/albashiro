<?php
/**
 * Albashiro - Availability Management Page
 */

// Check authentication
if (!isset($_SESSION['user_id'])) {
    redirect('auth/login');
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .time-slot {
            transition: all 0.2s;
        }

        .time-slot:hover {
            transform: translateY(-2px);
        }
    </style>
    <?php include __DIR__ . '/includes/dark-mode-styles.php'; ?>
</head>

<body class="bg-gray-100 min-h-screen">
    <?php
    $activeMenu = 'availability';
    include __DIR__ . '/includes/sidebar.php';
    ?>

    <!-- Main Content -->
    <main class="ml-64 p-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-800"><?= e($title) ?></h1>
                <p class="text-gray-500 text-sm">Manage therapist schedules and availability</p>
            </div>
        </div>

        <!-- Therapist Selector -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Select Therapist:</label>
            <select id="therapist-select"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                <?php
                $therapistModel = $this->model('Therapist');
                $therapists = $therapistModel->getAll();
                foreach ($therapists as $therapist):
                    ?>
                    <option value="<?= $therapist->id ?>"><?= e($therapist->name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Weekly Schedule -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-calendar-week text-indigo-600 mr-2"></i>
                Weekly Schedule
            </h2>

            <form id="schedule-form" onsubmit="saveSchedule(event)">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="space-y-4" id="schedule-container">
                    <!-- Will be populated by JavaScript -->
                </div>

                <button type="submit" class="mt-6 px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-save mr-2"></i>Save Schedule
                </button>
            </form>
        </div>

        <!-- Holidays/Overrides -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-calendar-times text-red-600 mr-2"></i>
                Holidays & Special Days
            </h2>

            <div class="grid md:grid-cols-2 gap-6">
                <!-- Add Override Form -->
                <div>
                    <h3 class="font-semibold text-gray-700 mb-3">Add Holiday/Override</h3>
                    <form id="override-form" onsubmit="addOverride(event)" class="space-y-3">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="date" id="override-date" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <input type="text" id="override-reason" placeholder="Reason (e.g., Public Holiday)" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <button type="submit"
                            class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            <i class="fas fa-plus mr-2"></i>Add Override
                        </button>
                    </form>
                </div>

                <!-- Override List -->
                <div>
                    <h3 class="font-semibold text-gray-700 mb-3">Upcoming Overrides</h3>
                    <div id="override-list" class="space-y-2 max-h-64 overflow-y-auto">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        const dayLabels = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        let currentTherapistId = document.getElementById('therapist-select').value;

        // Load schedule when therapist changes
        document.getElementById('therapist-select').addEventListener('change', function () {
            currentTherapistId = this.value;
            loadSchedule();
            loadOverrides();
        });

        // Load initial schedule
        loadSchedule();
        loadOverrides();

        function loadSchedule() {
            const container = document.getElementById('schedule-container');
            container.innerHTML = '<p class="text-gray-500">Loading schedule...</p>';

            fetch(`<?= base_url('admin/getTherapistSchedule') ?>?therapist_id=${currentTherapistId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderSchedule(data.schedule);
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function renderSchedule(schedule) {
            const container = document.getElementById('schedule-container');
            container.innerHTML = '';

            days.forEach((day, index) => {
                // Check if we have saved schedule for this day
                let daySchedule;

                if (schedule[day] && schedule[day].is_available !== undefined) {
                    // Use saved schedule
                    daySchedule = schedule[day];
                } else {
                    // Use smart default: Monday-Friday checked, Weekend unchecked
                    const isWeekday = index < 5; // Monday to Friday
                    daySchedule = {
                        is_available: isWeekday,
                        start_time: '09:00:00',
                        end_time: '17:00:00'
                    };
                }

                const dayDiv = document.createElement('div');
                dayDiv.className = 'flex items-center space-x-4 p-4 border border-gray-200 rounded-lg';
                dayDiv.innerHTML = `
                    <div class="w-32">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="day_${day}" ${daySchedule.is_available ? 'checked' : ''}
                                class="w-5 h-5 text-indigo-600 rounded" onchange="toggleDay('${day}')">
                            <span class="font-medium">${dayLabels[index]}</span>
                        </label>
                    </div>
                    <div id="times_${day}" class="flex-1 flex items-center space-x-3 ${!daySchedule.is_available ? 'opacity-50' : ''}">
                        <input type="time" name="start_${day}" value="${daySchedule.start_time.substring(0, 5)}"
                            class="px-3 py-2 border border-gray-300 rounded-lg" ${!daySchedule.is_available ? 'disabled' : ''}>
                        <span>to</span>
                        <input type="time" name="end_${day}" value="${daySchedule.end_time.substring(0, 5)}"
                            class="px-3 py-2 border border-gray-300 rounded-lg" ${!daySchedule.is_available ? 'disabled' : ''}>
                    </div>
                `;
                container.appendChild(dayDiv);
            });
        }

        function toggleDay(day) {
            const checkbox = document.querySelector(`input[name="day_${day}"]`);
            const timesDiv = document.getElementById(`times_${day}`);
            const inputs = timesDiv.querySelectorAll('input');

            if (checkbox.checked) {
                timesDiv.classList.remove('opacity-50');
                inputs.forEach(input => input.disabled = false);
            } else {
                timesDiv.classList.add('opacity-50');
                inputs.forEach(input => input.disabled = true);
            }
        }

        // Save schedule
        function saveSchedule(e) {
            e.preventDefault();

            const therapistId = document.getElementById('therapist-select').value;
            const schedule = {};

            ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'].forEach(day => {
                const checkbox = document.querySelector(`input[name="day_${day}"]`);
                const startInput = document.querySelector(`input[name="start_${day}"]`);
                const endInput = document.querySelector(`input[name="end_${day}"]`);
                const isAvailable = checkbox?.checked || false;
                const startTime = startInput?.value || '';
                const endTime = endInput?.value || '';

                // Only include if available AND has valid times
                if (isAvailable && startTime && endTime) {
                    schedule[day] = {
                        is_available: true,
                        start_time: startTime,
                        end_time: endTime
                    };
                } else {
                    // Mark as unavailable
                    schedule[day] = {
                        is_available: false,
                        start_time: null,
                        end_time: null
                    };
                }
            });

            console.log('Saving schedule:', { therapist_id: therapistId, schedule });

            fetch('<?= base_url('admin/saveTherapistSchedule') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    therapist_id: parseInt(therapistId),
                    schedule: schedule
                })
            })
                .then(response => response.json())
                .then(data => {
                    console.log('Save response:', data);
                    if (data.success) {
                        alert('Schedule saved successfully!');
                    } else {
                        alert('Error: ' + (data.message || 'Failed to save schedule'));
                    }
                })
                .catch(error => {
                    console.error('Error saving schedule:', error);
                    alert('Error saving schedule: ' + error.message);
                });
        }

        // Load overrides
        function loadOverrides() {
            fetch(`<?= base_url('admin/getTherapistOverrides') ?>?therapist_id=${currentTherapistId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderOverrides(data.overrides);
                    }
                });
        }

        function renderOverrides(overrides) {
            const list = document.getElementById('override-list');

            if (overrides.length === 0) {
                list.innerHTML = '<p class="text-gray-500 text-sm">No overrides set</p>';
                return;
            }

            list.innerHTML = overrides.map(override => `
                <div class="flex items-center justify-between p-3 bg-red-50 border border-red-200 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-800">${new Date(override.override_date).toLocaleDateString('id-ID')}</p>
                        <p class="text-sm text-gray-600">${override.reason || 'No reason'}</p>
                    </div>
                    <button onclick="deleteOverride(${override.id})" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `).join('');
        }

        // Add override
        function addOverride(e) {
            e.preventDefault();

            const therapistId = document.getElementById('therapist-select').value;
            const date = document.getElementById('override-date').value;
            const reason = document.getElementById('override-reason').value;

            if (!date || !reason) {
                alert('Please fill in all fields');
                return;
            }

            console.log('Adding override:', { therapist_id: therapistId, date, reason });

            fetch('<?= base_url('admin/addTherapistOverride') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    therapist_id: parseInt(therapistId),
                    date: date,
                    reason: reason
                })
            })
                .then(response => response.json())
                .then(data => {
                    console.log('Override response:', data);
                    if (data.success) {
                        alert('Override added successfully!');
                        document.getElementById('override-date').value = '';
                        document.getElementById('override-reason').value = '';
                        loadOverrides();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to add override'));
                    }
                })
                .catch(error => {
                    console.error('Error adding override:', error);
                    alert('Error adding override: ' + error.message);
                });
        }

        function deleteOverride(id) {
            if (!confirm('Delete this override?')) return;

            fetch('<?= base_url('admin/deleteTherapistOverride') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadOverrides();
                    }
                });
        }
    </script>

    <?php include __DIR__ . '/includes/dark-mode-script.php'; ?>
</body>

</html>