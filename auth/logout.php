<?php
include '../config/database.php';

session_destroy();
alert('success', 'Anda telah berhasil logout.');
redirect('../index.php');
?>