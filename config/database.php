<?php
session_start();

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'puskesmas');

// Koneksi Database
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Fungsi Helper
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

function isPasien() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'pasien';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function alert($type, $message) {
    $_SESSION['alert'] = ['type' => $type, 'message' => $message];
}

function showAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        $class = $alert['type'] == 'success' ? 'alert-success' : 'alert-error';
        echo "<div class='alert $class'>{$alert['message']}</div>";
        unset($_SESSION['alert']);
    }
}

function getHariIndonesia($hari) {
    $days = [
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
        'Sunday' => 'Minggu'
    ];
    return $days[$hari] ?? $hari;
}
?>