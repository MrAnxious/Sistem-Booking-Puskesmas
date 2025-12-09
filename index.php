<?php
/**
 * Halaman Landing Page - Puskesmas Online
 * Sistem Informasi Pendaftaran Online Puskesmas
 */

// Start session untuk pengecekan status login
session_start();

// Jika user sudah login, redirect ke dashboard sesuai role
if (isset($_SESSION['role']) && isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
        exit();
    } elseif ($_SESSION['role'] === 'pasien') {
        header("Location: pasien/dashboard.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Puskesmas Nalumsari - Layanan Kesehatan Digital Terpadu</title>
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary-color: #2e7d32;
            --primary-dark: #1b5e20;
            --primary-light: #4caf50;
            --accent-color: #388e3c;
            --white: #ffffff;
            --gray-light: #f8f9fa;
            --gray-medium: #e9ecef;
            --gray-dark: #6c757d;
            --text-dark: #212529;
            --text-light: #6c757d;
            --border-radius: 8px;
            --box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        body {
            background-color: var(--white);
            color: var(--text-dark);
            line-height: 1.6;
            overflow-x: hidden;
            font-size: 16px;
        }

        /* Header Styles */
        .header {
            background: var(--white);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: var(--transition);
        }

        .header.scrolled {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .logo-icon {
            width: 42px;
            height: 42px;
            background: var(--primary-color);
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: bold;
            font-size: 18px;
        }

        .logo-text {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary-color);
        }

        .nav {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .nav-links {
            display: flex;
            gap: 25px;
            list-style: none;
        }

        .nav-link {
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            position: relative;
            font-size: 15px;
        }

        .nav-link:hover {
            color: var(--primary-color);
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: var(--transition);
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .auth-buttons {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(46, 125, 50, 0.3);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .btn-outline:hover {
            background: var(--primary-color);
            color: var(--white);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--gray-medium);
            color: var(--text-dark);
        }

        .btn-secondary:hover {
            background: var(--gray-dark);
            color: var(--white);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: var(--white);
            padding: 140px 0 80px;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 45%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 C30,100 70,0 100,100 L100,0 Z" fill="rgba(255,255,255,0.1)"/></svg>');
            background-size: cover;
        }

        .hero-content {
            max-width: 600px;
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 1.1rem;
            margin-bottom: 30px;
            opacity: 0.9;
            line-height: 1.6;
        }

        .hero-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 40px;
        }

        .hero-stats {
            display: flex;
            gap: 30px;
            margin-top: 40px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.85rem;
            opacity: 0.8;
        }

        /* Features Section */
        .features {
            padding: 80px 0;
            background: var(--gray-light);
        }

        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .section-subtitle {
            font-size: 1.1rem;
            color: var(--text-light);
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 25px;
        }

        .feature-card {
            background: var(--white);
            padding: 25px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            border-left: 4px solid var(--primary-color);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
        }

        .feature-icon {
            width: 55px;
            height: 55px;
            background: var(--primary-light);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            color: var(--white);
            font-size: 22px;
        }

        .feature-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 12px;
            color: var(--text-dark);
        }

        .feature-description {
            color: var(--text-light);
            line-height: 1.6;
            flex-grow: 1;
        }

        /* Services Section */
        .services {
            padding: 80px 0;
            background: var(--white);
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }

        .service-card {
            background: var(--white);
            border: 1px solid var(--gray-medium);
            border-radius: var(--border-radius);
            padding: 25px;
            text-align: center;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .service-card:hover {
            border-color: var(--primary-color);
            box-shadow: 0 5px 15px rgba(46, 125, 50, 0.1);
            transform: translateY(-3px);
        }

        .service-icon {
            font-size: 2.2rem;
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        .service-title {
            font-size: 1.15rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text-dark);
        }

        .service-description {
            color: var(--text-light);
            font-size: 0.9rem;
            line-height: 1.5;
        }

        /* How It Works Section */
        .how-it-works {
            padding: 80px 0;
            background: var(--gray-light);
        }

        .steps {
            display: flex;
            justify-content: space-between;
            max-width: 900px;
            margin: 0 auto;
            position: relative;
        }

        .steps::before {
            content: '';
            position: absolute;
            top: 40px;
            left: 50px;
            right: 50px;
            height: 2px;
            background: var(--gray-medium);
            z-index: 1;
        }

        .step {
            text-align: center;
            position: relative;
            z-index: 2;
            flex: 1;
            padding: 0 15px;
        }

        .step-number {
            width: 70px;
            height: 70px;
            background: var(--primary-color);
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            font-weight: 700;
            margin: 0 auto 20px;
            border: 4px solid var(--gray-light);
        }

        .step-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text-dark);
        }

        .step-description {
            color: var(--text-light);
            font-size: 0.9rem;
            line-height: 1.5;
        }

        /* CTA Section */
        .cta {
            padding: 80px 0;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: var(--white);
            text-align: center;
        }

        .cta-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .cta-subtitle {
            font-size: 1.1rem;
            margin-bottom: 30px;
            opacity: 0.9;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }

        .cta-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* Footer */
        .footer {
            background: var(--text-dark);
            color: var(--white);
            padding: 50px 0 20px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-section h3 {
            font-size: 1.2rem;
            margin-bottom: 20px;
            color: var(--white);
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: var(--gray-medium);
            text-decoration: none;
            transition: var(--transition);
            font-size: 14px;
        }

        .footer-links a:hover {
            color: var(--white);
        }

        .footer-bottom {
            border-top: 1px solid var(--gray-dark);
            padding-top: 20px;
            text-align: center;
            color: var(--gray-medium);
            font-size: 0.85rem;
        }

        /* Hamburger Menu for Mobile */
        .menu-toggle {
            display: none;
            flex-direction: column;
            cursor: pointer;
            width: 24px;
            height: 18px;
            position: relative;
        }

        .menu-toggle span {
            display: block;
            height: 2px;
            width: 100%;
            background: var(--text-dark);
            border-radius: 2px;
            transition: var(--transition);
            transform-origin: center;
        }

        .menu-toggle span:nth-child(1) {
            transform: translateY(0) rotate(0);
        }

        .menu-toggle span:nth-child(2) {
            transform: translateY(6px) rotate(0);
            opacity: 1;
        }

        .menu-toggle span:nth-child(3) {
            transform: translateY(12px) rotate(0);
        }

        .menu-toggle.active span:nth-child(1) {
            transform: translateY(6px) rotate(45deg);
        }

        .menu-toggle.active span:nth-child(2) {
            opacity: 0;
        }

        .menu-toggle.active span:nth-child(3) {
            transform: translateY(6px) rotate(-45deg);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .hero-title {
                font-size: 2.4rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
        }

        @media (max-width: 768px) {
            .header-content {
                padding: 12px 0;
            }

            .menu-toggle {
                display: flex;
            }

            .nav {
                position: fixed;
                top: 70px;
                left: 0;
                width: 100%;
                background: var(--white);
                flex-direction: column;
                padding: 20px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
                transform: translateY(-100%);
                opacity: 0;
                visibility: hidden;
                transition: var(--transition);
                z-index: 999;
            }

            .nav.active {
                transform: translateY(0);
                opacity: 1;
                visibility: visible;
            }

            .nav-links {
                flex-direction: column;
                gap: 15px;
                width: 100%;
            }

            .nav-link {
                display: block;
                padding: 10px 0;
                border-bottom: 1px solid var(--gray-medium);
            }

            .nav-link:last-child {
                border-bottom: none;
            }

            .auth-buttons {
                flex-direction: column;
                width: 100%;
                margin-top: 15px;
            }

            .auth-buttons .btn {
                width: 100%;
                justify-content: center;
            }

            .hero {
                padding: 120px 0 60px;
                text-align: center;
            }

            .hero-title {
                font-size: 2rem;
            }

            .hero-subtitle {
                font-size: 1rem;
            }

            .hero-buttons {
                justify-content: center;
                flex-direction: column;
            }

            .hero-buttons .btn {
                width: 100%;
                justify-content: center;
            }

            .hero-stats {
                justify-content: center;
                flex-wrap: wrap;
                gap: 20px;
            }

            .steps {
                flex-direction: column;
                gap: 40px;
            }

            .steps::before {
                display: none;
            }

            .section-title {
                font-size: 1.8rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .services-grid {
                grid-template-columns: 1fr;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }

            .cta-buttons .btn {
                width: 100%;
                max-width: 300px;
            }

            .footer-content {
                grid-template-columns: 1fr;
                gap: 30px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 15px;
            }

            .hero-title {
                font-size: 1.8rem;
            }

            .section-title {
                font-size: 1.6rem;
            }

            .feature-card, .service-card {
                padding: 20px;
            }

            .hero-stats {
                gap: 15px;
            }

            .stat-number {
                font-size: 1.5rem;
            }

            .cta-title {
                font-size: 1.7rem;
            }
        }

        /* Animation Classes */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .slide-in-left {
            opacity: 0;
            transform: translateX(-30px);
            transition: all 0.6s ease;
        }

        .slide-in-left.visible {
            opacity: 1;
            transform: translateX(0);
        }

        .slide-in-right {
            opacity: 0;
            transform: translateX(30px);
            transition: all 0.6s ease;
        }

        .slide-in-right.visible {
            opacity: 1;
            transform: translateX(0);
        }

        /* Utility Classes */
        .text-center {
            text-align: center;
        }

        .mb-20 {
            margin-bottom: 20px;
        }

        .mt-30 {
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header" id="header">
        <div class="container">
            <div class="header-content">
                <a href="#" class="logo">
                    <div class="logo-icon">🏥</div>
                    <div class="logo-text">Puskesmas Nalumsari</div>
                </a>
                
                <div class="menu-toggle" id="menuToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                
                <nav class="nav" id="nav">
                    <ul class="nav-links">
                        <li><a href="#services" class="nav-link">Layanan</a></li>
                        <li><a href="#how-it-works" class="nav-link">Cara Kerja</a></li>
                        <li><a href="#contact" class="nav-link">Kontak</a></li>
                    </ul>
                    
                    <div class="auth-buttons">
                        <a href="auth/login.php" class="btn btn-primary">Login Pasien</a>
                        <a href="auth/admin_login.php" class="btn btn-outline">Login Admin</a>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="hero">
        <div class="container">
            <div class="hero-content slide-in-left">
                <h1 class="hero-title">
                    Layanan Kesehatan Digital
                    <span style="display: block; color: #c8e6c9; margin-top: 10px;">Yang Terjangkau dan Terpercaya</span>
                </h1>
                <p class="hero-subtitle">
                    Daftar dan konsultasi dengan tenaga kesehatan profesional secara online. 
                    Nikmati kemudahan akses layanan kesehatan tanpa harus antri lama. 
                    Sistem yang aman, cepat, dan terintegrasi.
                </p>
                <div class="hero-buttons">
                    <a href="auth/register.php" class="btn btn-primary">
                        📝 Daftar Sekarang
                    </a>
                    <a href="auth/login.php" class="btn btn-secondary">
                        🔐 Login Pasien
                    </a>
                    <a href="auth/admin_login.php" class="btn btn-outline" style="color: white; border-color: white;">
                        👨‍💼 Login Admin
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="stat-number">5.000+</div>
                        <div class="stat-label">Pasien Terdaftar</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">50+</div>
                        <div class="stat-label">Tenaga Medis</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Layanan Online</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services" id="services">
        <div class="container">
            <div class="section-header fade-in">
                <h2 class="section-title">Layanan Kesehatan</h2>
                <p class="section-subtitle">
                    Berbagai layanan kesehatan profesional yang tersedia untuk masyarakat
                </p>
            </div>
            
            <div class="services-grid">
                <div class="service-card slide-in-left">
                    <div class="service-icon">👨‍⚕️</div>
                    <h3 class="service-title">Poli Umum</h3>
                    <p class="service-description">
                        Layanan konsultasi kesehatan umum untuk semua usia dengan tenaga medis berpengalaman
                    </p>
                </div>
                
                <div class="service-card fade-in">
                    <div class="service-icon">🦷</div>
                    <h3 class="service-title">Poli Gigi</h3>
                    <p class="service-description">
                        Perawatan dan konsultasi kesehatan gigi dan mulut oleh dokter gigi profesional
                    </p>
                </div>
                
                <div class="service-card slide-in-right">
                    <div class="service-icon">👶</div>
                    <h3 class="service-title">Poli Anak</h3>
                    <p class="service-description">
                        Layanan kesehatan khusus untuk bayi, balita, dan anak-anak dengan pendekatan yang ramah
                    </p>
                </div>
                
                <div class="service-card slide-in-left">
                    <div class="service-icon">🤰</div>
                    <h3 class="service-title">Poli Kandungan</h3>
                    <p class="service-description">
                        Layanan kesehatan ibu dan anak, pemeriksaan kehamilan, dan konsultasi kandungan
                    </p>
                </div>
                
                <div class="service-card fade-in">
                    <div class="service-icon">👁️</div>
                    <h3 class="service-title">Poli Mata</h3>
                    <p class="service-description">
                        Pemeriksaan dan konsultasi kesehatan mata serta penanganan gangguan penglihatan
                    </p>
                </div>
                
                <div class="service-card slide-in-right">
                    <div class="service-icon">💊</div>
                    <h3 class="service-title">Apotek</h3>
                    <p class="service-description">
                        Layanan pembelian obat dengan resep dokter dan konsultasi penggunaan obat
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works" id="how-it-works">
        <div class="container">
            <div class="section-header fade-in">
                <h2 class="section-title">Cara Menggunakan Layanan</h2>
                <p class="section-subtitle">
                    Hanya dengan 4 langkah mudah, Anda bisa mendapatkan layanan kesehatan yang optimal
                </p>
            </div>
            
            <div class="steps">
                <div class="step fade-in">
                    <div class="step-number">1</div>
                    <h3 class="step-title">Daftar Akun</h3>
                    <p class="step-description">
                        Buat akun pasien dengan mengisi data diri yang valid dan lengkap
                    </p>
                </div>
                
                <div class="step fade-in">
                    <div class="step-number">2</div>
                    <h3 class="step-title">Login Sistem</h3>
                    <p class="step-description">
                        Masuk ke sistem menggunakan NIK dan password yang telah didaftarkan
                    </p>
                </div>
                
                <div class="step fade-in">
                    <div class="step-number">3</div>
                    <h3 class="step-title">Pilih Layanan</h3>
                    <p class="step-description">
                        Pilih poli dan tanggal kunjungan yang sesuai dengan kebutuhan Anda
                    </p>
                </div>
                
                <div class="step fade-in">
                    <div class="step-number">4</div>
                    <h3 class="step-title">Konfirmasi</h3>
                    <p class="step-description">
                        Datang ke puskesmas sesuai jadwal dengan menunjukkan bukti pendaftaran
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta" id="cta">
        <div class="container">
            <h2 class="cta-title fade-in">Siap Menggunakan Layanan Kami?</h2>
            <p class="cta-subtitle fade-in">
                Bergabunglah dengan ribuan pasien yang telah merasakan kemudahan 
                layanan kesehatan digital dari Puskesmas Nalumsari
            </p>
            <div class="cta-buttons">
                <a href="auth/register.php" class="btn btn-primary" style="background: white; color: var(--primary-color);">
                    📝 Daftar Sekarang
                </a>
                <a href="auth/login.php" class="btn btn-outline" style="color: white; border-color: white;">
                    🔐 Login ke Akun
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer" id="contact">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Puskesmas Nalumsari</h3>
                    <p style="color: var(--gray-medium); margin-bottom: 20px; line-height: 1.6;">
                        Layanan kesehatan digital terpadu yang memberikan kemudahan 
                        akses bagi masyarakat untuk mendapatkan pelayanan kesehatan 
                        yang berkualitas.
                    </p>
                    <div class="auth-buttons">
                        <a href="auth/login.php" class="btn btn-primary" style="padding: 8px 16px; font-size: 12px;">
                            Login Pasien
                        </a>
                        <a href="auth/admin_login.php" class="btn btn-outline" style="padding: 8px 16px; font-size: 12px; color: white; border-color: white;">
                            Login Admin
                        </a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3>Link Cepat</h3>
                    <ul class="footer-links">
                        <li><a href="#services">Jenis Layanan</a></li>
                        <li><a href="#how-it-works">Cara Penggunaan</a></li>
                        <li><a href="auth/register.php">Pendaftaran Pasien</a></li>
                        <li><a href="auth/login.php">Login Pasien</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Kontak Kami</h3>
                    <ul class="footer-links">
                        <li>📞  (0291) 4256518</li>
                        <li>📧 info@puskesmasnalumsari.id</li>
                        <li>📍 Jl. Raya Jepara - Kudus, Dusun 1, Pringtulis, Kec. Nalumsari, Kabupaten Jepara, Jawa Tengah 59466</li>
                        <li>🕒 Senin - Kamis : 07.00 - 12.00</li>
                        <li>🕒 Jumat: 07.00 - 10.45</li>
                        <li>🕒 Sabtu: 07.00 - 11.00</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                &copy; <span id="tahun-copyright"></span> Puskesmas Nalumsari | Sistem Pendaftaran Online
            </div>
        </div>
    </footer>

    <script>
        document.getElementById("tahun-copyright").innerHTML = new Date().getFullYear();
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const menuToggle = document.getElementById('menuToggle');
            const nav = document.getElementById('nav');
            
            menuToggle.addEventListener('click', function() {
                this.classList.toggle('active');
                nav.classList.toggle('active');
            });
            
            // Close mobile menu when clicking on a link
            document.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', function() {
                    menuToggle.classList.remove('active');
                    nav.classList.remove('active');
                });
            });

            // Header scroll effect
            const header = document.getElementById('header');
            window.addEventListener('scroll', function() {
                if (window.scrollY > 100) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });

            // Smooth scrolling untuk anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Intersection Observer untuk animasi scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, observerOptions);

            // Observe semua elemen dengan class animasi
            document.querySelectorAll('.fade-in, .slide-in-left, .slide-in-right').forEach(el => {
                observer.observe(el);
            });

            // Stat counter animation
            const statNumbers = document.querySelectorAll('.stat-number');
            statNumbers.forEach(stat => {
                const target = parseInt(stat.textContent);
                let current = 0;
                const increment = target / 50;
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        stat.textContent = target + '+';
                        clearInterval(timer);
                    } else {
                        stat.textContent = Math.floor(current) + '+';
                    }
                }, 50);
            });
        });
    </script>
</body>
</html>