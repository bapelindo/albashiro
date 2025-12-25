<!-- Reservasi Page -->
<section class="pt-32 pb-16 bg-gradient-to-br from-primary-50 via-cream-50 to-lavender-50 islamic-pattern">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto" data-aos="fade-up">
            <span
                class="inline-block px-4 py-2 bg-primary-100 text-primary-800 rounded-full text-sm font-semibold mb-4">
                <i class="fas fa-calendar-alt mr-2"></i>Reservasi
            </span>
            <h1 class="text-4xl md:text-5xl font-bold text-primary-900 mb-6">
                Buat <span class="gradient-text">Janji Temu</span>
            </h1>
            <p class="text-lg text-gray-600">
                Isi form di bawah untuk membuat reservasi. Tim kami akan menghubungi Anda untuk konfirmasi.
            </p>
        </div>
    </div>
</section>

<!-- Flash Messages -->
<?php if (isset($flash) && $flash): ?>
    <div class="max-w-3xl mx-auto px-4 -mt-8 mb-8">
        <div
            class="<?= $flash['type'] === 'error' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-green-50 border-green-200 text-green-700' ?> px-6 py-4 rounded-xl border flex items-start space-x-3">
            <i class="fas <?= $flash['type'] === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle' ?> mt-0.5"></i>
            <p class="text-sm"><?= $flash['message'] ?></p>
        </div>
    </div>
<?php endif; ?>

