<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> | <?= SITE_NAME ?> Admin</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .fc-event {
            cursor: pointer;
            border-radius: 4px;
            padding: 2px 4px;
        }

        .fc-daygrid-event {
            white-space: normal !important;
        }
    </style>
    <?php include __DIR__ . '/includes/dark-mode-styles.php'; ?>
</head>

<body class="bg-gray-100 min-h-screen">
    <?php
    $activeMenu = 'calendar';
    include __DIR__ . '/includes/sidebar.php';
    ?>

    <!-- Main Content -->
    <main class="ml-64 p-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-800"><?= e($title) ?></h1>
                <p class="text-gray-500 text-sm">Manage bookings visually</p>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-500"><?= date('d M Y, H:i') ?></span>
                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-indigo-600"></i>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-3">
                <select id="therapist-filter" onchange="filterByTherapist()"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Therapists</option>
                    <?php
                    $therapistModel = $this->model('Therapist');
                    $therapists = $therapistModel->getAll();
                    foreach ($therapists as $therapist):
                        ?>
                        <option value="<?= $therapist->id ?>"><?= e($therapist->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex items-center space-x-3">
                <!-- View Toggle -->
                <div class="flex bg-gray-100 rounded-lg p-1">
                    <button id="calendar-view-btn" onclick="switchView('calendar')"
                        class="px-4 py-2 rounded-md bg-white shadow text-indigo-600">
                        <i class="fas fa-calendar mr-2"></i>Calendar
                    </button>
                    <button id="list-view-btn" onclick="switchView('list')" class="px-4 py-2 rounded-md text-gray-600">
                        <i class="fas fa-list mr-2"></i>List
                    </button>
                </div>
                <a href="<?= base_url('admin/bookings') ?>"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-table mr-2"></i>Table View
                </a>
            </div>
        </div>

        <!-- Legend -->
        <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
            <div class="flex items-center space-x-6">
                <span class="text-sm font-medium text-gray-700">Status:</span>
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 rounded" style="background-color: #FCD34D;"></div>
                    <span class="text-sm text-gray-600">Pending</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 rounded" style="background-color: #60A5FA;"></div>
                    <span class="text-sm text-gray-600">Confirmed</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 rounded" style="background-color: #34D399;"></div>
                    <span class="text-sm text-gray-600">Completed</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 rounded" style="background-color: #F87171;"></div>
                    <span class="text-sm text-gray-600">Cancelled</span>
                </div>
            </div>
        </div>

        <!-- Calendar View -->
        <div id="calendar-container" class="bg-white rounded-xl shadow-sm p-6">
            <div id="calendar"></div>
        </div>

        <!-- List View -->
        <div id="list-container" class="hidden bg-white rounded-xl shadow-sm p-6">
            <div id="bookings-list" class="space-y-4">
                <!-- Will be populated by JavaScript -->
            </div>
        </div>
    </main>

    <!-- Booking Details Modal -->
    <div id="booking-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-800">Booking Details</h3>
                    <button onclick="window.closeModal()" class="text-gray-400 hover:text-gray-600">
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
                        let url = '<?= base_url('admin/getCalendarEvents') ?>?start=' + info.startStr + '&end=' + info.endStr;
                        if (therapistId) {
                            url += '&therapist_id=' + therapistId;
                        }

                        fetch(url)
                            .then(response => response.json())
                            .then(data => successCallback(data))
                            .catch(error => {
                                console.error('Error loading events:', error);
                                failureCallback(error);
                            });
                    },
                    eventClick: function (info) {
                        window.showBookingDetails(info.event.id);
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
            }
        });

        // Make functions globally accessible via window object
        window.filterByTherapist = function () {
            const listContainer = document.getElementById('list-container');
            const isListView = !listContainer.classList.contains('hidden');

            if (isListView) {
                window.loadListView();
            } else if (calendar) {
                calendar.refetchEvents();
            }
        };

        window.showBookingDetails = function (bookingId) {
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
                                <div class="flex items-center justify-between pb-4 border-b">
                                    <div>
                                        <h4 class="text-lg font-bold text-gray-800">${booking.extendedProps.service_name || 'Hypnotherapy Session'}</h4>
                                        <p class="text-sm text-gray-500">Booking Code: ${booking.extendedProps.booking_code}</p>
                                    </div>
                                    <span class="px-3 py-1 text-sm font-semibold rounded-full" 
                                        style="background-color: ${booking.backgroundColor}; color: white;">
                                        ${booking.extendedProps.status}
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Client Name</p>
                                        <p class="font-semibold text-gray-800">${booking.extendedProps.client_name}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">WhatsApp</p>
                                        <p class="font-semibold text-gray-800">${booking.extendedProps.wa_number}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Therapist</p>
                                        <p class="font-semibold text-gray-800">${booking.extendedProps.therapist_name}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Date & Time</p>
                                        <p class="font-semibold text-gray-800">${new Date(booking.start).toLocaleDateString('id-ID')} - ${booking.extendedProps.time} WIB</p>
                                    </div>
                                </div>
                                ${booking.extendedProps.notes ? `
                                    <div class="pt-4 border-t">
                                        <p class="text-sm text-gray-500 mb-2">Notes</p>
                                        <p class="text-gray-700">${booking.extendedProps.notes}</p>
                                    </div>
                                ` : ''}
                                <div class="flex space-x-3 pt-4">
                                    <a href="<?= base_url('admin/bookingDetail/') ?>${booking.extendedProps.booking_code}" 
                                       class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-center">
                                        <i class="fas fa-eye mr-2"></i>View Full Details
                                    </a>
                                    <button onclick="window.closeModal()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                        Close
                                    </button>
                                </div>
                            </div>
                        `;

                        document.getElementById('booking-modal').classList.remove('hidden');
                    }
                })
                .catch(error => console.error('Error:', error));
        };

        window.closeModal = function () {
            document.getElementById('booking-modal').classList.add('hidden');
        };

        window.switchView = function (view) {
            const calendarContainer = document.getElementById('calendar-container');
            const listContainer = document.getElementById('list-container');
            const calendarBtn = document.getElementById('calendar-view-btn');
            const listBtn = document.getElementById('list-view-btn');

            if (view === 'calendar') {
                calendarContainer.classList.remove('hidden');
                listContainer.classList.add('hidden');
                calendarBtn.classList.add('bg-white', 'shadow', 'text-indigo-600');
                calendarBtn.classList.remove('text-gray-600');
                listBtn.classList.remove('bg-white', 'shadow', 'text-indigo-600');
                listBtn.classList.add('text-gray-600');
            } else {
                calendarContainer.classList.add('hidden');
                listContainer.classList.remove('hidden');
                listBtn.classList.add('bg-white', 'shadow', 'text-indigo-600');
                listBtn.classList.remove('text-gray-600');
                calendarBtn.classList.remove('bg-white', 'shadow', 'text-indigo-600');
                calendarBtn.classList.add('text-gray-600');
                window.loadListView();
            }
        };

        window.loadListView = function () {
            const therapistId = document.getElementById('therapist-filter').value;
            const url = therapistId
                ? `<?= base_url('admin/getCalendarEvents') ?>?therapist_id=${therapistId}`
                : `<?= base_url('admin/getCalendarEvents') ?>`;

            fetch(url)
                .then(response => response.json())
                .then(events => {
                    const listContainer = document.getElementById('bookings-list');

                    if (events.length === 0) {
                        listContainer.innerHTML = '<p class="text-gray-500 text-center py-8">No bookings found</p>';
                        return;
                    }

                    // Group by date
                    const grouped = events.reduce((acc, event) => {
                        const date = event.start.split('T')[0];
                        if (!acc[date]) acc[date] = [];
                        acc[date].push(event);
                        return acc;
                    }, {});

                    // Render list
                    listContainer.innerHTML = Object.keys(grouped).sort().map(date => {
                        const bookings = grouped[date];
                        return `
                            <div class="border border-gray-200 rounded-lg overflow-hidden">
                                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                    <h3 class="font-semibold text-gray-800">
                                        <i class="fas fa-calendar mr-2"></i>
                                        ${new Date(date).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}
                                    </h3>
                                </div>
                                <div class="divide-y divide-gray-200">
                                    ${bookings.map(booking => `
                                        <div class="p-4 hover:bg-gray-50 cursor-pointer" onclick="window.showBookingDetails(${booking.id})">
                                            <div class="flex items-center justify-between">
                                                <div class="flex-1">
                                                    <div class="flex items-center space-x-3">
                                                        <div class="w-3 h-3 rounded-full" style="background-color: ${booking.backgroundColor}"></div>
                                                        <div>
                                                            <p class="font-semibold text-gray-800">${booking.extendedProps.service_name || 'Hypnotherapy Session'}</p>
                                                            <p class="text-sm text-gray-600">${booking.title}</p>
                                                            <p class="text-sm text-gray-500">
                                                                <i class="fas fa-clock mr-1"></i>${booking.start.split('T')[1] ? booking.start.split('T')[1].substring(0, 5) : booking.extendedProps.time || 'N/A'} WIB
                                                                <span class="mx-2">•</span>
                                                                <i class="fas fa-user-md mr-1"></i>${booking.extendedProps.therapist_name || 'N/A'}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <span class="px-3 py-1 text-xs font-semibold rounded-full" 
                                                        style="background-color: ${booking.backgroundColor}; color: white;">
                                                        ${booking.extendedProps.status}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        `;
                    }).join('');
                })
                .catch(error => {
                    console.error('Error loading list view:', error);
                    document.getElementById('bookings-list').innerHTML =
                        '<p class="text-red-500 text-center py-8">Error loading bookings</p>';
                });
        };
    </script>

    <?php include __DIR__ . '/includes/dark-mode-script.php'; ?>
</body>

</html>