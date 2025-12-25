/**
 * Reservasi Page JavaScript
 * Handles form validation, progress tracking, localStorage persistence, and slot loading
 */

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

        // Fetch available slots (BASE_URL is defined in header.php)
        fetch(`${window.BASE_URL}home/getAvailableSlots?therapist_id=${therapistId}&date=${date}`)
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
