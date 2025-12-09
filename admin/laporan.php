<?php
include '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/admin_login.php");
    exit;
}

// Default filter: bulan ini
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');
$poli_filter = isset($_GET['poli']) ? $_GET['poli'] : '';

// Ambil data poli untuk filter
$poli_query = "SELECT * FROM poli ORDER BY nama_poli";
$poli_result = mysqli_query($conn, $poli_query);

// Build query untuk laporan
$laporan_query = "SELECT p.nama_poli, 
                         COUNT(d.id) as total_kunjungan,
                         SUM(CASE WHEN d.status = 'selesai' THEN 1 ELSE 0 END) as total_hadir,
                         SUM(CASE WHEN d.status = 'tidak hadir' THEN 1 ELSE 0 END) as total_tidak_hadir
                  FROM pendaftaran d 
                  JOIN poli p ON d.id_poli = p.id 
                  WHERE DATE_FORMAT(d.tanggal_periksa, '%Y-%m') = '$bulan'";

if (!empty($poli_filter)) {
    $laporan_query .= " AND d.id_poli = $poli_filter";
}

$laporan_query .= " GROUP BY p.id, p.nama_poli ORDER BY total_kunjungan DESC";

$laporan_result = mysqli_query($conn, $laporan_query);

// Total statistik
$total_query = "SELECT 
                COUNT(*) as total_kunjungan,
                SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as total_hadir,
                SUM(CASE WHEN status = 'tidak hadir' THEN 1 ELSE 0 END) as total_tidak_hadir
                FROM pendaftaran 
                WHERE DATE_FORMAT(tanggal_periksa, '%Y-%m') = '$bulan'";
$total_result = mysqli_query($conn, $total_query);
$total_stats = mysqli_fetch_assoc($total_result);

// Persentase kehadiran
$persentase_hadir = $total_stats['total_kunjungan'] > 0 ?
    round(($total_stats['total_hadir'] / $total_stats['total_kunjungan']) * 100, 2) : 0;

// Data untuk detail kunjungan
$detail_query = "SELECT p.nama_lengkap, poli.nama_poli, d.tanggal_periksa, d.status, d.tanggal_daftar
                 FROM pendaftaran d 
                 JOIN pasien p ON d.id_pasien = p.id 
                 JOIN poli ON d.id_poli = poli.id 
                 WHERE DATE_FORMAT(d.tanggal_periksa, '%Y-%m') = '$bulan'";
if (!empty($poli_filter)) {
    $detail_query .= " AND d.id_poli = $poli_filter";
}
$detail_query .= " ORDER BY d.tanggal_periksa DESC, poli.nama_poli";
$detail_result = mysqli_query($conn, $detail_query);

// Hitung total detail
$total_detail = mysqli_num_rows($detail_result);

