<?php
// Pastikan file koneksi database di-include
include '../config/database.php'; 

// Fungsi untuk membersihkan dan memvalidasi input
function validate_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pastikan koneksi database tidak null
    if (isset($conn) && $conn) {
        $nik = validate_input($_POST['nik']);
        $email = validate_input($_POST['email']);
        
        // --- Validasi Sisi Server ---
        if (empty($nik) || empty($email)) {
             $error_message = 'NIK dan Email harus diisi.';
        } elseif (!preg_match("/^\d{16}$/", $nik)) {
            $error_message = 'NIK harus terdiri dari 16 digit angka.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Format Email tidak valid.';
        } else {
            // 1. Verifikasi NIK dan Email di tabel pasien
            // Menggunakan Prepared Statement untuk keamanan SQL Injection
            $query = "SELECT id_pasien FROM pasien WHERE nik = ? AND email = ?";
            $stmt = $conn->prepare($query);
            
            if ($stmt === false) {
                 $error_message = 'Gagal menyiapkan query verifikasi: ' . $conn->error;
            } else {
                $stmt->bind_param("ss", $nik, $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    // NIK dan Email cocok, lanjutkan reset password
                    
                    // Kata sandi default
                    $default_password_plain = '12345678';
                    // Gunakan MD5 sesuai permintaan, meskipun password_hash() lebih disarankan
                    $default_password_hashed = md5($default_password_plain); 
                    
                    // 2. Reset password ke default
                    $update_query = "UPDATE pasien SET password = ? WHERE nik = ? AND email = ?";
                    $update_stmt = $conn->prepare($update_query);
                    
                    if ($update_stmt === false) {
                        $error_message = 'Gagal menyiapkan query update: ' . $conn->error;
                    } else {
                        $update_stmt->bind_param("sss", $default_password_hashed, $nik, $email);
                        
                        if ($update_stmt->execute()) {
                            // Pesan sukses yang akan ditampilkan di div alert
                            $success_message = 'Password berhasil direset. Password baru Anda: <strong>' . htmlspecialchars($default_password_plain) . '</strong>. Harap segera ganti password Anda setelah login.';
                        } else {
                            $error_message = 'Gagal mereset password. Silakan coba lagi.';
                        }
                        $update_stmt->close();
                    }
                } else {
                    // NIK dan Email tidak cocok
                    $error_message = 'NIK dan Email tidak cocok. Silakan periksa kembali data Anda.';
                }
                $stmt->close();
            }
        }
    } else {
        $error_message = 'Koneksi database gagal. Silakan hubungi administrator.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Puskesmas Online</title>
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- Variabel Warna --- */
        :root {
            --primary-color: #2e7d32;
            --primary-dark: #1b5e20;
            --secondary-color: #4caf50;
            --accent-color: #81c784;
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

        /* --- Global Reset & Body --- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            /* Latar belakang gradien hijau gelap */
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 50%, #0d3a0d 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* Efek latar belakang gelembung */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(255,255,255,0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255,255,255,0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(255,255,255,0.05) 0%, transparent 50%);
            z-index: -1;
        }

        /* --- Container Utama --- */
        .container {
            background: var(--bg-white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-heavy);
            width: 100%;
            max-width: 460px;
            overflow: hidden;
            position: relative;
            animation: slideUp 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            backdrop-filter: blur(10px);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(50px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* --- Header (Bagian Atas Hijau) --- */
        .header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        /* Ikon Logo Kunci */
        .logo-container {
            display: flex; justify-content: center; margin-bottom: 20px; position: relative; z-index: 1;
        }

        .logo {
            width: 90px; height: 90px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(10px);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            box-shadow: var(--shadow-medium); border: 3px solid rgba(255, 255, 255, 0.3); transition: var(--transition);
        }

        .logo:hover { transform: scale(1.05); background: rgba(255, 255, 255, 0.3); }

        .logo i { font-size: 45px; color: white; }

        .header h1 { font-size: 32px; font-weight: 700; margin-bottom: 8px; position: relative; z-index: 1; letter-spacing: -0.5px; }

        .header p { opacity: 0.95; font-size: 16px; font-weight: 400; position: relative; z-index: 1; }

        /* --- Bagian Utama (Form) --- */
        .body {
            padding: 50px 40px 40px;
        }

        /* --- Alerts (Pesan Sukses/Error) --- */
        .alert {
            padding: 16px 20px; border-radius: 12px; margin-bottom: 28px; font-weight: 500;
            display: flex; align-items: center; gap: 12px; animation: slideIn 0.4s ease-out;
        }

        .alert-error {
            background: linear-gradient(90deg, #ffebee 0%, #ffcdd2 100%);
            color: #c62828; border: 1px solid #ef9a9a;
        }

        .alert-success {
            background: linear-gradient(90deg, #e8f5e9 0%, #c8e6c9 100%);
            color: var(--primary-color); border: 1px solid #a5d6a7;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        /* --- Form Styling --- */
        .form-group { margin-bottom: 28px; position: relative; }

        .form-group label {
            display: flex; align-items: center; gap: 8px; margin-bottom: 10px;
            color: var(--text-primary); font-weight: 600; font-size: 15px; letter-spacing: -0.2px;
        }

        .input-wrapper { position: relative; display: flex; align-items: center; }

        .form-control {
            width: 100%; padding: 16px 20px 16px 52px; border: 2px solid var(--border-color);
            border-radius: 12px; font-size: 16px; font-weight: 500; background: #fafbfc; transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary-color); outline: none;
            box-shadow: 0 0 0 4px rgba(46, 125, 50, 0.08);
            background: var(--bg-white); transform: translateY(-2px);
        }
        
        .form-control:focus ~ .input-icon { color: var(--primary-color); }

        .form-group .input-icon {
            position: absolute; left: 20px; color: var(--text-light); font-size: 18px; z-index: 2; transition: var(--transition);
        }
        
        .error-message {
            color: #d32f2f; font-size: 14px; margin-top: 8px; display: flex; align-items: center;
            gap: 6px; opacity: 0; transition: var(--transition);
        }

        .error-message.show { opacity: 1; }

        /* --- Button Styling --- */
        .btn {
            width: 100%; padding: 18px; 
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white; border: none; border-radius: 12px; font-size: 14px; font-weight: 600;
            cursor: pointer; transition: var(--transition); display: flex; align-items: center;
            justify-content: center; gap: 10px; position: relative; overflow: hidden;
            letter-spacing: 0.5px; text-transform: uppercase;
        }

        /* Efek shimmer pada tombol */
        .btn::before {
            content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s;
        }

        .btn:hover::before { left: 100%; }
        .btn:hover { transform: translateY(-3px); box-shadow: var(--shadow-heavy); background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-color) 100%); }
        .btn:active { transform: translateY(-1px); }

        /* Loading State */
        .btn.loading { pointer-events: none; opacity: 0.8; }
        .btn.loading i { display: none; }
        
        .btn.loading::after {
            content: ''; width: 20px; height: 20px; border: 2px solid transparent;
            border-top: 2px solid white; border-radius: 50%; animation: spin 1s linear infinite; margin-left: 0;
        }

        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        /* --- Link Kembali --- */
        .links {
            text-align: center; margin-top: 32px; padding-top: 28px; border-top: 1px solid var(--border-color);
        }

        .links a {
            color: var(--primary-color); text-decoration: none; font-weight: 500; font-size: 15px;
            transition: var(--transition); display: inline-flex; align-items: center;
            gap: 8px; padding: 12px 0; border-radius: 8px;
        }

        .links a:hover { color: var(--primary-dark); background: rgba(46, 125, 50, 0.05); transform: translateX(4px); }

        /* --- Security Badge --- */
        .security-badge {
            text-align: center; margin-top: 24px; padding: 16px;
            background: linear-gradient(135deg, rgba(46,125,50,0.03) 0%, rgba(76,175,80,0.03) 100%);
            border-radius: 12px; border: 1px solid rgba(46,125,50,0.1);
        }

        .security-badge i { color: var(--accent-color); margin-right: 8px; }

        /* --- Media Queries --- */
        @media (max-width: 480px) {
            body { padding: 16px; }
            .container { margin: 0; border-radius: 20px; }
            .header { padding: 32px 24px; }
            .header h1 { font-size: 28px; }
            .body { padding: 40px 28px 32px; }
            .logo { width: 80px; height: 80px; }
            .logo i { font-size: 40px; }
        }

        @media (max-width: 360px) {
            .body { padding: 32px 20px 24px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo-container">
                <div class="logo">
                    <i class="fas fa-key"></i> 
                </div>
            </div>
            <h1>Puskesmas Nalumsari</h1>
            <p>Reset Password Pasien</p>
        </div>
        
        <div class="body">
            <?php
            // Menampilkan pesan sukses atau error dari PHP
            if (!empty($success_message)) {
                echo '<div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        ' . $success_message . '
                      </div>';
            } elseif (!empty($error_message)) {
                echo '<div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        ' . $error_message . '
                      </div>';
            }
            ?>
            
            <form method="POST" action="" id="resetForm">
                <div class="form-group">
                    <label for="nik">
                        <i class="fas fa-id-card"></i>
                        Nomor Induk Kependudukan (NIK)
                    </label>
                    <div class="input-wrapper">
                        <input type="text" id="nik" name="nik" class="form-control" required 
                                placeholder="Masukkan 16 digit NIK Anda" maxlength="16" pattern="\d{16}"
                                value="<?php echo isset($_POST['nik']) ? htmlspecialchars($_POST['nik']) : ''; ?>">
                        <i class="fas fa-id-card input-icon"></i>
                    </div>
                    <div class="error-message" id="nikError">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>NIK harus terdiri dari 16 digit angka</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Alamat Email
                    </label>
                    <div class="input-wrapper">
                        <input type="email" id="email" name="email" class="form-control" required 
                                placeholder="Masukkan email Anda"
                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        <i class="fas fa-envelope input-icon"></i>
                    </div>
                    <div class="error-message" id="emailError">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>Masukkan alamat email yang valid</span>
                    </div>
                </div>
                
                <button type="submit" class="btn" id="submitBtn">
                    <i class="fas fa-redo"></i>
                    Reset Password
                </button>
            </form>
            
            <div class="links">
                <a href="login.php">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke Halaman Login
                </a>
            </div>
            
            <div class="security-badge">
                <i class="fas fa-shield-alt"></i>
                <small>Koneksi Terenkripsi & Data Terlindungi</small>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.form-control');
            const form = document.querySelector('#resetForm');
            const submitBtn = document.querySelector('#submitBtn');
            
            // Re-enable submit button if there was a server-side error on load
            if (document.querySelector('.alert-error')) {
                submitBtn.classList.remove('loading');
                submitBtn.innerHTML = '<i class="fas fa-redo"></i> Reset Password';
            }

            // --- Efek dan Validasi Input ---
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.style.transform = 'translateY(-2px)';
                });
                
                input.addEventListener('blur', function() {
                    this.style.transform = 'translateY(0)';
                    validateField(this);
                });
            });
            
            const nikInput = document.querySelector('#nik');
            const emailInput = document.querySelector('#email');

            // NIK: Hanya angka, maksimal 16 digit
            nikInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/\D/g, '').slice(0, 16);
                validateField(this); 
            });

            // Email: Validasi saat input berubah
            emailInput.addEventListener('input', function(e) {
                validateField(this); 
            });

            // --- Fungsi Validasi Umum ---
            function validateField(field) {
                const value = field.value.trim();
                let isValid = true;
                let errorMessage = '';
                
                if (field.id === 'nik') {
                    if (value.length !== 16 || !/^\d+$/.test(value)) {
                        isValid = false;
                        errorMessage = 'NIK harus terdiri dari 16 digit angka';
                    }
                } else if (field.id === 'email') {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(value)) {
                        isValid = false;
                        errorMessage = 'Masukkan alamat email yang valid';
                    }
                } else if (value === '') {
                     isValid = false;
                     errorMessage = 'Kolom ini tidak boleh kosong';
                }
                
                // Show/hide error message
                const errorElement = document.getElementById(field.id + 'Error');
                if (errorElement) {
                    const spanElement = errorElement.querySelector('span');
                    if (spanElement) {
                        spanElement.textContent = errorMessage;
                    }
                    
                    if (!isValid) {
                        errorElement.classList.add('show');
                        field.style.borderColor = '#d32f2f';
                    } else {
                        errorElement.classList.remove('show');
                        field.style.borderColor = ''; // Reset ke default
                    }
                }
                
                return isValid;
            }

            // --- Submit Form dengan Loading State ---
            form.addEventListener('submit', function(e) {
                
                const nikValid = validateField(nikInput);
                const emailValid = validateField(emailInput);
                
                if (!nikValid || !emailValid) {
                    e.preventDefault();
                    return;
                }

                // Jika validasi sukses, tampilkan loading state dan submit
                if (nikValid && emailValid) {
                    e.preventDefault(); 
                    submitBtn.classList.add('loading');
                    submitBtn.innerHTML = 'Memproses...';
                    
                    // Lakukan submit form
                    form.submit();
                }
            });
        });
    </script>
</body>
</html>