<?php
include '../config/database.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/admin_login.php');
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $status = mysqli_real_escape_string($conn, $_GET['status']);
    
    // Validasi status
    $allowed_status = ['selesai', 'tidak hadir'];
    if (!in_array($status, $allowed_status)) {
        alert('error', 'Status tidak valid!');
        redirect('dashboard.php');
    }
    
    $query = "UPDATE pendaftaran SET status = '$status' WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        $status_text = $status == 'selesai' ? 'hadir' : 'tidak hadir';
        alert('success', "Status pasien berhasil diubah menjadi $status_text!");
    } else {
        alert('error', 'Gagal mengupdate status pasien!');
    }
} else {
    alert('error', 'Parameter tidak lengkap!');
}

redirect('dashboard.php');
?>