<?php
/**
 * Albashiro - Admin Controller
 * Handles admin dashboard and blog CRUD
 */

class Admin extends Controller
{

    private $blogModel;
    private $bookingModel;

    public function __construct()
    {
        // Check authentication
        if (!$this->isLoggedIn()) {
            redirect('auth/login');
        }

        $this->blogModel = $this->model('BlogPost');
        $this->bookingModel = $this->model('Booking');
    }

    /**
     * Dashboard
     */
    public function index()
    {
        $data = [
            'title' => 'Dashboard',
            'posts' => $this->blogModel->getAll(),
            'bookings' => $this->bookingModel->getAll(),
            'user' => $this->getCurrentUser()
        ];
        echo $this->viewAdmin('admin/dashboard', $data);
    }

    /**
     * Calendar page
     */
    public function calendar()
    {
        $data = [
            'title' => 'Calendar - Booking Management',
            'user' => $this->getCurrentUser()
        ];
        echo $this->viewAdmin('admin/calendar', $data);
    }

    /**
     * Availability management page
     */
    public function availability()
    {
        $data = [
            'title' => 'Availability Management',
            'user' => $this->getCurrentUser()
        ];
        echo $this->viewAdmin('admin/availability', $data);
    }

    /**
     * Analytics dashboard
     */
    public function analytics()
    {
        $data = [
            'title' => 'Analytics Dashboard',
            'user' => $this->getCurrentUser()
        ];
        echo $this->viewAdmin('admin/analytics', $data);
    }

    /**
     * Export bookings to CSV
     */
    public function exportBookings()
    {
        $status = $this->input('status') ?? 'all';
        $startDate = $this->input('start_date') ?? '';
        $endDate = $this->input('end_date') ?? '';

        $bookings = $this->bookingModel->getAll();

        // Filter bookings
        if ($status !== 'all') {
            $bookings = array_filter($bookings, function ($b) use ($status) {
                return $b->status === $status;
            });
        }

        if ($startDate && $endDate) {
            $bookings = array_filter($bookings, function ($b) use ($startDate, $endDate) {
                $date = date('Y-m-d', strtotime($b->appointment_date));
                return $date >= $startDate && $date <= $endDate;
            });
        }

        // Generate CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="bookings_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // Headers
        fputcsv($output, [
            'Booking Code',
            'Client Name',
            'WhatsApp',
            'Therapist',
            'Date',
            'Time',
            'Status',
            'Created At'
        ], ',', '"', '\\');

        // Data
        foreach ($bookings as $booking) {
            fputcsv($output, [
                $booking->booking_code,
                $booking->client_name,
                $booking->wa_number,
                $booking->therapist_name,
                date('d/m/Y', strtotime($booking->appointment_date)),
                $booking->appointment_time ? substr($booking->appointment_time, 0, 5) : 'N/A',
                ucfirst($booking->status),
                date('d/m/Y H:i', strtotime($booking->created_at))
            ], ',', '"', '\\');
        }

        fclose($output);
        exit;
    }

    /**
     * Blog list
     */
    public function blog()
    {
        $data = [
            'title' => 'Kelola Blog',
            'posts' => $this->blogModel->getAll(),
            'user' => $this->getCurrentUser(),
            'flash' => $this->getFlash()
        ];
        echo $this->viewAdmin('admin/blog/index', $data);
    }

    /**
     * Create blog post form
     */
    public function create()
    {
        $data = [
            'title' => 'Tulis Artikel Baru',
            'user' => $this->getCurrentUser()
        ];
        echo $this->viewAdmin('admin/blog/create', $data);
    }

