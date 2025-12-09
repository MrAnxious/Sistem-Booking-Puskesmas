<?php
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $nik = mysqli_real_escape_string($conn, $_POST['nik']);
    $jenis_kelamin = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $password = md5($_POST['password']);
    
    // Cek apakah NIK sudah ada
    $check_query = "SELECT id FROM pasien WHERE nik = '$nik'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        alert('error', 'NIK sudah terdaftar!');
        redirect('register.php');
    }
    
    // Cek apakah email sudah ada
    $check_email = "SELECT id FROM pasien WHERE email = '$email'";
    $result_email = mysqli_query($conn, $check_email);
    
    if (mysqli_num_rows($result_email) > 0) {
        alert('error', 'Email sudah terdaftar!');
        redirect('register.php');
    }
    
    // Insert data pasien baru
    $query = "INSERT INTO pasien (nama_lengkap, nik, jenis_kelamin, no_hp, email, alamat, password) 
              VALUES ('$nama_lengkap', '$nik', '$jenis_kelamin', '$no_hp', '$email', '$alamat', '$password')";
    
    if (mysqli_query($conn, $query)) {
        alert('success', 'Pendaftaran berhasil! Silakan login.');
        redirect('login.php');
    } else {
        alert('error', 'Terjadi kesalahan. Silakan coba lagi.');
        redirect('register.php');
    }
} else {
    redirect('register.php');
}
?>