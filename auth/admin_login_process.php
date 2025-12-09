<?php
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']);
    
    $query = "SELECT * FROM admin WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $admin = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['nama'] = $admin['nama_lengkap'];
        $_SESSION['role'] = 'admin';
        $_SESSION['username'] = $admin['username'];
        
        alert('success', 'Login admin berhasil!');
        redirect('../admin/dashboard.php');
    } else {
        alert('error', 'Username atau password salah!');
        redirect('admin_login.php');
    }
} else {
    redirect('admin_login.php');
}
?>