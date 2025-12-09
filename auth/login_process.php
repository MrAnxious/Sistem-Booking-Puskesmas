<?php
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nik = mysqli_real_escape_string($conn, $_POST['nik']);
    $password = md5($_POST['password']);
    
    $query = "SELECT * FROM pasien WHERE nik = '$nik' AND password = '$password'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama_lengkap'];
        $_SESSION['role'] = 'pasien';
        $_SESSION['nik'] = $user['nik'];
        
        alert('success', 'Login berhasil!');
        redirect('../pasien/dashboard.php');
    } else {
        alert('error', 'NIK atau password salah!');
        redirect('login.php');
    }
} else {
    redirect('login.php');
}
?>