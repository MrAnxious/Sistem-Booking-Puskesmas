<?php
include '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/admin_login.php");
    exit;
}

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['tambah'])) {
        $id_poli = mysqli_real_escape_string($conn, $_POST['id_poli']);
        $hari = mysqli_real_escape_string($conn, $_POST['hari']);
        $jam_mulai = mysqli_real_escape_string($conn, $_POST['jam_mulai']);
        $jam_selesai = mysqli_real_escape_string($conn, $_POST['jam_selesai']);
        
        // Cek duplikasi
        $check_query = "SELECT id FROM jam_poli WHERE id_poli = $id_poli AND hari = '$hari'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $_SESSION['error'] = 'Jam poli untuk hari tersebut sudah ada!';
        } else {
            $query = "INSERT INTO jam_poli (id_poli, hari, jam_mulai, jam_selesai) 
                      VALUES ($id_poli, '$hari', '$jam_mulai', '$jam_selesai')";
            if (mysqli_query($conn, $query)) {
                $_SESSION['success'] = 'Jam poli berhasil ditambahkan!';
            } else {
                $_SESSION['error'] = 'Gagal menambahkan jam poli!';
            }
        }
    } elseif (isset($_POST['edit'])) {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $id_poli = mysqli_real_escape_string($conn, $_POST['id_poli']);
        $hari = mysqli_real_escape_string($conn, $_POST['hari']);
        $jam_mulai = mysqli_real_escape_string($conn, $_POST['jam_mulai']);
        $jam_selesai = mysqli_real_escape_string($conn, $_POST['jam_selesai']);
        
        // Cek duplikasi (kecuali data yang sedang diedit)
        $check_query = "SELECT id FROM jam_poli WHERE id_poli = $id_poli AND hari = '$hari' AND id != $id";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $_SESSION['error'] = 'Jam poli untuk hari tersebut sudah ada!';
        } else {
            $query = "UPDATE jam_poli SET id_poli = $id_poli, hari = '$hari', jam_mulai = '$jam_mulai', jam_selesai = '$jam_selesai' 
                      WHERE id = $id";
            if (mysqli_query($conn, $query)) {
                $_SESSION['success'] = 'Jam poli berhasil diupdate!';
            } else {
                $_SESSION['error'] = 'Gagal mengupdate jam poli!';
            }
        }
    }
}

if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($conn, $_GET['hapus']);
    
    $query = "DELETE FROM jam_poli WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = 'Jam poli berhasil dihapus!';
    } else {
        $_SESSION['error'] = 'Gagal menghapus jam poli!';
    }
    header("Location: jam_poli.php");
    exit;
}

// Ambil data poli untuk dropdown
$poli_query = "SELECT * FROM poli ORDER BY nama_poli";
$poli_result = mysqli_query($conn, $poli_query);

