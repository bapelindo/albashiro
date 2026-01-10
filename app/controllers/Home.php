<?php
/**
 * Albashiro - Home Controller
 */

class Home extends Controller
{

    private $therapistModel;
    private $serviceModel;
    private $testimonialModel;
    private $faqModel;
    private $bookingModel;

    public function __construct()
    {
        $this->therapistModel = $this->model('Therapist');
        $this->serviceModel = $this->model('Service');
        $this->testimonialModel = $this->model('Testimonial');
        $this->faqModel = $this->model('Faq');
        $this->bookingModel = $this->model('Booking');
    }

    /**
     * Landing page
     */
    public function index()
    {
        $data = [
            'title' => 'Beranda',
            'therapists' => $this->therapistModel->getAll(true),
            'services' => $this->serviceModel->getFeatured(),
            'allServices' => $this->serviceModel->getAll(),
            'testimonials' => $this->testimonialModel->getFeatured(6),
            'faqs' => $this->faqModel->getAll(),
            'flash' => $this->getFlash()
        ];

        echo $this->view('home/index', $data);
    }

    /**
     * Process booking form
     */
    public function book()
    {
        if (!$this->isPost()) {
            redirect('pages/reservasi');
        }

        // Verify CSRF token
        if (!verify_csrf($this->input('csrf_token'))) {
            $this->setFlash('error', 'Sesi tidak valid. Silakan coba lagi.');
            redirect('pages/reservasi');
        }

        // Validate inputs
        $errors = [];

        $therapistId = filter_var($this->input('therapist_id'), FILTER_VALIDATE_INT);
        $serviceId = filter_var($this->input('service_id'), FILTER_VALIDATE_INT);
        $clientName = $this->input('client_name');
        $waNumber = preg_replace('/[^0-9]/', '', $this->input('wa_number'));
        $email = filter_var($this->input('email'), FILTER_VALIDATE_EMAIL);
        $problemDescription = $this->input('problem_description');
        $appointmentDate = $this->input('appointment_date');
        $appointmentTime = $this->input('appointment_time');

        // Validation
        if (!$therapistId) {
            $errors[] = 'Pilih terapis yang valid';
        }
        if (empty($clientName) || strlen($clientName) < 3) {
            $errors[] = 'Nama lengkap minimal 3 karakter';
        }
        if (empty($waNumber) || strlen($waNumber) < 10) {
            $errors[] = 'Nomor WhatsApp tidak valid';
        }
        if (empty($problemDescription) || strlen($problemDescription) < 10) {
            $errors[] = 'Deskripsi keluhan minimal 10 karakter';
        }
        if (empty($appointmentDate)) {
            $errors[] = 'Pilih tanggal yang diinginkan';
        } else {
            $dateObj = strtotime($appointmentDate);
            if ($dateObj < strtotime('today')) {
                $errors[] = 'Tanggal tidak boleh di masa lalu';
            }
        }
        if (empty($appointmentTime)) {
            $errors[] = 'Pilih waktu yang diinginkan';
        }

        if (!empty($errors)) {
            $this->setFlash('error', implode('<br>', $errors));
            redirect('pages/reservasi');
        }

        // Check availability
        if (!$this->bookingModel->checkAvailability($therapistId, $appointmentDate, $appointmentTime)) {
            $this->setFlash('error', 'Mohon maaf, jadwal tersebut sudah terisi. Silakan pilih waktu lain.');
            redirect('pages/reservasi');
        }

        // Format WhatsApp number
        if (substr($waNumber, 0, 1) === '0') {
            $waNumber = '62' . substr($waNumber, 1);
        } elseif (substr($waNumber, 0, 2) !== '62') {
            $waNumber = '62' . $waNumber;
        }

        // Create booking
        try {
            $bookingCode = $this->bookingModel->create([
                'therapist_id' => $therapistId,
                'service_id' => $serviceId ?: null,
                'client_name' => $clientName,
                'wa_number' => $waNumber,
                'email' => $email ?: null,
                'problem_description' => $problemDescription,
                'appointment_date' => $appointmentDate,
                'appointment_time' => $appointmentTime
            ]);

            // Get therapist and service names for WhatsApp message
            $therapist = $this->therapistModel->getById($therapistId);
            $service = $serviceId ? $this->serviceModel->getById($serviceId) : null;

            // Format date and time for WhatsApp message
            $formattedDate = format_date_id($appointmentDate);
            $formattedDateTime = $formattedDate . ' pukul ' . substr($appointmentTime, 0, 5) . ' WIB';

            // Generate WhatsApp link for client
            $waLink = generate_wa_link(
                $therapist->name,
                $service ? $service->name : 'Konsultasi Umum',
                $clientName,
                $formattedDateTime
            );

            // Send notification to WhatsApp group
            require_once __DIR__ . '/../cron/send_to_group.php';
            sendBookingToGroup([
                'client_name' => $clientName,
                'wa_number' => $waNumber,
                'therapist_name' => $therapist->name,
                'service_name' => $service ? $service->name : 'Konsultasi Umum',
                'formatted_datetime' => $formattedDateTime,
                'booking_code' => $bookingCode,
                'problem_description' => $problemDescription
            ]);

            // Store success message with booking code
            $_SESSION['booking_success'] = [
                'code' => $bookingCode,
                'wa_link' => $waLink,
                'therapist' => $therapist->name,
                'date' => $formattedDateTime,
                'service' => $service ? $service->name : 'Konsultasi Umum'
            ];

            redirect('home/success');

        } catch (Exception $e) {
            $this->setFlash('error', 'Terjadi kesalahan. Silakan coba lagi.');
            redirect('pages/reservasi');
        }
    }

    /**
     * Booking success page
     */
    public function success()
    {
        if (!isset($_SESSION['booking_success'])) {
            redirect('');
        }

        $data = [
            'title' => 'Reservasi Berhasil',
            'booking' => $_SESSION['booking_success']
        ];

        // Do not unset session immediately to allow refresh, 
        // but typically better to unset after display. 
        // The original code unset it. I will follow that pattern or check if I need to keep it.
        // Let's unset it after the view is rendered? No, controller renders then exits.
        // I'll unset it in the view or just here.
        // Reverting to original pattern:
        // unset($_SESSION['booking_success']); 
        // Wait, if I unset it here, then `echo $this->view` works because I passed $data.

        echo $this->view('home/success', $data);

        // Clear session after loading view data
        unset($_SESSION['booking_success']);
    }

    /**
     * About page
     */
    public function about()
    {
        $data = [
            'title' => 'Tentang Kami',
            'therapists' => $this->therapistModel->getAll()
        ];

        echo $this->view('home/about', $data);
    }

    /**
     * Services page
     */
    public function services()
    {
        $data = [
            'title' => 'Layanan Kami',
            'services' => $this->serviceModel->getAll(),
            'groupedServices' => $this->serviceModel->getGroupedByAudience()
        ];

        echo $this->view('home/services', $data);
    }

    /**
     * Contact page
     */
    public function contact()
    {
        $data = [
            'title' => 'Hubungi Kami'
        ];

        echo $this->view('home/contact', $data);
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

        // Get slots from availability (respects schedule + overrides)
        $slots = $availabilityModel->getAvailableSlots($therapistId, $date, 60);

        // Filter out already booked slots
        $availableSlots = [];
        foreach ($slots as $slot) {
            $isBooked = $this->bookingModel->isSlotBooked($therapistId, $date, $slot['time']);
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
}
