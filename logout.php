<?php
session_start();

// Hapus semua variabel session
$_SESSION = array();

// Hapus session
session_destroy();

// Mengarahkan pengguna ke halaman login
header("Location: login.php");
exit();
?>