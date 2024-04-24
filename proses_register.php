<?php
// Konfigurasi database
$host = 'localhost';
$db   = 'ukk2024_febryantoekaputra';
$user = 'root';
$pass = '';

// Membuat koneksi ke database
$koneksi = new mysqli($host, $user, $pass, $db);

// Memeriksa koneksi
if ($koneksi->connect_error) {
    die("Koneksi database gagal: " . $koneksi->connect_error);
}

// Memeriksa apakah form telah disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Menangkap data yang dikirimkan melalui formulir
    $username     = $_POST['username'];
    $password     = $_POST['password'];
    $email        = $_POST['email'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $alamat       = $_POST['alamat'];

    // Memeriksa apakah username sudah digunakan
    $checkUsernameQuery = "SELECT * FROM user WHERE Username = '$username'";
    $result = $koneksi->query($checkUsernameQuery);

    if ($result->num_rows > 0) {
        // Jika username sudah digunakan, simpan pesan kesalahan dalam session
        session_start();
        $_SESSION['username_error'] = 'Username sudah digunakan. Silakan gunakan username lain.';

        // Mengarahkan pengguna kembali ke halaman register
        header("Location: register.php");
        exit();
    }

    // Menghash password menggunakan SHA-256
    $hashedPassword = hash('sha256', $password);

    // Menyimpan data ke database
    $sql = "INSERT INTO user (Username, Password, Email, NamaLengkap, Alamat) VALUES ('$username', '$hashedPassword', '$email', '$nama_lengkap', '$alamat')";

    if ($koneksi->query($sql) === TRUE) {
        echo "Registrasi berhasil!";
        // Mengarahkan pengguna ke halaman login.php
        header("Location: login.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $koneksi->error;
    }
}

// Menutup koneksi database
$koneksi->close();
?>