<!-- Reservation Form -->
<section class="py-12 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl p-8 md:p-12 shadow-2xl border border-gray-100" data-aos="fade-up">
            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-gray-700">Progress Pengisian</span>
                    <span id="progress-text" class="text-sm font-bold text-primary-600">0%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                    <div id="progress-bar"
                        class="bg-gradient-to-r from-primary-600 to-accent-600 h-3 rounded-full transition-all duration-500 ease-out"
                        style="width: 0%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    <span id="progress-hint">Mulai dengan memilih terapis</span>
                </p>
            </div>

            <form action="<?= base_url('home/book') ?>" method="POST" id="reservation-form">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <div class="space-y-6">
                    <!-- Therapist Select -->
                    <div class="relative">
                        <label class="block text-sm font-semibold text-primary-900 mb-2">
                            <i class="fas fa-user-md mr-2 text-accent-600"></i>Pilih Terapis <span
                                class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="therapist_id" required data-validate
                                class="w-full px-4 py-4 pr-12 border-2 border-gray-200 rounded-xl focus:border-accent-500 focus:ring-0 transition-all bg-cream-50">
                                <option value="">-- Pilih Terapis --</option>
                                <?php foreach ($therapists as $therapist): ?>
                                    <option value="<?= $therapist->id ?>"><?= e($therapist->name) ?> -
                                        <?= e($therapist->specialty) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i
                                class="fas fa-check-circle absolute right-4 top-1/2 -translate-y-1/2 text-green-500 text-xl opacity-0 transition-opacity field-check"></i>
                        </div>
                    </div>

                    <!-- Service Select -->
                    <div>
                        <label class="block text-sm font-semibold text-primary-900 mb-2">
                            <i class="fas fa-hand-holding-heart mr-2 text-accent-600"></i>Pilih Layanan
                        </label>
                        <select name="service_id"
                            class="w-full px-4 py-4 border-2 border-gray-200 rounded-xl focus:border-accent-500 focus:ring-0 transition-colors bg-cream-50">
                            <option value="">-- Pilih Layanan (Opsional) --</option>
                            <?php foreach ($services as $service): ?>
                                <option value="<?= $service->id ?>"><?= e($service->name) ?> -
                                    <?= format_rupiah($service->price) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Two Columns -->
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-primary-900 mb-2">
                                <i class="fas fa-user mr-2 text-accent-600"></i>Nama Lengkap <span
                                    class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" name="client_name" required minlength="3" data-validate
                                    placeholder="Masukkan nama lengkap"
                                    class="w-full px-4 py-4 pr-12 border-2 border-gray-200 rounded-xl focus:border-accent-500 focus:ring-0 transition-all bg-cream-50">
                                <i
                                    class="fas fa-check-circle absolute right-4 top-1/2 -translate-y-1/2 text-green-500 text-xl opacity-0 transition-opacity field-check"></i>
                            </div>
                            <p class="text-xs text-red-500 mt-1 hidden field-error">Nama minimal 3 karakter</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-primary-900 mb-2">
                                <i class="fab fa-whatsapp mr-2 text-accent-600"></i>Nomor WhatsApp <span
                                    class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="tel" name="wa_number" required placeholder="081234567890" data-validate
                                    pattern="[0-9]{10,15}"
                                    class="w-full px-4 py-4 pr-12 border-2 border-gray-200 rounded-xl focus:border-accent-500 focus:ring-0 transition-all bg-cream-50">
                                <i
                                    class="fas fa-check-circle absolute right-4 top-1/2 -translate-y-1/2 text-green-500 text-xl opacity-0 transition-opacity field-check"></i>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Format: 081234567890 (akan otomatis diformat)</p>
                            <p class="text-xs text-red-500 mt-1 hidden field-error">Nomor WhatsApp tidak valid</p>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-primary-900 mb-2">
                                <i class="fas fa-envelope mr-2 text-accent-600"></i>Email
                            </label>
                            <div class="relative">
                                <input type="email" name="email" placeholder="email@example.com" data-validate
                                    class="w-full px-4 py-4 pr-12 border-2 border-gray-200 rounded-xl focus:border-accent-500 focus:ring-0 transition-all bg-cream-50">
                                <i
                                    class="fas fa-check-circle absolute right-4 top-1/2 -translate-y-1/2 text-green-500 text-xl opacity-0 transition-opacity field-check"></i>
                            </div>
                            <p class="text-xs text-red-500 mt-1 hidden field-error">Format email tidak valid</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-primary-900 mb-2">
                                <i class="fas fa-calendar mr-2 text-accent-600"></i>Tanggal <span
                                    class="text-red-500">*</span>
                            </label>
                            <input type="date" name="appointment_date" required min="<?= date('Y-m-d') ?>"
                                class="w-full px-4 py-4 border-2 border-gray-200 rounded-xl focus:border-accent-500 focus:ring-0 transition-colors bg-cream-50">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-primary-900 mb-2">
                            <i class="fas fa-clock mr-2 text-accent-600"></i>Waktu <span class="text-red-500">*</span>
                        </label>
                        <select name="appointment_time" id="appointment_time" required
                            class="w-full px-4 py-4 border-2 border-gray-200 rounded-xl focus:border-accent-500 focus:ring-0 transition-colors bg-cream-50">
                            <option value="">-- Pilih Tanggal dan Terapis Terlebih Dahulu --</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Waktu yang tersedia akan muncul setelah memilih terapis
                            dan tanggal</p>
                    </div>

                    <!-- Problem Description -->
                    <div>
                        <label class="block text-sm font-semibold text-primary-900 mb-2">
                            <i class="fas fa-comment-alt mr-2 text-accent-600"></i>Ceritakan Keluhan Anda <span
                                class="text-red-500">*</span>
                        </label>
                        <textarea name="problem_description" required minlength="10" rows="4"
                            placeholder="Jelaskan secara singkat masalah atau keluhan yang ingin Anda konsultasikan..."
                            class="w-full px-4 py-4 border-2 border-gray-200 rounded-xl focus:border-accent-500 focus:ring-0 transition-colors bg-cream-50 resize-none"></textarea>
                    </div>
                </div>

                <!-- Privacy Notice -->
                <div class="mt-6 p-4 bg-primary-50 rounded-xl flex items-start space-x-3">
                    <i class="fas fa-shield-alt text-primary-600 mt-0.5"></i>
                    <p class="text-sm text-gray-600">
                        Data Anda dijaga kerahasiaannya dan hanya digunakan untuk keperluan konsultasi.
                    </p>
                </div>

                <!-- Submit -->
                <div class="mt-8">
                    <button type="submit"
                        class="w-full py-4 bg-gradient-to-r from-primary-700 to-accent-600 text-white text-lg font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all flex items-center justify-center">
                        <i class="fas fa-paper-plane mr-3"></i>
                        Kirim Reservasi
                    </button>
                </div>
            </form>
        </div>

        <!-- Alternative Contact -->
        <div class="mt-8 text-center">
            <p class="text-gray-500 mb-4">Atau hubungi langsung via WhatsApp:</p>
            <a href="https://wa.me/<?= ADMIN_WHATSAPP ?>?text=Halo, saya ingin membuat reservasi hipnoterapi"
                target="_blank"
                class="inline-flex items-center px-6 py-3 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-full shadow-lg transition-all">
                <i class="fab fa-whatsapp text-xl mr-2"></i>
                Chat WhatsApp
            </a>
        </div>
    </div>