    /**
     * Store new blog post
     */
    public function store()
    {
        if (!$this->isPost()) {
            redirect('admin/blog');
        }

        // Verify CSRF
        if (!verify_csrf($this->input('csrf_token'))) {
            $this->setFlash('error', 'Sesi tidak valid.');
            redirect('admin/create');
        }

        // Handle image upload
        $featuredImage = $this->input('featured_image'); // URL input
        try {
            $uploadedImage = $this->handleImageUpload('featured_image_upload');
            if ($uploadedImage) {
                $featuredImage = $uploadedImage; // Prioritize uploaded file
            }
        } catch (Exception $e) {
            $this->setFlash('error', 'Upload gagal: ' . $e->getMessage());
            redirect('admin/create');
        }

        $data = [
            'title' => $this->input('title'),
            'excerpt' => $this->input('excerpt'),
            'content' => $_POST['content'] ?? '', // Allow HTML
            'category' => $this->input('category'),
            'tags' => $this->input('tags'),
            'status' => $this->input('status'),
            'author_id' => SecureAuth::getUser()['user_id'] ?? 1,
            'featured_image' => $featuredImage
        ];

        // Validation
        if (empty($data['title']) || empty($data['content'])) {
            $this->setFlash('error', 'Judul dan konten harus diisi.');
            redirect('admin/create');
        }

        $this->blogModel->create($data);
        $this->setFlash('success', 'Artikel berhasil dibuat.');
        redirect('admin/blog');
    }

    /**
     * Edit blog post form
     */
    public function edit($id)
    {
        $post = $this->blogModel->getById($id);
        if (!$post) {
            redirect('admin/blog');
        }

        $data = [
            'title' => 'Edit Artikel',
            'post' => $post,
            'user' => $this->getCurrentUser()
        ];
        echo $this->viewAdmin('admin/blog/edit', $data);
    }

    /**
     * Update blog post
     */
    public function update($id)
    {
        if (!$this->isPost()) {
            redirect('admin/blog');
        }

        // Verify CSRF
        if (!verify_csrf($this->input('csrf_token'))) {
            $this->setFlash('error', 'Sesi tidak valid.');
            redirect('admin/edit/' . $id);
        }

        // Get existing post to preserve image if not updated
        $existingPost = $this->blogModel->getById($id);

        // Handle image upload
        $featuredImage = $this->input('featured_image'); // URL input
        try {
            $uploadedImage = $this->handleImageUpload('featured_image_upload');
            if ($uploadedImage) {
                $featuredImage = $uploadedImage; // Prioritize uploaded file
            } elseif (empty($featuredImage) && $existingPost) {
                $featuredImage = $existingPost->featured_image; // Keep existing
            }
        } catch (Exception $e) {
            $this->setFlash('error', 'Upload gagal: ' . $e->getMessage());
            redirect('admin/edit/' . $id);
        }

        $data = [
            'title' => $this->input('title'),
            'excerpt' => $this->input('excerpt'),
            'content' => $_POST['content'] ?? '',
            'category' => $this->input('category'),
            'tags' => $this->input('tags'),
            'status' => $this->input('status'),
            'featured_image' => $featuredImage
        ];

        // Validation
        if (empty($data['title']) || empty($data['content'])) {
            $this->setFlash('error', 'Judul dan konten harus diisi.');
            redirect('admin/edit/' . $id);
        }

        $this->blogModel->update($id, $data);
        $this->setFlash('success', 'Artikel berhasil diperbarui.');
        redirect('admin/blog');
    }

    /**
     * Delete blog post
     */
    public function delete($id)
    {
        if (!$this->isPost()) {
            redirect('admin/blog');
        }

        $this->blogModel->delete($id);
        $this->setFlash('success', 'Artikel berhasil dihapus.');
        redirect('admin/blog');
    }

    /**
     * Handle image upload
     */
    private function handleImageUpload($fieldName = 'featured_image_upload')
    {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $file = $_FILES[$fieldName];

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Upload error: ' . $file['error']);
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception('Invalid file type. Only JPG, PNG, GIF, and WebP allowed.');
        }

        // Validate file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception('File too large. Maximum 5MB allowed.');
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'blog-' . time() . '-' . uniqid() . '.' . $extension;
        $uploadPath = SITE_ROOT . '/public/images/' . $filename;

