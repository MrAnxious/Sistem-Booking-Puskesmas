<?php
// File: register.php

// PENTING: Aktifkan baris di bawah ini dan hapus fungsi dummy showAlert()
include '../config/database.php'; 

// Dummy function untuk preview (Hapus ini jika include database sudah aktif)
if (!function_exists('showAlert')) {
    /**
     * Fungsi dummy untuk menampilkan alert pesan (Error/Success) di halaman register.
     * Gantikan dengan logika penanganan error PHP yang sebenarnya.
     */
    function showAlert() {
        // Contoh: Tampilkan alert jika NIK sudah terdaftar
        if (isset($_GET['error']) && $_GET['error'] == 'nik_exists') {
            echo '<div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> Pendaftaran Gagal! NIK yang Anda masukkan **sudah terdaftar** di sistem.</div>';
        }
        // Contoh alert sukses (misalnya setelah redirect post)
        if (isset($_GET['success']) && $_GET['success'] == 'true') {
             // Biasanya tidak terjadi di register, tapi untuk contoh
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pasien - Puskesmas Nalumsari</title>
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.ico">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            /* Warna Medis Hijau Modern */
            --primary-color: #047857; /* Dark Emerald */
            --primary-dark: #065f46;  /* Deeper Emerald */
            --secondary-color: #34d399; /* Light Emerald */
            --accent-color: #a7f3d0;  /* Pale Emerald */
            
            --text-primary: #1f2937;  /* Dark Slate */
            --text-secondary: #4b5563;
            --text-light: #9ca3af;
            
            --bg-white: #ffffff;
            --bg-light: #f9fafb;
            --border-color: #e5e7eb;
            --error-color: #ef4444;
            
            --shadow-light: 0 4px 20px rgba(0, 0, 0, 0.08);
            --shadow-heavy: 0 20px 60px rgba(0, 0, 0, 0.15);
            --border-radius: 16px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            outline: none;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
            color: var(--text-primary);
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(255,255,255,0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255,255,255,0.08) 0%, transparent 50%);
            z-index: -1;
        }

        .container {
            background: var(--bg-white);
            border-radius: 24px;
            box-shadow: var(--shadow-heavy);
            width: 100%;
            max-width: 720px;
            overflow: hidden;
            position: relative;
            animation: slideUp 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            border: 1px solid rgba(255, 255, 255, 0.5); /* Subtle white border */
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* --- Header Styling --- */
        .header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #10b981 100%); /* Emerald 600 */
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }

        .logo-container {
            display: flex;
            justify-content: center;
            margin-bottom: 15px;
        }

        .logo {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .logo i {
            font-size: 40px;
            color: white;
        }

        .header h1 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 30px;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .header p {
            font-size: 15px;
            opacity: 0.9;
        }

        /* --- Body & Form Styling --- */
        .body {
            padding: 40px 35px 35px;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 28px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.4s ease-out;
            font-size: 15px;
        }

        .alert-error {
            background: #fef2f2;
            color: var(--error-color);
            border: 1px solid #fecaca;
        }

        .alert-success {
            background: #ecfdf5;
            color: var(--primary-dark);
            border: 1px solid #a7f3d0;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        /* Ensure single column on small screens */
        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            color: var(--text-primary);
            font-weight: 600;
            font-size: 14px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .form-control {
            width: 100%;
            padding: 14px 18px 14px 50px;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 16px;
            font-weight: 500;
            background: var(--bg-light);
            transition: var(--transition);
            color: var(--text-primary);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            background: var(--bg-white);
            box-shadow: 0 0 0 4px rgba(4, 120, 87, 0.1);
            transform: translateY(-1px);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
            padding-left: 18px; /* No icon for textarea */
            padding-top: 18px;
        }
        
        select.form-control {
            padding-left: 18px; /* No icon for select */
            appearance: none;
            /* Custom arrow */
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%239ca3af' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: calc(100% - 15px) center;
        }

        .form-group .input-icon {
            position: absolute;
            left: 18px;
            color: var(--text-light);
            font-size: 16px;
            z-index: 2;
            transition: var(--transition);
        }

        .form-control:focus ~ .input-icon {
            color: var(--primary-color);
        }
        
        /* Icon positioning for non-textarea, non-select */
        .form-group:not(.textarea-group):not(.select-group) .input-icon {
            top: 50%;
            transform: translateY(-50%);
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            cursor: pointer;
            color: var(--text-light);
            font-size: 16px;
            transition: var(--transition);
            z-index: 3;
            background: none;
            border: none;
            padding: 5px;
            top: 50%;
            transform: translateY(-50%);
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }

        /* --- Button Styling --- */
        .btn-submit {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, var(--primary-color) 0%, #10b981 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 15px;
            box-shadow: 0 8px 20px rgba(4, 120, 87, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(4, 120, 87, 0.4);
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-color) 100%);
        }

        .btn-submit:active {
            transform: translateY(-1px);
        }
        
        .btn-submit .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin { to { transform: rotate(360deg); } }
        
        .btn-submit.loading .btn-text { display: none; }
        .btn-submit.loading .spinner { display: block; }
        .btn-submit.loading { pointer-events: none; opacity: 0.9; }

        /* --- Error Messages & Links --- */
        .error-message {
            color: var(--error-color);
            font-size: 12px;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .error-message.show {
            opacity: 1;
        }
        
        .form-control.error {
            border-color: var(--error-color);
            background-color: #fef2f2;
        }

        .links {
            text-align: center;
            margin-top: 32px;
            padding-top: 25px;
            border-top: 1px solid var(--border-color);
        }

        .links a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .links a:hover {
            color: var(--primary-dark);
            transform: translateX(4px);
        }

        .copyright {
            text-align: center;
            margin-top: 24px;
            padding: 16px;
            font-size: 13px;
            color: var(--text-secondary);
            opacity: 0.7;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <div class="logo-container">
                <div class="logo">
                    <i class="fas fa-user-plus"></i>
                </div>
            </div>
            <h1>Pendaftaran Pasien Baru</h1>
            <p>Isi data diri Anda dengan lengkap dan benar untuk mendaftar.</p>
        </div>
        
        <div class="body">
            <?php showAlert(); ?>
            
            <form method="POST" action="register_process.php" id="registerForm" novalidate>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nama_lengkap">
                            <i class="fas fa-user"></i>
                            Nama Lengkap
                        </label>
                        <div class="input-wrapper">
                            <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control" required 
                                   placeholder="Masukkan nama lengkap Anda">
                            <i class="fas fa-user input-icon"></i>
                        </div>
                        <div class="error-message" id="nama_lengkapError">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>Nama lengkap minimal 2 karakter.</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="nik">
                            <i class="fas fa-id-card"></i>
                            Nomor Induk Kependudukan (NIK)
                        </label>
                        <div class="input-wrapper">
                            <input type="text" id="nik" name="nik" class="form-control" required 
                                   placeholder="Masukkan 16 digit NIK" maxlength="16" inputmode="numeric">
                            <i class="fas fa-id-card input-icon"></i>
                        </div>
                        <div class="error-message" id="nikError">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>NIK harus terdiri dari **16 digit angka**.</span>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group select-group">
                        <label for="jenis_kelamin">
                            <i class="fas fa-venus-mars"></i>
                            Jenis Kelamin
                        </label>
                        <div class="input-wrapper">
                            <select id="jenis_kelamin" name="jenis_kelamin" class="form-control" required>
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="error-message" id="jenis_kelaminError">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>Pilih jenis kelamin.</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="no_hp">
                            <i class="fas fa-phone"></i>
                            Nomor Telepon
                        </label>
                        <div class="input-wrapper">
                            <input type="text" id="no_hp" name="no_hp" class="form-control" required 
                                   placeholder="Contoh: 081234567890" maxlength="15" inputmode="tel">
                            <i class="fas fa-phone input-icon"></i>
                        </div>
                        <div class="error-message" id="no_hpError">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>Nomor telepon harus 10-15 digit.</span>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Alamat Email (Opsional, tapi disarankan)
                    </label>
                    <div class="input-wrapper">
                        <input type="email" id="email" name="email" class="form-control" 
                               placeholder="contoh@email.com">
                        <i class="fas fa-envelope input-icon"></i>
                    </div>
                    <div class="error-message" id="emailError">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>Masukkan alamat email yang valid.</span>
                    </div>
                </div>
                
                <div class="form-group textarea-group">
                    <label for="alamat">
                        <i class="fas fa-home"></i>
                        Alamat Lengkap
                    </label>
                    <div class="input-wrapper">
                        <textarea id="alamat" name="alamat" class="form-control" required 
                                  placeholder="Masukkan alamat lengkap (Jalan, RT/RW, Desa/Kelurahan, Kecamatan, dst)"></textarea>
                    </div>
                    <div class="error-message" id="alamatError">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>Alamat minimal 10 karakter.</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Kata Sandi
                    </label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" class="form-control" required 
                               placeholder="Buat kata sandi (minimal 6 karakter)" minlength="6">
                        <i class="fas fa-lock input-icon"></i>
                        <button type="button" class="password-toggle" id="togglePassword">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                    <div class="error-message" id="passwordError">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>Kata sandi minimal 6 karakter.</span>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit" id="submitBtn">
                    <span class="btn-text">Daftar Sekarang</span>
                    <div class="spinner"></div>
                </button>
            </form>
            
            <div class="links">
                <a href="login.php">
                    <i class="fas fa-arrow-left"></i>
                    Sudah punya akun? Login di sini
                </a>
            </div>
            
            <div class="copyright">
                &copy; <span id="tahun-copyright"></span> Puskesmas Nalumsari.
            </div>
        </div>
    </div>

    <script>
        // INISIALISASI
        document.getElementById("tahun-copyright").textContent = new Date().getFullYear();
        
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('#registerForm');
            const submitBtn = document.querySelector('#submitBtn');
            const togglePassword = document.querySelector('#togglePassword');
            const passwordInput = document.querySelector('#password');
            const inputs = document.querySelectorAll('.form-control');

            // FUNGSI UTILITY

            /**
             * Menandai input dengan status error
             * @param {HTMLElement} field - Elemen input
             * @param {HTMLElement} errorElement - Elemen pesan error
             * @param {string} message - Pesan error spesifik (opsional)
             */
            function showFieldError(field, errorElement, message) {
                field.classList.add('error');
                errorElement.classList.add('show');
                if (message) {
                    // Update pesan error dinamis jika diperlukan
                    errorElement.querySelector('span').textContent = message;
                }
            }

            /**
             * Menghilangkan status error pada input
             * @param {HTMLElement} field - Elemen input
             * @param {HTMLElement} errorElement - Elemen pesan error
             */
            function clearFieldError(field, errorElement) {
                field.classList.remove('error');
                errorElement.classList.remove('show');
            }

            /**
             * Validasi field tunggal
             * @param {HTMLElement} field - Elemen input, select, atau textarea
             * @returns {boolean} - Status validasi
             */
            function validateField(field) {
                const value = field.value.trim();
                const errorElement = document.getElementById(field.id + 'Error');
                let isValid = true;
                
                // Clear previous error
                clearFieldError(field, errorElement);

                if (field.required && value === '') {
                    isValid = false;
                } else {
                    switch(field.id) {
                        case 'nama_lengkap':
                            if (value.length > 0 && value.length < 2) {
                                isValid = false;
                                showFieldError(field, errorElement, 'Nama lengkap minimal 2 karakter');
                            }
                            break;
                        case 'nik':
                            // NIK harus 16 digit angka
                            if (value.length > 0 && (value.length !== 16 || !/^\d{16}$/.test(value))) {
                                isValid = false;
                                showFieldError(field, errorElement, 'NIK harus terdiri dari 16 digit angka');
                            }
                            break;
                        case 'jenis_kelamin':
                            if (value === '') {
                                isValid = false;
                                showFieldError(field, errorElement, 'Pilih jenis kelamin');
                            }
                            break;
                        case 'no_hp':
                            // Nomor telepon 10-15 digit
                            if (value.length > 0 && (value.length < 10 || value.length > 15 || !/^\d+$/.test(value))) {
                                isValid = false;
                                showFieldError(field, errorElement, 'Nomor telepon harus 10-15 digit');
                            }
                            break;
                        case 'email':
                            // Email bersifat opsional, hanya validasi jika diisi
                            if (value.length > 0) {
                                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                                if (!emailRegex.test(value)) {
                                    isValid = false;
                                    showFieldError(field, errorElement, 'Masukkan alamat email yang valid');
                                }
                            }
                            break;
                        case 'alamat':
                            if (value.length > 0 && value.length < 10) {
                                isValid = false;
                                showFieldError(field, errorElement, 'Alamat minimal 10 karakter');
                            }
                            break;
                        case 'password':
                            if (value.length > 0 && value.length < 6) {
                                isValid = false;
                                showFieldError(field, errorElement, 'Kata sandi minimal 6 karakter');
                            }
                            break;
                    }
                }

                if (!isValid) {
                    showFieldError(field, errorElement, errorElement.querySelector('span').textContent);
                } else if (errorElement) {
                    clearFieldError(field, errorElement);
                }
                
                return isValid;
            }

            // EVENT LISTENERS

            // 1. Toggle Password Visibility
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                this.querySelector('i').className = type === 'password' 
                    ? 'far fa-eye' 
                    : 'far fa-eye-slash';
            });

            // 2. Real-time validation on blur/change
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    validateField(this);
                });
                input.addEventListener('change', function() {
                    validateField(this); // Untuk select/checkbox
                });
            });

            // 3. NIK and Phone formatting (Hanya Angka)
            document.getElementById('nik').addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').slice(0, 16);
                validateField(this);
            });
            document.getElementById('no_hp').addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').slice(0, 15);
                validateField(this);
            });

            // 4. Form Submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                let allValid = true;
                
                // Validate all required fields upon submit
                inputs.forEach(field => {
                    if (!validateField(field)) {
                        allValid = false;
                    }
                });
                
                if (allValid) {
                    // Start loading state
                    submitBtn.classList.add('loading');
                    
                    // Ganti teks button dan tambahkan spinner
                    const textSpan = submitBtn.querySelector('.btn-text');
                    textSpan.textContent = 'Mendaftarkan...';

                    // Simulasikan delay pengiriman (Hapus setTimeout untuk produksi)
                    setTimeout(() => {
                        form.submit();
                    }, 1500);
                } else {
                    // Scroll ke field pertama yang error
                    const firstInvalid = document.querySelector('.form-control.error');
                    if (firstInvalid) {
                        firstInvalid.focus();
                        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
        });
    </script>
</body>
</html>