// Helper function untuk alert
function showCustomAlert() {
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i> ' . $_SESSION['success'] . '</div>';
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> ' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']);
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Puskesmas Nalumsari</title>
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            /* Color Palette Modern */
            --primary-color: #16a085; /* Hijau Teal Puskesmas modern */
            --primary-dark: #0e6655;
            --secondary-color: #2c3e50;
            --accent-color: #27ae60;
            
            --bg-body: #f3f4f6;
            --bg-sidebar: #ffffff;
            --bg-card: #ffffff;
            
            --text-main: #1f2937;
            --text-muted: #6b7280;
            
            --border-color: #e5e7eb;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            
            --sidebar-width: 260px;
            --header-height: 70px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
        }

        /* --- Sidebar --- */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--bg-sidebar);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            border-right: 1px solid var(--border-color);
            z-index: 50;
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            height: var(--header-height);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--primary-color);
            text-decoration: none;
        }

        .logo img {
            height: 32px;
            width: auto;
        }

        .nav-menu {
            padding: 1.5rem 1rem;
            list-style: none;
            flex-grow: 1;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.75rem 1rem;
            border-radius: var(--radius-md);
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }

        .nav-link i {
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        .nav-link:hover, .nav-link.active {
            background-color: #f0fdf4; /* Hijau sangat muda */
            color: var(--primary-color);
        }
        
        .sidebar-footer {
            padding: 1rem;
            border-top: 1px solid var(--border-color);
        }

        /* --- Main Content --- */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s ease;
        }

        /* --- Top Header --- */
        .top-header {
            height: var(--header-height);
            background: var(--bg-card);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 40;
        }

        .toggle-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--text-muted);
            cursor: pointer;
        }

        .page-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-main);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-info {
            text-align: right;
        }
        
        .user-name { font-weight: 600; font-size: 0.9rem; }
        .user-role { font-size: 0.8rem; color: var(--text-muted); }

        .btn-logout {
            color: #ef4444;
            text-decoration: none;
            padding: 0.5rem;
            border-radius: var(--radius-md);
            transition: background 0.2s;
        }
        
        .btn-logout:hover { background: #fef2f2; }

        /* --- Content Area --- */
        .content-wrapper {
            padding: 2rem;
        }

        /* --- Cards --- */
        .card-box {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* --- Filter Section --- */
        .filter-section {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            align-items: end;
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-main);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 0.9rem;
            transition: all 0.2s;
            background-color: var(--bg-card);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(22, 160, 133, 0.1);
        }

        .form-actions {
            display: flex;
            gap: 1rem;
        }

        /* --- Buttons --- */
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-md);
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .btn-success {
            background: var(--accent-color);
            color: white;
        }

        .btn-success:hover {
            background: #219653;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        /* --- Stats Grid --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--bg-card);
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
            opacity: 0.8;
        }

        /* --- Tables --- */
        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 800px;
        }

        th {
            background-color: #f9fafb;
            text-align: left;
            padding: 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border-color);
        }

        td {
            padding: 1rem;
            font-size: 0.9rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        tr:last-child td { border-bottom: none; }
        tr:hover td { background-color: #f9fafb; }

        /* --- Status Badges --- */
        .badge {
            padding: 0.35rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge-selesai { background: #dcfce7; color: #15803d; }
        .badge-terdaftar { background: #e0f2fe; color: #0369a1; }
        .badge-tidakhadir { background: #fee2e2; color: #b91c1c; }

        .badge-high { background: #dcfce7; color: #15803d; }
        .badge-medium { background: #fef3c7; color: #b45309; }
        .badge-low { background: #fee2e2; color: #b91c1c; }

        /* --- Alert --- */
        .alert {
            padding: 1rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .alert-error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

        /* --- Utility Classes --- */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .hidden { display: none; }

        .no-data {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--text-muted);
        }

        .no-data i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* --- Responsive Logic --- */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                box-shadow: none;
            }
            
            .sidebar.active {
                transform: translateX(0);
                box-shadow: 10px 0 20px rgba(0,0,0,0.1);
            }

            .main-content {
                margin-left: 0;
            }

            .toggle-btn {
                display: block;
            }
            
            /* Overlay when sidebar open */
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 45;
            }
            .sidebar-overlay.active { display: block; }
        }

        @media (max-width: 768px) {
            .content-wrapper { padding: 1rem; }
            .top-header { padding: 0 1rem; }
            .card-box { padding: 1rem; }
            .filter-grid { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .form-actions { flex-direction: column; }
            .user-info { display: none; }
        }

        @media (max-width: 640px) {
            .stats-grid { grid-template-columns: 1fr; }
            .form-actions .btn { width: 100%; justify-content: center; }
        }
    </style>
</head>

<body>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="#" class="logo">
                <img src="../assets/images/logo.png" alt="Logo">
                <span>Puskesmas Nalumsari</span>
            </a>
        </div>

        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="poli.php" class="nav-link">
                    <i class="fas fa-stethoscope"></i>
                    <span>Data Poli</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="jam_poli.php" class="nav-link">
                    <i class="fas fa-clock"></i>
                    <span>Jam Praktek</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="kuota.php" class="nav-link">
                    <i class="fas fa-ticket-alt"></i>
                    <span>Kuota</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="pasien.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>Data Pasien</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="laporan.php" class="nav-link active">
                    <i class="fas fa-file-alt"></i>
                    <span>Laporan</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="profil.php" class="nav-link">
                    <i class="fas fa-user"></i>
                    <span>Profil</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <a href="../auth/logout.php" class="btn-logout" style="display:flex; align-items:center; gap:10px; width:100%; justify-content:center;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <main class="main-content">
        <header class="top-header">
            <div style="display: flex; align-items: center; gap: 15px;">
                <button class="toggle-btn" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h2 class="page-title">Laporan Kunjungan</h2>
            </div>
            
            <div class="user-profile">
                <div class="user-info">
                    <div class="user-name"><?php echo isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Administrator'; ?></div>
                    <div class="user-role">Admin Staff</div>
                </div>
                <div style="background: #e5e7eb; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #6b7280;">
                    <i class="fas fa-user"></i>
                </div>
            </div>
        </header>

        <div class="content-wrapper">
            
            <?php showCustomAlert(); ?>

            <!-- Filter Section -->
            <div class="filter-section">
                <form method="GET" action="laporan.php">
                    <div class="filter-grid">
                        <div class="form-group">
                            <label class="form-label" for="bulan">
                                <i class="fas fa-calendar"></i>
                                Periode Bulan
                            </label>
                            <input type="month" id="bulan" name="bulan" class="form-control" 
                                   value="<?php echo $bulan; ?>" max="<?php echo date('Y-m'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="poli">
                                <i class="fas fa-stethoscope"></i>
                                Filter Poli
                            </label>
                            <select id="poli" name="poli" class="form-control">
                                <option value="">Semua Poli</option>
                                <?php
                                mysqli_data_seek($poli_result, 0);
                                while ($poli = mysqli_fetch_assoc($poli_result)) {
                                    $selected = ($poli_filter == $poli['id']) ? 'selected' : '';
                                    echo "<option value='{$poli['id']}' $selected>{$poli['nama_poli']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i>
                                Filter Laporan
                            </button>
                            <a href="laporan.php" class="btn btn-secondary">
                                <i class="fas fa-redo"></i>
                                Reset
                            </a>
                            <a href="generate_pdf.php?bulan=<?php echo $bulan; ?>&poli=<?php echo $poli_filter; ?>" 
                               class="btn btn-success" target="_blank">
                                <i class="fas fa-file-pdf"></i>
                                Export PDF
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Stats Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number"><?php echo $total_stats['total_kunjungan']; ?></div>
                    <div class="stat-label">Total Kunjungan</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-number"><?php echo $total_stats['total_hadir']; ?></div>
                    <div class="stat-label">Pasien Hadir</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-user-times"></i>
                    </div>
                    <div class="stat-number"><?php echo $total_stats['total_tidak_hadir']; ?></div>
                    <div class="stat-label">Tidak Hadir</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-number"><?php echo $persentase_hadir; ?>%</div>
                    <div class="stat-label">Tingkat Kehadiran</div>
                </div>
            </div>

            <!-- Laporan per Poli -->
            <div class="card-box">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i>
                        Ringkasan Kunjungan per Poli
                    </h3>
                    <div class="text-muted">
                        Periode: <?php echo date('F Y', strtotime($bulan . '-01')); ?>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Poli</th>
                                <th class="text-center">Total Kunjungan</th>
                                <th class="text-center">Hadir</th>
                                <th class="text-center">Tidak Hadir</th>
                                <th class="text-center">Persentase Hadir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $total_all_kunjungan = 0;
                            $total_all_hadir = 0;
                            $total_all_tidak_hadir = 0;

                            if (mysqli_num_rows($laporan_result) > 0) {
                                mysqli_data_seek($laporan_result, 0);
                                while ($laporan = mysqli_fetch_assoc($laporan_result)) {
                                    $persentase = $laporan['total_kunjungan'] > 0 ?
                                        round(($laporan['total_hadir'] / $laporan['total_kunjungan']) * 100, 2) : 0;

                                    $total_all_kunjungan += $laporan['total_kunjungan'];
                                    $total_all_hadir += $laporan['total_hadir'];
                                    $total_all_tidak_hadir += $laporan['total_tidak_hadir'];

                                    // Tentukan badge untuk persentase
                                    $badge_class = 'badge-high';
                                    if ($persentase < 60) {
                                        $badge_class = 'badge-low';
                                    } elseif ($persentase < 80) {
                                        $badge_class = 'badge-medium';
                                    }

                                    echo "<tr>
                                            <td>$no</td>
                                            <td>
                                                <div style='font-weight:600;'>{$laporan['nama_poli']}</div>
                                            </td>
                                            <td class='text-center'>{$laporan['total_kunjungan']}</td>
                                            <td class='text-center'>{$laporan['total_hadir']}</td>
                                            <td class='text-center'>{$laporan['total_tidak_hadir']}</td>
                                            <td class='text-center'>
                                                <span class='badge $badge_class'>$persentase%</span>
                                            </td>
                                          </tr>";
                                    $no++;
                                }

                                $total_persentase = $total_all_kunjungan > 0 ?
                                    round(($total_all_hadir / $total_all_kunjungan) * 100, 2) : 0;

                                echo "<tr style='background-color: #f9fafb; font-weight: 600;'>
                                        <td colspan='2' class='text-center'>TOTAL</td>
                                        <td class='text-center'>$total_all_kunjungan</td>
                                        <td class='text-center'>$total_all_hadir</td>
                                        <td class='text-center'>$total_all_tidak_hadir</td>
                                        <td class='text-center'>$total_persentase%</td>
                                      </tr>";
                            } else {
                                echo "<tr>
                                        <td colspan='6' class='no-data'>
                                            <i class='fas fa-inbox'></i>
                                            <div>Tidak ada data kunjungan untuk periode ini</div>
                                        </td>
                                      </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Detail Transaksi -->
            <div class="card-box">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list-alt"></i>
                        Detail Transaksi Kunjungan
                    </h3>
                    <div class="text-muted">
                        Total: <?php echo $total_detail; ?> records
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Pasien</th>
                                <th>Poli</th>
                                <th class="text-center">Tanggal Periksa</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Waktu Daftar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no_detail = 1;
                            if (mysqli_num_rows($detail_result) > 0) {
                                mysqli_data_seek($detail_result, 0);
                                while ($detail = mysqli_fetch_assoc($detail_result)) {
                                    $status_class = 'badge-' . str_replace(' ', '', $detail['status']);
                                    
                                    echo "<tr>
                                            <td>$no_detail</td>
                                            <td>
                                                <div style='font-weight:500;'>{$detail['nama_lengkap']}</div>
                                            </td>
                                            <td>{$detail['nama_poli']}</td>
                                            <td class='text-center'>" . date('d/m/Y', strtotime($detail['tanggal_periksa'])) . "</td>
                                            <td class='text-center'>
                                                <span class='badge $status_class'>" . ucfirst($detail['status']) . "</span>
                                            </td>
                                            <td class='text-center'>" . date('H:i', strtotime($detail['tanggal_daftar'])) . "</td>
                                          </tr>";
                                    $no_detail++;
                                }
                            } else {
                                echo "<tr>
                                        <td colspan='6' class='no-data'>
                                            <i class='fas fa-inbox'></i>
                                            <div>Tidak ada data transaksi untuk periode ini</div>
                                        </td>
                                      </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Sidebar Toggle Logic untuk Mobile
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function toggleSidebar() {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
        }

        sidebarToggle.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', toggleSidebar);

        // Auto hide alert
        const alerts = document.querySelectorAll('.alert');
        if (alerts) {
            setTimeout(() => {
                alerts.forEach(alert => {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                });
            }, 4000);
        }

        // Set max date untuk filter bulan
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const currentMonth = year + '-' + month;
            
            document.getElementById('bulan').max = currentMonth;
        });
    </script>
</body>
</html>