</section>

<!-- Custom Styles for Enhanced UX -->
<style>
    /* Smooth focus animations */
    input:focus,
    select:focus,
    textarea:focus {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(139, 92, 246, 0.15);
    }

    /* Checkmark pulse animation */
    @keyframes checkPulse {

        0%,
        100% {
            transform: translate(-50%, -50%) scale(1);
        }

        50% {
            transform: translate(-50%, -50%) scale(1.2);
        }
    }

    .field-check {
        animation: checkPulse 0.5s ease-in-out;
    }

    /* Progress bar shimmer effect */
    @keyframes shimmer {
        0% {
            background-position: -1000px 0;
        }

        100% {
            background-position: 1000px 0;
        }
    }

    #progress-bar {
        background: linear-gradient(90deg,
                #7c3aed 0%,
                #a855f7 25%,
                #ec4899 50%,
                #a855f7 75%,
                #7c3aed 100%);
        background-size: 200% 100%;
        animation: shimmer 3s linear infinite;
    }

    /* Input hover glow */
    input:hover:not(:focus),
    select:hover:not(:focus),
    textarea:hover:not(:focus) {
        border-color: #c4b5fd;
        box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
    }

    /* Success state animation */
    .border-green-300 {
        animation: successGlow 0.6s ease-in-out;
    }

    @keyframes successGlow {

        0%,
        100% {
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
        }

        50% {
            box-shadow: 0 0 0 6px rgba(34, 197, 94, 0.2);
        }
    }

    /* Error shake animation */
    .border-red-300 {
        animation: errorShake 0.5s ease-in-out;
    }

    @keyframes errorShake {

        0%,
        100% {
            transform: translateX(0);
        }

        25% {
            transform: translateX(-5px);
        }

        75% {
            transform: translateX(5px);
        }
    }

    /* Submit button pulse on hover */
    button[type="submit"]:hover {
        animation: buttonPulse 1.5s ease-in-out infinite;
    }

    @keyframes buttonPulse {

        0%,
        100% {
            box-shadow: 0 10px 25px rgba(139, 92, 246, 0.3);
        }

        50% {
            box-shadow: 0 15px 35px rgba(139, 92, 246, 0.5);
        }
    }

    /* Label float effect on focus */
    input:focus+label,
    select:focus+label,
    textarea:focus+label {
        color: #7c3aed;
        transform: translateY(-2px);
    }

    /* Smooth transitions for all interactive elements */
    input,
    select,
    textarea,
    button {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Progress hint fade in */
    #progress-hint {
        animation: fadeIn 0.5s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Field wrapper hover effect */
    .relative:hover .field-check {
        transform: translate(-50%, -50%) scale(1.1);
    }

    /* Gradient text animation for progress */
    #progress-text {
        background: linear-gradient(90deg, #7c3aed, #ec4899, #7c3aed);
        background-size: 200% 100%;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: shimmer 3s linear infinite;
    }

    /* Loading state for time select */
    select:disabled {
        cursor: wait;
        opacity: 0.7;
        animation: pulse 1.5s ease-in-out infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 0.7;
        }

        50% {
            opacity: 0.5;
        }
    }

    /* Enhanced shadow on form container */
    .shadow-2xl {
        box-shadow: 0 25px 50px -12px rgba(139, 92, 246, 0.15);
    }

    /* Smooth scroll behavior */
    html {
        scroll-behavior: smooth;
    }
</style>

