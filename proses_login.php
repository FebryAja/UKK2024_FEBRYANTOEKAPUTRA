<?php
session_start();

// Konfigurasi database
$host = 'localhost';
$db = 'ukk2024_febryantoekaputra';
$user = 'root';
$pass = '';

// Membuat koneksi ke database
$koneksi = new mysqli($host, $user, $pass, $db);

// Memeriksa koneksi
if ($koneksi->connect_error) {
    die("Koneksi database gagal: " . $koneksi->connect_error);
}

// Menangkap data yang dikirimkan melalui formulir
$username = $_POST['username'];
$password = $_POST['password'];

// Menghash password menggunakan SHA-256
$hashedPassword = hash('sha256', $password);

// Mengecek kecocokan akun dalam database
$sql = "SELECT * FROM user WHERE Username = '$username' AND Password = '$hashedPassword'";  
$result = $koneksi->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $userID = $row['UserID']; // Mendapatkan ID pengguna dari hasil query

    // Akun ditemukan, lakukan tindakan yang sesuai, misalnya mengarahkan ke halaman utama
    $_SESSION['currentUser'] = array(
        'id' => $userID,
        'username' => $username
    ); // Menyimpan ID pengguna dan username ke dalam session

    // Mengarahkan pengguna ke halaman utama
    header("Location: index.php");
    exit();
} else {
    // Akun tidak ditemukan, berikan pesan kesalahan
    $_SESSION['error'] = "Username atau password salah!";
    header("Location: login.php");
    exit();
}

// Menutup koneksi database
$koneksi->close();
?>