// Ambil data jam poli
$jam_poli_query = "SELECT jp.*, p.nama_poli 
                   FROM jam_poli jp 
                   JOIN poli p ON jp.id_poli = p.id 
                   ORDER BY p.nama_poli, FIELD(jp.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')";
$jam_poli_result = mysqli_query($conn, $jam_poli_query);

$hari_list = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

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
    <title>Kelola Jam Poli - Puskesmas Nalumsari</title>
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

        /* --- Forms --- */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
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
            margin-top: 1.5rem;
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

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
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
        .hidden {
            display: none;
        }

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
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .user-info { display: none; }
        }

        @media (max-width: 640px) {
            .form-actions .btn {
                width: 100%;
                justify-content: center;
            }
            
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
                <a href="jam_poli.php" class="nav-link active">
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
                <h2 class="page-title">Kelola Jam Praktek Poli</h2>
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

            <!-- Form Tambah Jam Poli -->
            <div class="card-box" id="form-tambah">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus-circle"></i>
                        Tambah Jam Praktek Baru
                    </h3>
                </div>
                
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label" for="id_poli">
                                <i class="fas fa-stethoscope"></i>
                                Poli
                            </label>
                            <select id="id_poli" name="id_poli" class="form-control" required>
                                <option value="">-- Pilih Poli --</option>
                                <?php
                                while ($poli = mysqli_fetch_assoc($poli_result)) {
                                    echo "<option value='{$poli['id']}'>{$poli['nama_poli']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="hari">
                                <i class="fas fa-calendar-day"></i>
                                Hari
                            </label>
                            <select id="hari" name="hari" class="form-control" required>
                                <option value="">-- Pilih Hari --</option>
                                <?php
                                foreach ($hari_list as $hari) {
                                    echo "<option value='$hari'>$hari</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label" for="jam_mulai">
                                <i class="fas fa-play-circle"></i>
                                Jam Mulai
                            </label>
                            <input type="time" id="jam_mulai" name="jam_mulai" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="jam_selesai">
                                <i class="fas fa-stop-circle"></i>
                                Jam Selesai
                            </label>
                            <input type="time" id="jam_selesai" name="jam_selesai" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="tambah" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Simpan Jam Praktek
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class="fas fa-redo"></i>
                            Reset Form
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Form Edit Jam Poli (Hidden by default) -->
            <div class="card-box hidden" id="form-edit">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit"></i>
                        Edit Jam Praktek
                    </h3>
                </div>
                
                <form method="POST">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label" for="edit_id_poli">
                                <i class="fas fa-stethoscope"></i>
                                Poli
                            </label>
                            <select id="edit_id_poli" name="id_poli" class="form-control" required>
                                <option value="">-- Pilih Poli --</option>
                                <?php
                                // Reset pointer result
                                mysqli_data_seek($poli_result, 0);
                                while ($poli = mysqli_fetch_assoc($poli_result)) {
                                    echo "<option value='{$poli['id']}'>{$poli['nama_poli']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="edit_hari">
                                <i class="fas fa-calendar-day"></i>
                                Hari
                            </label>
                            <select id="edit_hari" name="hari" class="form-control" required>
                                <option value="">-- Pilih Hari --</option>
                                <?php
                                foreach ($hari_list as $hari) {
                                    echo "<option value='$hari'>$hari</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label" for="edit_jam_mulai">
                                <i class="fas fa-play-circle"></i>
                                Jam Mulai
                            </label>
                            <input type="time" id="edit_jam_mulai" name="jam_mulai" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="edit_jam_selesai">
                                <i class="fas fa-stop-circle"></i>
                                Jam Selesai
                            </label>
                            <input type="time" id="edit_jam_selesai" name="jam_selesai" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="edit" class="btn btn-success">
                            <i class="fas fa-save"></i>
                            Update Jam Praktek
                        </button>
                        <button type="button" onclick="batalEdit()" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Batal Edit
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Daftar Jam Poli -->
            <div class="card-box">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i>
                        Daftar Jam Praktek Poli
                    </h3>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Poli</th>
                                <th>Hari</th>
                                <th>Jam Mulai</th>
                                <th>Jam Selesai</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            if (mysqli_num_rows($jam_poli_result) > 0) {
                                mysqli_data_seek($jam_poli_result, 0); // Reset pointer
                                while ($jam_poli = mysqli_fetch_assoc($jam_poli_result)) {
                                    echo "<tr>
                                            <td>$no</td>
                                            <td>{$jam_poli['nama_poli']}</td>
                                            <td>{$jam_poli['hari']}</td>
                                            <td>" . date('H:i', strtotime($jam_poli['jam_mulai'])) . "</td>
                                            <td>" . date('H:i', strtotime($jam_poli['jam_selesai'])) . "</td>
                                            <td>
                                                <div class='action-buttons'>
                                                    <button onclick='editJamPoli({$jam_poli['id']}, {$jam_poli['id_poli']}, \"{$jam_poli['hari']}\", \"{$jam_poli['jam_mulai']}\", \"{$jam_poli['jam_selesai']}\")' 
                                                            class='btn btn-primary btn-sm'>
                                                        <i class='fas fa-edit'></i> Edit
                                                    </button>
                                                    <a href='jam_poli.php?hapus={$jam_poli['id']}' 
                                                       class='btn btn-danger btn-sm' 
                                                       onclick='return confirm(\"Yakin menghapus jam praktek ini?\")'>
                                                        <i class='fas fa-trash'></i> Hapus
                                                    </a>
                                                </div>
                                            </td>
                                          </tr>";
                                    $no++;
                                }
                            } else {
                                echo "<tr>
                                        <td colspan='6' class='no-data'>
                                            <i class='fas fa-inbox'></i>
                                            <div>Belum ada data jam praktek</div>
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

        // Edit Jam Poli Function
        function editJamPoli(id, id_poli, hari, jam_mulai, jam_selesai) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_id_poli').value = id_poli;
            document.getElementById('edit_hari').value = hari;
            document.getElementById('edit_jam_mulai').value = jam_mulai;
            document.getElementById('edit_jam_selesai').value = jam_selesai;
            
            // Show edit form, hide add form
            document.getElementById('form-edit').classList.remove('hidden');
            document.getElementById('form-tambah').classList.add('hidden');
            
            // Scroll to edit form
            document.getElementById('form-edit').scrollIntoView({ behavior: 'smooth' });
        }
        
        function batalEdit() {
            // Show add form, hide edit form
            document.getElementById('form-tambah').classList.remove('hidden');
            document.getElementById('form-edit').classList.add('hidden');
            
            // Reset form
            document.getElementById('edit_id').value = '';
            document.getElementById('edit_id_poli').value = '';
            document.getElementById('edit_hari').value = '';
            document.getElementById('edit_jam_mulai').value = '';
            document.getElementById('edit_jam_selesai').value = '';
        }
        
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

        // Validasi waktu
        const jamMulaiInput = document.getElementById('jam_mulai');
        const jamSelesaiInput = document.getElementById('jam_selesai');
        const editJamMulaiInput = document.getElementById('edit_jam_mulai');
        const editJamSelesaiInput = document.getElementById('edit_jam_selesai');
        
        function validateTime(startInput, endInput) {
            if (startInput.value && endInput.value) {
                if (startInput.value >= endInput.value) {
                    endInput.setCustomValidity('Jam selesai harus setelah jam mulai');
                    return false;
                } else {
                    endInput.setCustomValidity('');
                    return true;
                }
            }
            return true;
        }
        
        if (jamMulaiInput && jamSelesaiInput) {
            jamMulaiInput.addEventListener('change', () => validateTime(jamMulaiInput, jamSelesaiInput));
            jamSelesaiInput.addEventListener('change', () => validateTime(jamMulaiInput, jamSelesaiInput));
        }
        
        if (editJamMulaiInput && editJamSelesaiInput) {
            editJamMulaiInput.addEventListener('change', () => validateTime(editJamMulaiInput, editJamSelesaiInput));
            editJamSelesaiInput.addEventListener('change', () => validateTime(editJamMulaiInput, editJamSelesaiInput));
        }

        // Form submission validation
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const jamMulai = this.querySelector('input[name="jam_mulai"]');
                    const jamSelesai = this.querySelector('input[name="jam_selesai"]');
                    
                    if (jamMulai && jamSelesai) {
                        if (!validateTime(jamMulai, jamSelesai)) {
                            e.preventDefault();
                            alert('Jam selesai harus setelah jam mulai!');
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>