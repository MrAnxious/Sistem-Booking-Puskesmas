<?php
// Hapus session_start() karena sudah ada di database.php
include '../config/database.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Puskesmas Nalumsari</title>
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1b5e20;
            --primary-dark: #0d3a0d;
            --secondary-color: #2e7d32;
            --accent-color: #4caf50;
            --text-primary: #1a1a1a;
            --text-secondary: #555;
            --text-light: #757575;
            --bg-white: #ffffff;
            --bg-light: #f8f9fa;
            --border-color: #e0e0e0;
            --shadow-light: 0 4px 20px rgba(0, 0, 0, 0.08);
            --shadow-medium: 0 10px 40px rgba(0, 0, 0, 0.12);
            --shadow-heavy: 0 20px 60px rgba(0, 0, 0, 0.15);
            --border-radius: 16px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 50%, #0d3a0d 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background:
                radial-gradient(circle at 20% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
            z-index: -1;
        }

        .login-container {
            background: var(--bg-white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-heavy);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
            position: relative;
            animation: slideUp 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 35px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
            }

            50% {
                transform: translateY(-20px) rotate(180deg);
            }
        }

        .logo-container {
            display: flex;
            justify-content: center;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }

        .logo {
            width: 70px;
            height: 70px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-medium);
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: var(--transition);
        }

        .logo:hover {
            transform: scale(1.05);
            background: rgba(255, 255, 255, 0.3);
        }

        .logo i {
            font-size: 32px;
            color: white;
        }

        .login-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .login-header p {
            opacity: 0.9;
            font-size: 16px;
            position: relative;
            z-index: 1;
        }

        .login-body {
            padding: 40px 35px 35px;
        }

        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.4s ease-out;
        }

        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }

        .alert-success {
            background: #e8f5e9;
            color: var(--primary-color);
            border: 1px solid #c8e6c9;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
            color: var(--text-primary);
            font-weight: 600;
            font-size: 15px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .form-control {
            width: 100%;
            padding: 15px 18px 15px 48px;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            font-size: 16px;
            background: #fafbfc;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(27, 94, 32, 0.1);
            background: var(--bg-white);
        }

        .form-group .input-icon {
            position: absolute;
            left: 18px;
            color: var(--text-light);
            font-size: 18px;
            z-index: 2;
            transition: var(--transition);
        }

        .form-control:focus~.input-icon {
            color: var(--primary-color);
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            cursor: pointer;
            color: var(--text-light);
            font-size: 18px;
            transition: var(--transition);
            z-index: 3;
            background: none;
            border: none;
            padding: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .password-toggle:hover {
            color: var(--primary-color);
            transform: scale(1.1);
        }

        .btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.6s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-color) 100%);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .btn.loading .btn-text {
            display: none;
        }

        .btn.loading::after {
            content: '';
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .login-links {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }

        .login-links a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 0;
            border-radius: 8px;
            margin: 0 10px;
        }

        .login-links a:hover {
            color: var(--primary-dark);
            background: rgba(27, 94, 32, 0.05);
        }

        .security-badge {
            text-align: center;
            margin-top: 20px;
            padding: 14px;
            background: linear-gradient(135deg, rgba(27, 94, 32, 0.03) 0%, rgba(46, 125, 50, 0.03) 100%);
            border-radius: 10px;
            border: 1px solid rgba(27, 94, 32, 0.1);
        }

        .security-badge i {
            color: var(--accent-color);
            margin-right: 8px;
        }

        .admin-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.1);
            padding: 6px 12px;
            border-radius: 20px;
            margin-top: 10px;
            font-size: 12px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        @media (max-width: 480px) {
            body {
                padding: 16px;
            }

            .login-container {
                margin: 0;
                border-radius: 14px;
            }

            .login-header {
                padding: 25px 20px;
            }

            .login-header h1 {
                font-size: 24px;
            }

            .login-body {
                padding: 30px 25px 25px;
            }

            .logo {
                width: 60px;
                height: 60px;
            }

            .logo i {
                font-size: 28px;
            }

            .login-links a {
                display: block;
                margin: 5px 0;
            }
        }

        @media (max-width: 360px) {
            .login-body {
                padding: 25px 20px 20px;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo-container">
                <div class="logo">
                    <i class="fas fa-user-shield"></i>
                </div>
            </div>
            <h1>Puskesmas Nalumsari</h1>
            <p>Login sebagai Administrator</p>
            <div class="admin-badge">
                <i class="fas fa-shield-alt"></i>
                <span>Akses Terbatas - Staff Terotorisasi</span>
            </div>
        </div>

        <div class="login-body">
            <?php
            // Menggunakan fungsi showAlert() dari database.php
            showAlert();
            ?>

            <form method="POST" action="admin_login_process.php" id="adminLoginForm">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user-cog"></i>
                        Username
                    </label>
                    <div class="input-wrapper">
                        <input type="text" id="username" name="username" class="form-control" required
                            placeholder="Masukkan username admin">
                        <i class="fas fa-user-cog input-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" class="form-control" required
                            placeholder="Masukkan password admin">
                        <i class="fas fa-lock input-icon"></i>
                        <button type="button" class="password-toggle" id="togglePassword">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn" id="submitBtn">
                    <i class="fas fa-sign-in-alt"></i>
                    <span class="btn-text">Login sebagai Admin</span>
                </button>
            </form>

            <div class="login-links">
                <a href="../index.php">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke Halaman Utama
                </a>
            </div>

            <div class="security-badge">
                &copy; <span id="tahun-copyright"></span> Puskesmas Nalumsari
            </div>
        </div>
    </div>

    <script>
        document.getElementById("tahun-copyright").innerHTML = new Date().getFullYear();
        document.addEventListener('DOMContentLoaded', function () {
            // Enhanced input effects
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function () {
                    this.parentElement.style.transform = 'translateY(-2px)';
                });

                input.addEventListener('blur', function () {
                    this.parentElement.style.transform = 'translateY(0)';
                });
            });

            // Advanced password toggle
            const togglePassword = document.querySelector('#togglePassword');
            const passwordInput = document.querySelector('#password');

            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function () {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);

                    this.querySelector('i').className = type === 'password'
                        ? 'far fa-eye'
                        : 'far fa-eye-slash';
                });
            }

            // Form validation
            const form = document.querySelector('#adminLoginForm');
            const submitBtn = document.querySelector('#submitBtn');

            if (form) {
                form.addEventListener('submit', function (e) {
                    // Validasi sederhana
                    const username = document.getElementById('username').value.trim();
                    const password = document.getElementById('password').value.trim();

                    if (!username || !password) {
                        e.preventDefault();
                        return false;
                    }

                    // Tambah efek loading
                    if (submitBtn) {
                        submitBtn.classList.add('loading');
                    }
                });
            }
        });
    </script>
</body>

</html>