<script>
    // Load available time slots when therapist and date are selected
    document.addEventListener('DOMContentLoaded', function () {
        const therapistSelect = document.querySelector('select[name="therapist_id"]');
        const serviceSelect = document.querySelector('select[name="service_id"]');
        const dateInput = document.querySelector('input[name="appointment_date"]');
        const timeSelect = document.getElementById('appointment_time');
        const nameInput = document.querySelector('input[name="client_name"]');
        const waInput = document.querySelector('input[name="wa_number"]');
        const emailInput = document.querySelector('input[name="email"]');
        const problemTextarea = document.querySelector('textarea[name="problem_description"]');
        const form = document.querySelector('form');

        // ===== LOCALSTORAGE PERSISTENCE =====
        const STORAGE_KEY = 'reservasi_form_data';

        // Load saved data from localStorage
        function loadFormData() {
            try {
                const savedData = localStorage.getItem(STORAGE_KEY);
                if (savedData) {
                    const data = JSON.parse(savedData);

                    if (data.therapist_id) therapistSelect.value = data.therapist_id;
                    if (data.service_id) serviceSelect.value = data.service_id;
                    if (data.appointment_date) dateInput.value = data.appointment_date;
                    if (data.client_name) nameInput.value = data.client_name;
                    if (data.wa_number) waInput.value = data.wa_number;
                    if (data.email) emailInput.value = data.email;
                    if (data.problem_description) problemTextarea.value = data.problem_description;

                    // Trigger validation for saved fields
                    setTimeout(() => {
                        [therapistSelect, nameInput, waInput, emailInput].forEach(field => {
                            if (field.value) validateField(field);
                        });
                        updateProgress();
                    }, 100);

                    // Trigger slot loading if therapist and date are selected
                    if (data.therapist_id && data.appointment_date) {
                        loadAvailableSlots(data.appointment_time);
                    }
                }
            } catch (e) {
                console.error('Error loading form data:', e);
            }
        }

        // Save form data to localStorage
        function saveFormData() {
            try {
                const data = {
                    therapist_id: therapistSelect.value,
                    service_id: serviceSelect.value,
                    appointment_date: dateInput.value,
                    appointment_time: timeSelect.value,
                    client_name: nameInput.value,
                    wa_number: waInput.value,
                    email: emailInput.value,
                    problem_description: problemTextarea.value
                };
                localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
            } catch (e) {
                console.error('Error saving form data:', e);
            }
        }

        // Clear saved data
        function clearFormData() {
            localStorage.removeItem(STORAGE_KEY);
        }

        // Save data on input change
        [therapistSelect, serviceSelect, dateInput, timeSelect, nameInput, waInput, emailInput, problemTextarea].forEach(element => {
            element.addEventListener('change', saveFormData);
            element.addEventListener('input', saveFormData);
        });

        // Clear data on successful form submission
        form.addEventListener('submit', function () {
            clearFormData();
        });

        // ===== PROGRESS TRACKING =====
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');
        const progressHint = document.getElementById('progress-hint');
        const requiredFields = [therapistSelect, dateInput, timeSelect, nameInput, waInput, problemTextarea];
        const progressHints = [
            'Mulai dengan memilih terapis',
            'Pilih tanggal yang diinginkan',
            'Pilih waktu yang tersedia',
            'Masukkan nama lengkap Anda',
            'Masukkan nomor WhatsApp',
            'Ceritakan keluhan Anda'
        ];

        function updateProgress() {
            let filledCount = 0;
            requiredFields.forEach(field => {
                if (field.value && field.value.trim() !== '') filledCount++;
            });

            const percentage = Math.round((filledCount / requiredFields.length) * 100);
            const wasComplete = progressBar.style.width === '100%';
            
            progressBar.style.width = percentage + '%';
            progressText.textContent = percentage + '%';

            if (percentage === 100) {
                progressHint.innerHTML = '<i class="fas fa-check-circle mr-1"></i>Formulir lengkap! Siap untuk dikirim';
                progressHint.className = 'text-xs text-green-600 mt-2 font-semibold';
                
                // Celebration effect on first completion
                if (!wasComplete) {
                    celebrateCompletion();
                }
            } else {
                const nextEmptyIndex = requiredFields.findIndex(f => !f.value || f.value.trim() === '');
                if (nextEmptyIndex !== -1) {
                    progressHint.innerHTML = '<i class="fas fa-info-circle mr-1"></i>' + progressHints[nextEmptyIndex];
                    progressHint.className = 'text-xs text-gray-500 mt-2';
                }
            }
        }

        // Celebration effect
        function celebrateCompletion() {
            // Add sparkle effect to progress bar
            progressBar.style.boxShadow = '0 0 20px rgba(139, 92, 246, 0.6)';
            setTimeout(() => {
                progressBar.style.boxShadow = '';
            }, 1000);

            // Animate submit button
            const submitBtn = document.querySelector('button[type="submit"]');
            submitBtn.style.transform = 'scale(1.05)';
            submitBtn.style.boxShadow = '0 15px 40px rgba(139, 92, 246, 0.4)';
            setTimeout(() => {
                submitBtn.style.transform = '';
                submitBtn.style.boxShadow = '';
            }, 600);
        }

        // ===== FIELD VALIDATION =====
        function validateField(field) {
            const parent = field.closest('div').parentElement;
            const checkIcon = parent.querySelector('.field-check');
            const errorMsg = parent.querySelector('.field-error');
            let isValid = false;

            if (field.type === 'email') {
                isValid = field.value === '' || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value);
            } else if (field.name === 'wa_number') {
                isValid = /^[0-9]{10,15}$/.test(field.value.replace(/\D/g, ''));
            } else if (field.name === 'client_name') {
                isValid = field.value.length >= 3;
            } else if (field.tagName === 'SELECT') {
                isValid = field.value !== '';
            } else {
                isValid = field.value.trim() !== '';
            }

            if (checkIcon) {
                checkIcon.style.opacity = (isValid && field.value) ? '1' : '0';
                field.classList.toggle('border-green-300', isValid && field.value);
                field.classList.toggle('border-red-300', field.value && !isValid);
            }

            if (errorMsg) {
                errorMsg.classList.toggle('hidden', !field.value || isValid);
            }

            return isValid;
        }

        // ===== WHATSAPP AUTO-FORMAT =====
        waInput.addEventListener('blur', function () {
            if (this.value) {
                let cleaned = this.value.replace(/\D/g, '');
                if (cleaned.startsWith('08')) cleaned = '62' + cleaned.substring(1);
                if (!cleaned.startsWith('62') && cleaned.length > 0) cleaned = '62' + cleaned;
                this.value = cleaned;
                validateField(this);
            }
        });

        // ===== EVENT LISTENERS =====
        [therapistSelect, nameInput, waInput, emailInput].forEach(field => {
            field.addEventListener('input', function () {
                validateField(this);
                updateProgress();
                saveFormData();
            });
            field.addEventListener('change', function () {
                validateField(this);
                updateProgress();
                saveFormData();
            });
        });

        [serviceSelect, dateInput, timeSelect, problemTextarea].forEach(field => {
            field.addEventListener('change', () => { updateProgress(); saveFormData(); });
            field.addEventListener('input', () => { updateProgress(); saveFormData(); });
        });

        // ===== SLOT LOADING =====
        function loadAvailableSlots(savedTime = null) {
            const therapistId = therapistSelect.value;
            const date = dateInput.value;

            if (!therapistId || !date) {
                timeSelect.innerHTML = '<option value="">-- Pilih Tanggal dan Terapis Terlebih Dahulu --</option>';
                return;
            }

            // Show loading
            timeSelect.innerHTML = '<option value="">Loading...</option>';
            timeSelect.disabled = true;

            // Fetch available slots
            fetch(`<?= base_url('home/getAvailableSlots') ?>?therapist_id=${therapistId}&date=${date}`)
                .then(response => response.json())
                .then(data => {
                    timeSelect.disabled = false;

                    if (data.success && data.slots && data.slots.length > 0) {
                        timeSelect.innerHTML = '<option value="">-- Pilih Waktu --</option>' +
                            data.slots.map(slot => `<option value="${slot.time}">${slot.display}</option>`).join('');

                        // Restore saved time if available
                        if (savedTime && data.slots.some(slot => slot.time === savedTime)) {
                            timeSelect.value = savedTime;
                        }
                    } else {
                        timeSelect.innerHTML = '<option value="">Tidak ada waktu tersedia untuk tanggal ini</option>';
                    }
                })
                .catch(error => {
                    console.error('Error loading slots:', error);
                    timeSelect.disabled = false;
                    timeSelect.innerHTML = '<option value="">Error loading slots</option>';
                });
        }

        // Load slots when therapist or date changes
        therapistSelect.addEventListener('change', () => loadAvailableSlots());
        dateInput.addEventListener('change', () => loadAvailableSlots());

        // Load saved form data on page load
        loadFormData();
    });
</script>