        // Ensure upload directory exists
        $uploadDir = SITE_ROOT . '/public/images/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new Exception('Failed to upload image.');
        }

        return $filename;
    }

    /**
     * Delete tag from all articles
     */
    public function deleteTag()
    {
        // Check if user is logged in
        if (!SecureAuth::isLoggedIn()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        // Get tag name from POST
        $tag = isset($_POST['tag']) ? trim($_POST['tag']) : '';

        if (empty($tag)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Tag tidak valid']);
            exit;
        }

        // Get all posts with this tag
        $posts = $this->blogModel->getAll();
        $affectedCount = 0;

        foreach ($posts as $post) {
            if (empty($post->tags))
                continue;

            $tags = array_map('trim', explode(',', $post->tags));

            // Check if this post has the tag
            if (in_array($tag, $tags)) {
                // Remove the tag
                $tags = array_filter($tags, fn($t) => $t !== $tag);
                $newTags = implode(', ', $tags);

                // Update the post
                $this->blogModel->update($post->id, [
                    'title' => $post->title,
                    'excerpt' => $post->excerpt,
                    'content' => $post->content,
                    'featured_image' => $post->featured_image,
                    'category' => $post->category,
                    'tags' => $newTags,
                    'status' => $post->status
                ]);

                $affectedCount++;
            }
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'affected' => $affectedCount,
            'message' => "Tag berhasil dihapus dari $affectedCount artikel"
        ]);
        exit;
    }

    /**
     * Get calendar events (AJAX endpoint)
     * Get calendar events (AJAX)
     */
    public function getCalendarEvents()
    {
        header('Content-Type: application/json');

        try {
            $therapistId = isset($_GET['therapist_id']) ? (int) $_GET['therapist_id'] : 0;

            // Get bookings
            if ($therapistId) {
                $bookings = $this->bookingModel->getByTherapist($therapistId);
            } else {
                $bookings = $this->bookingModel->getAll();
            }

            $events = [];
            foreach ($bookings as $booking) {
                $events[] = [
                    'id' => $booking->id,
                    'title' => $booking->client_name,
                    'start' => $booking->appointment_date . 'T' . ($booking->appointment_time ?? '00:00:00'),
                    'backgroundColor' => $this->getStatusColor($booking->status),
                    'borderColor' => $this->getStatusColor($booking->status),
                    'extendedProps' => [
                        'booking_code' => $booking->booking_code,
                        'client_name' => $booking->client_name,
                        'wa_number' => $booking->wa_number,
                        'therapist' => $booking->therapist_name ?? 'N/A',
                        'therapist_name' => $booking->therapist_name ?? 'N/A',
                        'service_name' => $booking->service_name ?? 'Hypnotherapy Session',
                        'time' => $booking->appointment_time ? substr($booking->appointment_time, 0, 5) : 'N/A',
                        'status' => ucfirst($booking->status),
                        'notes' => $booking->notes ?? ''
                    ]
                ];
            }

            echo json_encode($events);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Get booking details for modal (AJAX)
     */
    public function getBookingDetails($id)
    {
        header('Content-Type: application/json');

        $booking = $this->bookingModel->getById($id);

        if ($booking) {
            echo json_encode([
                'success' => true,
                'booking' => [
                    'id' => $booking->id,
                    'title' => $booking->client_name,
                    'start' => $booking->appointment_date . 'T' . ($booking->appointment_time ?? '00:00:00'),
                    'backgroundColor' => $this->getStatusColor($booking->status),
                    'borderColor' => $this->getStatusColor($booking->status),
                    'extendedProps' => [
                        'booking_code' => $booking->booking_code,
                        'client_name' => $booking->client_name,
                        'wa_number' => $booking->wa_number,
                        'therapist' => $booking->therapist_name ?? 'N/A',
                        'therapist_name' => $booking->therapist_name ?? 'N/A',
                        'service_name' => $booking->service_name ?? 'Hypnotherapy Session',
                        'time' => $booking->appointment_time ? substr($booking->appointment_time, 0, 5) : 'N/A',
                        'status' => ucfirst($booking->status),
                        'notes' => $booking->notes ?? ''
                    ]
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Booking not found']);
        }
        exit;
    }

    /**
     * Get status color for calendar
     */
    private function getStatusColor($status)
    {
        $colors = [
            'pending' => '#FCD34D',
            'confirmed' => '#60A5FA',
            'completed' => '#34D399',
            'cancelled' => '#F87171'
        ];

        return $colors[$status] ?? '#9CA3AF';
    }

    /**
     * Get available slots (AJAX endpoint)
     */
    public function getAvailableSlots()
    {
        header('Content-Type: application/json');

        $therapistId = isset($_GET['therapist_id']) ? (int) $_GET['therapist_id'] : 0;
        $date = isset($_GET['date']) ? $_GET['date'] : '';

        if (!$therapistId || !$date) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            exit;
        }

        $availabilityModel = $this->model('Availability');
        $bookingModel = $this->model('Booking');

        // Get slots from availability (respects schedule + overrides)
        $slots = $availabilityModel->getAvailableSlots($therapistId, $date, 60);

        // Filter out already booked slots
        $availableSlots = [];
        foreach ($slots as $slot) {
            $isBooked = $bookingModel->isSlotBooked($therapistId, $date, $slot['time']);
            if (!$isBooked) {
                $availableSlots[] = $slot;
            }
        }

        echo json_encode([
            'success' => true,
            'slots' => $availableSlots
        ]);
        exit;
    }

    /**
     * Reschedule booking
     */
    public function rescheduleBooking()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/bookings');
        }

        $bookingId = $_POST['booking_id'] ?? 0;
        $newDate = $_POST['new_date'] ?? '';
        $newTime = $_POST['new_time'] ?? '';
        $reason = $_POST['reason'] ?? '';

        if (!$bookingId || !$newDate || !$newTime || !$reason) {
            $this->setFlash('error', 'Semua field harus diisi');
            redirect('admin/bookingDetail/' . ($_POST['booking_code'] ?? ''));
        }

        // Get booking
        $booking = $this->bookingModel->getById($bookingId);
        if (!$booking) {
            $this->setFlash('error', 'Booking tidak ditemukan');
            redirect('admin/bookings');
        }

        // Store old date/time for notification
        $oldDate = $booking->appointment_date;
        $oldTime = $booking->appointment_time;

        // Reschedule
        $result = $this->bookingModel->reschedule(
            $bookingId,
            $newDate,
            $newTime,
            $reason,
            $_SESSION['user_name'] ?? 'Admin'
        );

        if ($result) {
            // Send WhatsApp notification
            require_once __DIR__ . '/../services/WhatsAppService.php';
            $whatsapp = new WhatsAppService();

            if ($whatsapp->isEnabled()) {
                $updatedBooking = $this->bookingModel->getById($bookingId);
                $whatsapp->sendRescheduleNotification($updatedBooking, $oldDate, $oldTime);
            }

            $this->setFlash('success', 'Booking berhasil di-reschedule');
        } else {
            $this->setFlash('error', 'Gagal reschedule booking');
        }

        redirect('admin/bookingDetail/' . $booking->booking_code);
    }

    /**
     * Bookings list
     */
    public function bookings()
    {
        $status = $this->input('status') ?? 'all';

        $allBookings = $this->bookingModel->getAll();

        // Filter by status if needed
        if ($status !== 'all') {
            $bookings = array_filter($allBookings, function ($booking) use ($status) {
                return $booking->status === $status;
            });
        } else {
            $bookings = $allBookings;
        }

        $data = [
            'title' => 'Kelola Reservasi',
            'bookings' => $bookings,
            'currentStatus' => $status,
            'user' => $this->getCurrentUser(),
            'flash' => $this->getFlash()
        ];
        echo $this->viewAdmin('admin/bookings/index', $data);
    }

    /**
     * Booking detail
     */
    public function bookingDetail($id)
    {
        $bookingModel = $this->model('Booking');
        $booking = $bookingModel->getByCode($id);

        if (!$booking) {
            $this->setFlash('error', 'Reservasi tidak ditemukan.');
            redirect('admin/bookings');
        }

        // Get reschedule history
        $reschedule_history = $bookingModel->getRescheduleHistory($booking->id);

        $data = [
            'title' => 'Detail Reservasi',
            'booking' => $booking,
            'reschedule_history' => $reschedule_history,
            'user' => $this->getCurrentUser(),
            'flash' => $this->getFlash()
        ];
        echo $this->viewAdmin('admin/bookings/detail', $data);
    }

    /**
     * Update booking status
     */
    public function updateBookingStatus()
    {
        if (!$this->isPost()) {
            redirect('admin/bookings');
        }

        $bookingId = filter_var($this->input('booking_id'), FILTER_VALIDATE_INT);
        $status = $this->input('status');
        $notes = $this->input('notes');

        if (!$bookingId || !in_array($status, ['pending', 'confirmed', 'completed', 'cancelled'])) {
            $this->setFlash('error', 'Data tidak valid.');
            redirect('admin/bookings');
        }

        $this->bookingModel->updateStatus($bookingId, $status);

        // Update notes if provided
        if (!empty($notes)) {
            $this->bookingModel->updateNotes($bookingId, $notes);
        }

        $this->setFlash('success', 'Status reservasi berhasil diperbarui.');
        redirect('admin/bookings');
    }

    /**
     * Get therapist schedule (AJAX)
     */
    public function getTherapistSchedule()
    {
        header('Content-Type: application/json');

        $therapistId = isset($_GET['therapist_id']) ? (int) $_GET['therapist_id'] : 0;

        if (!$therapistId) {
            echo json_encode(['success' => false]);
            exit;
        }

        $availabilityModel = $this->model('Availability');
        $scheduleData = $availabilityModel->getWeeklySchedule($therapistId);

        // Convert to associative array by day
        $schedule = [];
        foreach ($scheduleData as $row) {
            $schedule[$row->day_of_week] = [
                'is_available' => (bool) $row->is_available,
                'start_time' => $row->start_time,
                'end_time' => $row->end_time
            ];
        }

        echo json_encode([
            'success' => true,
            'schedule' => $schedule
        ]);
        exit;
    }

    /**
     * Save therapist schedule (AJAX)
     */
    public function saveTherapistSchedule()
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $therapistId = $input['therapist_id'] ?? 0;
        $schedule = $input['schedule'] ?? [];

        if (!$therapistId || empty($schedule)) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            exit;
        }

        try {
            $availabilityModel = $this->model('Availability');

            foreach ($schedule as $day => $times) {
                if ($times['is_available']) {
                    $availabilityModel->setDayAvailability(
                        $therapistId,
                        $day,
                        $times['start_time'],
                        $times['end_time']
                    );
                } else {
                    // Set as unavailable
                    $availabilityModel->setDayAvailability(
                        $therapistId,
                        $day,
                        null,
                        null
                    );
                }
            }

            echo json_encode(['success' => true, 'message' => 'Schedule saved successfully']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Get therapist overrides (AJAX)
     */
    public function getTherapistOverrides()
    {
        header('Content-Type: application/json');

        $therapistId = isset($_GET['therapist_id']) ? (int) $_GET['therapist_id'] : 0;

        if (!$therapistId) {
            echo json_encode(['success' => false]);
            exit;
        }

        $availabilityModel = $this->model('Availability');
        $overrides = $availabilityModel->getOverrides($therapistId, date('Y-m-d'), date('Y-m-d', strtotime('+1 year')));

        echo json_encode([
            'success' => true,
            'overrides' => $overrides
        ]);
        exit;
    }

    /**
     * Add therapist override (AJAX)
     */
    public function addTherapistOverride()
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $therapistId = $input['therapist_id'] ?? 0;
        $date = $input['date'] ?? '';
        $reason = $input['reason'] ?? '';

        if (!$therapistId || !$date) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            exit;
        }

        try {
            $availabilityModel = $this->model('Availability');
            $result = $availabilityModel->addOverride($therapistId, $date, false, null, null, $reason);

            echo json_encode(['success' => (bool) $result, 'message' => $result ? 'Override added' : 'Failed to add']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Delete therapist override (AJAX)
     */
    public function deleteTherapistOverride()
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? 0;

        if (!$id) {
            echo json_encode(['success' => false]);
            exit;
        }

        $db = Database::getInstance();
        $result = $db->query("DELETE FROM availability_overrides WHERE id = ?", [$id]);

        echo json_encode(['success' => $result]);
        exit;
    }

    /**
     * Check if user is logged in
     */
    private function isLoggedIn()
    {
        require_once SITE_ROOT . '/core/SecureAuth.php';
        return SecureAuth::isLoggedIn();
    }

    /**
     * Get current user data
     */
    private function getCurrentUser()
    {
        return (object) [
            'id' => SecureAuth::getUser()['user_id'] ?? null,
            'name' => $_SESSION['user_name'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'role' => $_SESSION['user_role'] ?? ''
        ];
    }

    /**
     * Load admin view (no header/footer templates)
     */
    protected function viewAdmin($view, $data = [])
    {
        $viewFile = SITE_ROOT . '/app/views/' . $view . '.php';

        if (file_exists($viewFile)) {
            extract($data);
            ob_start();
            include $viewFile;
            return ob_get_clean();
        }

        throw new Exception("View {$view} not found");
    }
}
