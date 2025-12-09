<?php
include '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/admin_login.php");
    exit;
}

// Handle delete patient
if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($conn, $_GET['hapus']);
    
    // Cek apakah pasien memiliki riwayat pendaftaran
    $check_pendaftaran = "SELECT COUNT(*) as total FROM pendaftaran WHERE id_pasien = $id";
    $result_pendaftaran = mysqli_query($conn, $check_pendaftaran);
    $total_pendaftaran = mysqli_fetch_assoc($result_pendaftaran)['total'];
    
    if ($total_pendaftaran > 0) {
        $_SESSION['error'] = 'Tidak dapat menghapus pasien karena memiliki riwayat pendaftaran!';
    } else {
        $query = "DELETE FROM pasien WHERE id = $id";
        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = 'Pasien berhasil dihapus!';
        } else {
            $_SESSION['error'] = 'Gagal menghapus pasien!';
        }
    }
    header("Location: pasien.php");
    exit;
}

// Handle reset password
if (isset($_GET['reset_password'])) {
    $id = mysqli_real_escape_string($conn, $_GET['reset_password']);
    $default_password = md5('123456'); // Password default
    
    $query = "UPDATE pasien SET password = '$default_password' WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = 'Password pasien berhasil direset ke "123456"!';
    } else {
        $_SESSION['error'] = 'Gagal mereset password pasien!';
    }
    header("Location: pasien.php");
    exit;
}

$pasien_query = "SELECT * FROM pasien ORDER BY nama_lengkap";
$pasien_result = mysqli_query($conn, $pasien_query);

// Hitung total pasien
$total_pasien_query = "SELECT COUNT(*) as total FROM pasien";
$total_pasien_result = mysqli_query($conn, $total_pasien_query);
$total_pasien = mysqli_fetch_assoc($total_pasien_result)['total'];

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
    <title>Data Pasien - Puskesmas Nalumsari</title>
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
        .bg-purple-light { background: #f3e8ff; color: #9333ea; }

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

        /* --- Search Box --- */
        .search-box {
            position: relative;
            max-width: 400px;
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 0.9rem;
            transition: all 0.2s;
            background-color: var(--bg-card);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(22, 160, 133, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        /* --- Tables --- */
        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 1000px;
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

        .badge-male { background: #e0f2fe; color: #0369a1; }
        .badge-female { background: #fce7f3; color: #be185d; }

        /* --- Buttons --- */
        .btn {
            padding: 0.5rem 1rem;
            border-radius: var(--radius-md);
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
        }

        .btn-warning:hover {
            background: #d97706;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
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

        /* --- Utility Classes --- */
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

        .text-truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 200px;
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
            .stats-grid { grid-template-columns: 1fr; }
            
            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .search-box {
                max-width: 100%;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .user-info { display: none; }
        }

        @media (max-width: 640px) {
            .action-buttons .btn {
                width: 100%;
                justify-content: center;
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
                <a href="pasien.php" class="nav-link active">
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
                <h2 class="page-title">Kelola Data Pasien</h2>
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

            <!-- Stats Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3><?php echo $total_pasien; ?></h3>
                        <p>Total Pasien</p>
                    </div>
                    <div class="stat-icon-bg bg-blue-light">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>

            <!-- Patients Table -->
            <div class="card-box">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i>
                        Daftar Pasien Terdaftar
                    </h3>
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="searchInput" class="search-input" placeholder="Cari pasien...">
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table id="pasienTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIK</th>
                                <th>Nama Lengkap</th>
                                <th>Jenis Kelamin</th>
                                <th>No HP</th>
                                <th>Email</th>
                                <th>Alamat</th>
                                <th>Tanggal Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            if (mysqli_num_rows($pasien_result) > 0) {
                                mysqli_data_seek($pasien_result, 0);
                                while ($pasien = mysqli_fetch_assoc($pasien_result)) {
                                    $jk_badge = $pasien['jenis_kelamin'] == 'L' ? 
                                        '<span class="badge badge-male">Laki-laki</span>' : 
                                        '<span class="badge badge-female">Perempuan</span>';
                                    
                                    echo "<tr>
                                            <td>$no</td>
                                            <td>{$pasien['nik']}</td>
                                            <td>
                                                <div style='font-weight:600;'>{$pasien['nama_lengkap']}</div>
                                            </td>
                                            <td>$jk_badge</td>
                                            <td>{$pasien['no_hp']}</td>
                                            <td>{$pasien['email']}</td>
                                            <td class='text-truncate' title='{$pasien['alamat']}'>{$pasien['alamat']}</td>
                                            <td>" . date('d/m/Y', strtotime($pasien['created_at'])) . "</td>
                                            <td>
                                                <div class='action-buttons'>
                                                    <a href='pasien.php?reset_password={$pasien['id']}' 
                                                       class='btn btn-warning' 
                                                       onclick='return confirm(\"Reset password {$pasien['nama_lengkap']} menjadi 123456?\")'>
                                                        <i class='fas fa-key'></i> Reset
                                                    </a>
                                                    <a href='pasien.php?hapus={$pasien['id']}' 
                                                       class='btn btn-danger' 
                                                       onclick='return confirm(\"Yakin menghapus pasien {$pasien['nama_lengkap']}?\")'>
                                                        <i class='fas fa-trash'></i> Hapus
                                                    </a>
                                                </div>
                                            </td>
                                          </tr>";
                                    $no++;
                                }
                            } else {
                                echo "<tr>
                                        <td colspan='9' class='no-data'>
                                            <i class='fas fa-inbox'></i>
                                            <div>Belum ada data pasien</div>
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

        // Search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const table = document.getElementById('pasienTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            searchInput.addEventListener('keyup', function() {
                const filter = this.value.toLowerCase();
                
                for (let i = 0; i < rows.length; i++) {
                    const cells = rows[i].getElementsByTagName('td');
                    let found = false;
                    
                    for (let j = 0; j < cells.length; j++) {
                        const cellText = cells[j].textContent || cells[j].innerText;
                        if (cellText.toLowerCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                    
                    rows[i].style.display = found ? '' : 'none';
                }
            });
            
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
        });
    </script>
</body>
</html>