<?php 
// File: login.php

// PENTING: Aktifkan baris di bawah ini dan hapus fungsi dummy showAlert()
include '../config/database.php'; 

// Dummy function untuk preview (Hapus ini jika include database sudah aktif)
 if (!function_exists('showAlert')) {
    /**
     * Fungsi dummy untuk menampilkan alert error di halaman login.
     * Gantikan dengan logika penanganan error PHP yang sebenarnya.
     */
    function showAlert() {
        // Contoh: Tampilkan alert jika ada parameter 'error' di URL
        if (isset($_GET['error']) && $_GET['error'] == 'true') {
            echo '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> NIK atau Kata Sandi salah! Silakan coba lagi.</div>';
        }
        // Contoh alert sukses (misalnya setelah pendaftaran)
        if (isset($_GET['registered']) && $_GET['registered'] == 'true') {
            echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Pendaftaran berhasil! Silakan masuk menggunakan NIK Anda.</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pasien - Puskesmas Nalumsari</title>
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.ico">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            /* Medical Color Palette Modern */
            --primary: #059669;       /* Emerald 600 */
            --primary-hover: #047857; /* Emerald 700 */
            --primary-light: #d1fae5; /* Emerald 100 */
            --surface: #ffffff;
            --background: #f0fdf4;    /* Emerald 50 */
            
            --text-main: #1e293b;     /* Slate 800 */
            --text-muted: #64748b;    /* Slate 500 */
            --border: #e2e8f0;        /* Slate 200 */
            
            --error: #ef4444;
            --error-bg: #fef2f2;
            
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --radius: 12px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            outline: none;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background);
            /* Pattern Background Halus */
            background-image: radial-gradient(#10b981 0.5px, transparent 0.5px), radial-gradient(#10b981 0.5px, var(--background) 0.5px);
            background-size: 20px 20px;
            background-position: 0 0, 10px 10px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--text-main);
        }

        .login-wrapper {
            width: 100%;
            max-width: 440px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255, 255, 255, 0.5);
            overflow: hidden;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* --- Header Section --- */
        .card-header {
            padding: 40px 30px 20px;
            text-align: center;
        }

        .brand-logo {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary) 0%, #34d399 100%);
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
            transform: rotate(-5deg);
            transition: transform 0.3s ease;
        }

        .login-card:hover .brand-logo {
            transform: rotate(0deg) scale(1.05);
        }

        .brand-logo i {
            font-size: 32px;
            color: white;
        }

        .title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 24px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .subtitle {
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.5;
        }

        /* --- Form Section --- */
        .card-body {
            padding: 20px 30px 40px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 18px;
            transition: color 0.3s;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px 14px 48px; /* Left padding for icon */
            font-size: 15px;
            color: var(--text-main);
            background: #f8fafc;
            border: 2px solid transparent;
            border-radius: var(--radius);
            transition: all 0.2s ease;
        }

        .form-control:hover {
            background: #f1f5f9;
        }

        .form-control:focus {
            background: white;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-light);
        }

        .form-control:focus + .input-icon {
            color: var(--primary);
        }
        
        /* Error state for inputs */
        .form-control.error {
            border-color: var(--error);
            background-color: #fff5f5;
        }

        /* Toggle Password */
        .toggle-password {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-muted);
            font-size: 18px;
            padding: 0;
            transition: color 0.2s;
        }

        .toggle-password:hover {
            color: var(--primary);
        }

        /* Button */
        .btn-submit {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
            box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.2);
        }

        .btn-submit:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(5, 150, 105, 0.3);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* Loading Spinner */
        .spinner {
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
        .btn-submit.loading { pointer-events: none; opacity: 0.8; }

        /* Links */
        .auth-links {
            margin-top: 25px;
            text-align: center;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .link-item {
            font-size: 14px;
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .link-item:hover {
            color: var(--primary);
        }

        .divider {
            height: 1px;
            background: var(--border);
            margin: 20px 0;
        }

        .btn-back {
            color: var(--text-muted);
            font-size: 13px;
        }

        /* Alerts & Errors */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-error { 
            background: var(--error-bg); 
            color: var(--error); 
            border: 1px solid #fecaca; 
        }
        .alert-success { 
            background: var(--primary-light); 
            color: var(--primary-hover); 
            border: 1px solid #bbf7d0; 
        }

        .error-text {
            color: var(--error);
            font-size: 12px;
            margin-top: 6px;
            display: none;
            align-items: center;
            gap: 4px;
            animation: fadeIn 0.3s;
        }
        .error-text.show { display: flex; }
        
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        /* Footer */
        .copyright {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: var(--text-muted);
            opacity: 0.8;
        }

        /* Responsive */
        @media (max-width: 480px) {
            body { padding: 15px; }
            .card-header { padding: 30px 20px 10px; }
            .card-body { padding: 20px 20px 30px; }
            .brand-logo { width: 60px; height: 60px; margin-bottom: 15px; }
            .title { font-size: 20px; }
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <div class="login-card">
            <div class="card-header">
                <div class="brand-logo">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <h1 class="title">Selamat Datang</h1>
                <p class="subtitle">Silakan login menggunakan NIK Anda untuk mengakses layanan kesehatan online Puskesmas Nalumsari.</p>
            </div>

            <div class="card-body">
                <?php showAlert(); ?>

                <form method="POST" action="login_process.php" id="loginForm" novalidate>
                    
                    <div class="form-group">
                        <label for="nik" class="form-label">Nomor Induk Kependudukan (NIK)</label>
                        <div class="input-group">
                            <input type="text" id="nik" name="nik" class="form-control" 
                                placeholder="16 digit angka" 
                                maxlength="16" 
                                inputmode="numeric" 
                                pattern="[0-9]*" 
                                required>
                            <i class="fas fa-id-card input-icon"></i>
                        </div>
                        <div class="error-text" id="nikError">
                            <i class="fas fa-circle-exclamation"></i> NIK harus terdiri dari **16 angka** dan tidak boleh kosong.
                        </div>
                    </div>

                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <label for="password" class="form-label" style="margin-bottom: 0;">Kata Sandi</label>
                            <a href="forgot_password.php" style="font-size: 12px; color: var(--primary); text-decoration: none; font-weight: 500;">Lupa Sandi?</a>
                        </div>
                        <div class="input-group">
                            <input type="password" id="password" name="password" class="form-control" 
                                placeholder="Masukkan kata sandi" required>
                            <i class="fas fa-lock input-icon"></i>
                            <button type="button" class="toggle-password" id="togglePassword" aria-label="Toggle password visibility">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                        <div class="error-text" id="passError">
                            <i class="fas fa-circle-exclamation"></i> Kata sandi tidak boleh kosong.
                        </div>
                    </div>

                    <button type="submit" class="btn-submit" id="submitBtn">
                        <span class="btn-text">Masuk Sekarang</span>
                        <div class="spinner"></div>
                    </button>

                    <div class="auth-links">
                        <div style="font-size: 14px; color: var(--text-muted);">
                            Belum punya akun? <a href="register.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">Daftar Disini</a>
                        </div>
                        
                        <div class="divider"></div>
                        
                        <a href="../index.php" class="link-item btn-back">
                            <i class="fas fa-arrow-left"></i> Kembali ke Beranda Utama
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="copyright">
            &copy; <span id="year"></span> Puskesmas Nalumsari. Melayani dengan Hati.
        </div>
    </div>

    <script>
        // INISIALISASI & KONSTANTA
        document.getElementById('year').textContent = new Date().getFullYear();
        const form = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        const nikInput = document.getElementById('nik');
        const passwordInput = document.getElementById('password');
        const nikError = document.getElementById('nikError');
        const passError = document.getElementById('passError');
        const togglePassword = document.getElementById('togglePassword');

        // FUNGSI UTILITY

        /** Menampilkan pesan error dan menandai input yang salah */
        function showError(inputElement, errorElement) {
            inputElement.classList.add('error');
            inputElement.style.borderColor = 'var(--error)';
            inputElement.style.backgroundColor = 'var(--error-bg)';
            errorElement.classList.add('show');
        }

        /** Menghapus pesan error dan membersihkan penanda input */
        function clearError(inputElement, errorElement) {
            inputElement.classList.remove('error');
            inputElement.style.borderColor = '';
            inputElement.style.backgroundColor = '';
            errorElement.classList.remove('show');
        }

        // EVENT LISTENERS

        // 1. Toggle Password Visibility
        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            // Ganti ikon
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });

        // 2. Format NIK (Hanya Angka)
        nikInput.addEventListener('input', function() {
            // Hapus semua karakter non-angka
            this.value = this.value.replace(/[^0-9]/g, '');
            // Hapus error saat mulai mengetik
            clearError(nikInput, nikError);
        });

        // Hapus error pada input password saat mengetik
        passwordInput.addEventListener('input', function() {
            clearError(passwordInput, passError);
        });

        // 3. Form Validation & Submit Logic
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            let isValid = true;

            // Reset semua error
            clearError(nikInput, nikError);
            clearError(passwordInput, passError);

            // Validasi NIK (Harus 16 digit)
            if (nikInput.value.length !== 16) {
                showError(nikInput, nikError);
                isValid = false;
            }

            // Validasi Password (Tidak boleh kosong)
            if (passwordInput.value.trim() === '') {
                showError(passwordInput, passError);
                isValid = false;
            }

            if (isValid) {
                // Tampilkan Efek Loading dan kirim formulir setelah delay
                submitBtn.classList.add('loading');
                
                // Simulasikan delay pengiriman (Anda bisa hapus ini di lingkungan produksi)
                setTimeout(() => {
                    form.submit();
                }, 800);
            }
        });
    </script>
</body>
</html>