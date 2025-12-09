<?php
include '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Redirect manual jika fungsi helper tidak ada
    header("Location: ../auth/admin_login.php");
    exit;
}

$today = date('Y-m-d');
$current_month = date('Y-m');

// Statistik untuk dashboard
$stats = [];

// Total pendaftaran hari ini
$query_today = "SELECT COUNT(*) as total FROM pendaftaran WHERE tanggal_periksa = '$today'";
$result_today = mysqli_query($conn, $query_today);
$stats['today'] = mysqli_fetch_assoc($result_today)['total'];

// Total pasien hadir hari ini
$query_hadir = "SELECT COUNT(*) as total FROM pendaftaran WHERE tanggal_periksa = '$today' AND status = 'selesai'";
$result_hadir = mysqli_query($conn, $query_hadir);
$stats['hadir'] = mysqli_fetch_assoc($result_hadir)['total'];

// Total pasien tidak hadir hari ini
$query_tidakhadir = "SELECT COUNT(*) as total FROM pendaftaran WHERE tanggal_periksa = '$today' AND status = 'tidak hadir'";
$result_tidakhadir = mysqli_query($conn, $query_tidakhadir);
$stats['tidakhadir'] = mysqli_fetch_assoc($result_tidakhadir)['total'];

// Total semua pasien
$query_pasien = "SELECT COUNT(*) as total FROM pasien";
$result_pasien = mysqli_query($conn, $query_pasien);
$stats['pasien'] = mysqli_fetch_assoc($result_pasien)['total'];

// Total pendaftaran bulan ini
$query_month = "SELECT COUNT(*) as total FROM pendaftaran WHERE DATE_FORMAT(tanggal_periksa, '%Y-%m') = '$current_month'";
$result_month = mysqli_query($conn, $query_month);
$stats['month'] = mysqli_fetch_assoc($result_month)['total'];

// Data untuk chart (7 hari terakhir)
$chart_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $day_name = date('D', strtotime($date));
    
    // Terjemahkan hari
    $day_map = ['Sun' => 'Min', 'Mon' => 'Sen', 'Tue' => 'Sel', 'Wed' => 'Rab', 'Thu' => 'Kam', 'Fri' => 'Jum', 'Sat' => 'Sab'];
    $day_indo = $day_map[$day_name];

    $query_chart = "SELECT COUNT(*) as total FROM pendaftaran WHERE tanggal_periksa = '$date'";
    $result_chart = mysqli_query($conn, $query_chart);
    $total = mysqli_fetch_assoc($result_chart)['total'];

    $chart_data[] = [
        'date' => $date,
        'day' => $day_indo,
        'total' => $total
    ];
}

// Data pendaftaran hari ini untuk tabel
$pendaftaran_query = "SELECT d.id, p.nama_lengkap, poli.nama_poli, d.tanggal_daftar, d.status 
                      FROM pendaftaran d 
                      JOIN pasien p ON d.id_pasien = p.id 
                      JOIN poli ON d.id_poli = poli.id 
                      WHERE d.tanggal_periksa = '$today'
                      ORDER BY d.tanggal_daftar DESC";
$pendaftaran_result = mysqli_query($conn, $pendaftaran_query);

// Data aktivitas terbaru
$aktivitas_query = "SELECT p.nama_lengkap, poli.nama_poli, d.tanggal_periksa, d.status, d.tanggal_daftar 
                    FROM pendaftaran d 
                    JOIN pasien p ON d.id_pasien = p.id 
                    JOIN poli ON d.id_poli = poli.id 
                    ORDER BY d.tanggal_daftar DESC 
                    LIMIT 5"; // Limit 5 agar tidak terlalu panjang
$aktivitas_result = mysqli_query($conn, $aktivitas_query);

// Helper function untuk alert (jika belum ada)
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
    <title>Dashboard Admin - Puskesmas Nalumsari</title>
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

        /* --- Stats Cards --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--bg-card);
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .stat-info h3 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0.25rem;
        }

        .stat-info p {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .stat-icon-bg {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        /* Specific Colors for Stats */
        .bg-blue-light { background: #e0f2fe; color: #0284c7; }
        .bg-green-light { background: #dcfce7; color: #16a34a; }
        .bg-red-light { background: #fee2e2; color: #dc2626; }
        .bg-purple-light { background: #f3e8ff; color: #9333ea; }

        /* --- Charts & Tables Containers --- */
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

        /* --- Modern Table --- */
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

        /* Status Badges */
        .badge {
            padding: 0.35rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge-terdaftar { background: #e0f2fe; color: #0369a1; }
        .badge-selesai { background: #dcfce7; color: #15803d; }
        .badge-tidakhadir { background: #fee2e2; color: #b91c1c; }

        /* Buttons */
        .btn-action {
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-size: 0.8rem;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            transition: opacity 0.2s;
        }
        
        .btn-action:hover { opacity: 0.9; }
        .btn-success { background: var(--accent-color); color: white; }
        .btn-danger { background: #ef4444; color: white; }

        /* --- CSS Chart --- */
        .chart-wrapper {
            height: 250px;
            margin-top: 20px;
            display: flex;
            align-items: flex-end;
            gap: 20px;
            padding-bottom: 30px; /* Space for labels */
            border-bottom: 1px solid var(--border-color);
            position: relative;
            background-image: linear-gradient(var(--border-color) 1px, transparent 1px);
            background-size: 100% 50px; /* Grid lines */
        }

        .chart-col {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            height: 100%;
            position: relative;
        }

        .bar {
            width: 50%;
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border-radius: 6px 6px 0 0;
            transition: height 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            min-height: 4px; /* Ensure visible even if 0 */
            cursor: pointer;
        }
        
        .bar:hover {
            filter: brightness(1.1);
        }

        /* Tooltip */
        .bar::before {
            content: attr(data-tooltip);
            position: absolute;
            top: -35px;
            left: 50%;
            transform: translateX(-50%);
            background: #1f2937;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            opacity: 0;
            transition: opacity 0.2s;
            white-space: nowrap;
            pointer-events: none;
            z-index: 10;
        }

        .bar:hover::before { opacity: 1; }

        .bar-label {
            position: absolute;
            bottom: -30px;
            font-size: 0.75rem;
            color: var(--text-muted);
            text-align: center;
            width: 100%;
        }
        
        .bar-date {
            display: block;
            font-size: 0.7rem;
            color: #9ca3af;
        }

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

        /* --- Layout Improvements --- */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .dashboard-section {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: var(--text-muted);
        }

        .no-data i {
            font-size: 2rem;
            margin-bottom: 10px;
            display: block;
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
            
            .dashboard-grid {
                grid-template-columns: 1fr;
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
            .stats-grid { grid-template-columns: 1fr; }
            .top-header { padding: 0 1rem; }
            .content-wrapper { padding: 1rem; }
            .user-info { display: none; } /* Hide name on small screens */
            
            .chart-wrapper {
                gap: 10px;
            }
            .bar { width: 80%; }
            
            .card-box {
                padding: 1rem;
            }
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
                <a href="dashboard.php" class="nav-link active">
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
                <a href="laporan.php" class="nav-link">
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
                <h2 class="page-title">Dashboard Overview</h2>
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

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3><?php echo $stats['today']; ?></h3>
                        <p>Pendaftaran Hari Ini</p>
                    </div>
                    <div class="stat-icon-bg bg-blue-light">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h3><?php echo $stats['hadir']; ?></h3>
                        <p>Pasien Hadir</p>
                    </div>
                    <div class="stat-icon-bg bg-green-light">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h3><?php echo $stats['tidakhadir']; ?></h3>
                        <p>Tidak Hadir</p>
                    </div>
                    <div class="stat-icon-bg bg-red-light">
                        <i class="fas fa-user-times"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h3><?php echo $stats['month']; ?></h3>
                        <p>Total Bulan Ini</p>
                    </div>
                    <div class="stat-icon-bg bg-purple-light">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                </div>
            </div>

            <div class="dashboard-grid">
                <!-- Chart Section -->
                <div class="card-box">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-bar" style="color: var(--primary-color);"></i> Statistik 7 Hari Terakhir</h3>
                    </div>
                    
                    <div class="chart-wrapper">
                        <?php
                        $max_value = 0;
                        foreach ($chart_data as $d) {
                            if ($d['total'] > $max_value) $max_value = $d['total'];
                        }
                        if ($max_value == 0) $max_value = 1; // Prevent division by zero

                        foreach ($chart_data as $data) {
                            $height = ($data['total'] / $max_value) * 100;
                            // Minimal height visual 5% agar bar terlihat walau nilai kecil
                            $visual_height = $data['total'] > 0 ? max($height, 5) : 1;
                            
                            echo '
                            <div class="chart-col">
                                <div class="bar" style="height: ' . $visual_height . '%;" data-tooltip="' . $data['total'] . ' Pasien"></div>
                                <div class="bar-label">
                                    <strong>' . $data['day'] . '</strong>
                                    <span class="bar-date">' . date('d/m', strtotime($data['date'])) . '</span>
                                </div>
                            </div>';
                        }
                        ?>
                    </div>
                </div>

                <!-- Activity Section -->
                <div class="dashboard-section">
                    <div class="card-box">
                         <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-history" style="color: var(--primary-color);"></i> 5 Aktivitas Terakhir</h3>
                        </div>
                        <div class="table-responsive">
                            <table style="font-size: 0.85rem;">
                                <thead>
                                    <tr>
                                        <th>Pasien</th>
                                        <th>Status</th>
                                        <th>Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(mysqli_num_rows($aktivitas_result) > 0): ?>
                                        <?php while ($row = mysqli_fetch_assoc($aktivitas_result)): 
                                             $status_class = 'badge-' . str_replace(' ', '', $row['status']);
                                        ?>
                                        <tr>
                                            <td>
                                                <div style="font-weight: 500;"><?php echo htmlspecialchars($row['nama_lengkap']); ?></div>
                                                <div style="color: var(--text-muted); font-size: 0.75rem;"><?php echo $row['nama_poli']; ?></div>
                                            </td>
                                            <td><span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                            <td style="color: var(--text-muted);"><?php echo date('H:i', strtotime($row['tanggal_daftar'])); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="no-data">
                                                <i class="fas fa-inbox"></i>
                                                Belum ada aktivitas
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Queue Section -->
            <div class="card-box">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list-alt" style="color: var(--primary-color);"></i> 
                        Daftar Antrian Hari Ini
                    </h3>
                    <div class="badge" style="background: #e5e7eb; color: #374151; font-size: 0.9rem;">
                        <?php echo date('d F Y'); ?>
                    </div>
                </div>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="25%">Nama Pasien</th>
                                <th width="20%">Poli Tujuan</th>
                                <th width="15%">Jam Daftar</th>
                                <th width="15%">Status</th>
                                <th width="20%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            if (mysqli_num_rows($pendaftaran_result) > 0) {
                                // Reset pointer data jika diperlukan, atau query ulang di atas
                                mysqli_data_seek($pendaftaran_result, 0); 
                                
                                while ($pendaftaran = mysqli_fetch_assoc($pendaftaran_result)) {
                                    $status_class = 'badge-' . str_replace(' ', '', $pendaftaran['status']);
                                    echo "<tr>
                                            <td>$no</td>
                                            <td>
                                                <div style='font-weight:600;'>" . htmlspecialchars($pendaftaran['nama_lengkap']) . "</div>
                                            </td>
                                            <td>{$pendaftaran['nama_poli']}</td>
                                            <td>" . date('H:i', strtotime($pendaftaran['tanggal_daftar'])) . " WIB</td>
                                            <td><span class='badge $status_class'>" . ucfirst($pendaftaran['status']) . "</span></td>
                                            <td>";
                                    
                                    if ($pendaftaran['status'] == 'terdaftar') {
                                        echo "<div style='display:flex; gap:5px;'>
                                                <a href='update_status.php?id={$pendaftaran['id']}&status=selesai' class='btn-action btn-success' onclick='return confirm(\"Konfirmasi pasien hadir?\")'>
                                                    <i class='fas fa-check'></i> Hadir
                                                </a>
                                                <a href='update_status.php?id={$pendaftaran['id']}&status=tidak hadir' class='btn-action btn-danger' onclick='return confirm(\"Konfirmasi pasien tidak hadir?\")'>
                                                    <i class='fas fa-times'></i> Absen
                                                </a>
                                              </div>";
                                    } else {
                                        echo "<span style='color: #9ca3af; font-size: 0.85rem;'><i class='fas fa-lock'></i> Selesai</span>";
                                    }

                                    echo "</td></tr>";
                                    $no++;
                                }
                            } else {
                                echo "<tr>
                                        <td colspan='6' class='no-data'>
                                            <i class='fas fa-inbox'></i>
                                            Tidak ada pendaftaran untuk hari ini.
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
    </script>
</body>
</html>