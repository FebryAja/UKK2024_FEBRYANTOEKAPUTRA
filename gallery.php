<?php
class Gallery
{
    private $db;
    public function __construct()
    {
        $this->db = new mysqli("localhost", "root", "", "UKK2024_FEBRYANTOEKAPUTRA");
        if ($this->db->connect_error) {
            die("Connection failed: " . $this->db->connect_error);
        }
    }

    public function getPostingan($namaAlbum = null)
    {
        $query = "SELECT foto.*, user.Username AS NamaUser, album.NamaAlbum
              FROM foto
              INNER JOIN user ON foto.UserID = user.UserID
              INNER JOIN album ON foto.AlbumID = album.AlbumID";

        // Jika ada namaAlbum yang diberikan, tambahkan kondisi WHERE
        if ($namaAlbum) {
            $query .= " WHERE album.NamaAlbum = '$namaAlbum'";
        }

        $query .= " ORDER BY foto.FotoID DESC";

        $result = $this->db->query($query);
        $postingan = array();
        while ($row = $result->fetch_assoc()) {
            $row['KomentarFoto'] = $this->getKomentarFoto($row['FotoID']);
            $postingan[] = $row;
        }
        return $postingan;
    }

    public function getKomentarFoto($fotoID)
    {
        $query = "SELECT komentarfoto.*, user.Username AS NamaUser
        FROM komentarfoto
        INNER JOIN user ON komentarfoto.UserID = user.UserID
        WHERE komentarfoto.FotoID = " . $fotoID;
        $result = $this->db->query($query);
        $komentarFoto = array();
        while ($row = $result->fetch_assoc()) {
            $komentarFoto[] = $row;
        }
        return $komentarFoto;
    }

    // mengambil data pada tabel album
    public function generateSelectOptions()
    {
        $query = "SELECT AlbumID, NamaAlbum FROM album";
        $result = $this->db->query($query);
        $albums = $result->fetch_all(MYSQLI_ASSOC);

        $selectOptions = '';
        foreach ($albums as $album) {
            $AlbumID = $album['AlbumID'];
            $NamaAlbum = $album['NamaAlbum'];
            $selectOptions .= '<option value="' . $AlbumID . '">' . $NamaAlbum . '</option>';
        }

        return $selectOptions;
    }

    public function uploadImage($file, $caption, $albumid, $tanggal, $userid)
    {
        $targetDir = "assets/images/";
        $targetFile = $targetDir . basename($file['name']);
        $judulFoto = pathinfo(basename($file['name']), PATHINFO_FILENAME);
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        // Check if the file is an actual image
        $check = getimagesize($file['tmp_name']);
        if ($check === false) {
            return "File is not an image.";
        }

        // Check file size
        if ($file['size'] > 5000000) { // 5 MB
            return "Sorry, your file is too large.";
        }

        // Allow certain file formats
        $allowedFormats = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowedFormats)) {
            return "Sorry, only JPG, JPEG, PNG, and GIF files are allowed.";
        }

        // Move the file to the target directory
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            $this->saveToDatabase($targetFile, $judulFoto, $caption, $albumid, $tanggal, $userid);
            return true;
        } else {
            return "Sorry, there was an error uploading your file.";
        }

    }

    private function saveToDatabase($LokasiFile, $JudulFoto, $DeskripsiFoto, $AlbumID, $TanggalUnggah, $UserID)
    {
        $query = "INSERT INTO foto (LokasiFile, JudulFoto, DeskripsiFoto, AlbumID, TanggalUnggah, UserID) 
              VALUES ('$LokasiFile', '$JudulFoto', '$DeskripsiFoto', '$AlbumID', '$TanggalUnggah', '$UserID')";
        $this->db->query($query);
    }

    // menambahkan hashtag baru
    public function addHashtag($namaHashtag, $deskripsiFoto, $tanggalDibuat, $userID)
    {
        $query = "INSERT INTO album (NamaAlbum, Deskripsi, TanggalDibuat, UserID) 
                  VALUES ('$namaHashtag', '$deskripsiFoto', '$tanggalDibuat', '$userID')";
        if ($this->db->query($query)) {
            return true;
        } else {
            return "Failed to add hashtag.";
        }
    }
    // check hashtag jika hashtag sudah ada
    public function checkHashtagExists($namaHashtag)
    {
        $query = "SELECT * FROM Album WHERE NamaAlbum = '$namaHashtag'";
        $result = $this->db->query($query);
        if ($result && $result->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    // memanggil album
    public function getAlbum()
    {
        $query = "SELECT * FROM album";
        $result = $this->db->query($query);
        $albums = array();

        while ($row = $result->fetch_assoc()) {
            $albums[] = $row['NamaAlbum'];
        }

        return $albums;
    }

    // Fungsi untuk menyimpan komentar ke dalam tabel komentarfoto
    public function saveComment($fotoID, $userID, $isiKomentar, $tanggalKomentar)
    {
        // Lakukan sanitasi pada data yang akan disimpan ke database (menghindari serangan SQL injection)
        $isiKomentar = $this->db->real_escape_string($isiKomentar);

        // Buat query untuk menyimpan komentar
        $query = "INSERT INTO komentarfoto (FotoID, UserID, IsiKomentar, TanggalKomentar) 
                  VALUES ('$fotoID', '$userID', '$isiKomentar', '$tanggalKomentar')";

        // Eksekusi query
        if ($this->db->query($query) === TRUE) {
            return true;
        } else {
            echo "Error: " . $this->db->error;
        }
    }

    // Fungsi untuk menyimpan like ke tabel `likefoto`
    public function saveLike($fotoID, $userID, $timestamp)
    {
        // Query untuk menyimpan data like
        $sql = "INSERT INTO likefoto (FotoID, UserID, TanggalLike) VALUES ('$fotoID', '$userID', '$timestamp')";

        if ($this->db->query($sql) === TRUE) {
            return true;
        } else {
            echo "Gagal menyimpan like: " . $this->db->error;
        }
    }

    // Fungsi untuk menghapus like dari tabel `likefoto`
    public function removeLike($fotoID, $userID)
    {
        // Query untuk menghapus data like
        $sql = "DELETE FROM likefoto WHERE FotoID = '$fotoID' AND UserID = '$userID'";

        if ($this->db->query($sql) === TRUE) {
            return true;
        } else {
            echo "Gagal menghapus like: " . $this->db->error;
        }
    }

    // menghitung jumlah like pada postingan
    public function getLikeCount($fotoID)
    {
        $sql = "SELECT COUNT(*) AS likeCount FROM likefoto WHERE fotoID = $fotoID";
        $result = $this->db->query($sql);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $likeCount = $row["likeCount"];
            return $likeCount;
        } else {
            return 0;
        }
    }


    // Fungsi untuk memeriksa apakah postingan sudah dilike oleh pengguna
    public function isLikedByUser($fotoID, $userID)
    {
        // Query untuk memeriksa keberadaan data like
        $sql = "SELECT * FROM likefoto WHERE FotoID = '$fotoID' AND UserID = '$userID'";
        $result = $this->db->query($sql);

        if ($result->num_rows > 0) {
            // Data like ditemukan
            return true;
        } else {
            // Data like tidak ditemukan
            return false;
        }
    }

    // hapus gambar
    public function deleteImage($FotoID)
    {
        // Dapatkan informasi file yang akan dihapus
        $imageInfo = $this->getImageInfo($FotoID);

        if (!$imageInfo) {
            return "Foto tidak ditemukan.";
        }

        // Hapus file dari direktori
        if (unlink($imageInfo['LokasiFile'])) {
            // Hapus data like yang terkait dengan FotoID
            $query = "DELETE FROM likefoto WHERE FotoID = $FotoID";
            $this->db->query($query);

            // Hapus data komen yang terkait dengan FotoID
            $query = "DELETE FROM komentarfoto WHERE FotoID = $FotoID";
            $this->db->query($query);

            // Hapus data foto
            $query = "DELETE FROM foto WHERE FotoID = $FotoID";
            $this->db->query($query);

            return true;
        } else {
            return "Gagal menghapus foto.";
        }
    }




    // edit foto
    public function editImage($FotoID, $file, $newCaption)
    {
        $imageInfo = $this->getImageInfo($FotoID);

        if (!$imageInfo) {
            return "Foto tidak ditemukan.";
        }

        // Jika tidak ada file yang diunggah, hanya edit caption
        if (empty($file['name'])) {
            $query = "UPDATE foto SET DeskripsiFoto = '$newCaption' WHERE FotoID = $FotoID";
            $this->db->query($query);
            return true;
        }

        // Jika ada file yang diunggah, proses seperti biasa
        $targetDir = "assets/images/";
        $targetFile = $targetDir . basename($file['name']);
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        $check = getimagesize($file['tmp_name']);
        if ($check === false) {
            return "File is not an image.";
        }

        // Check file size
        if ($file['size'] > 5000000) { // 5 MB
            return "Sorry, your file is too large.";
        }

        // Allow certain file formats
        $allowedFormats = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowedFormats)) {
            return "Sorry, only JPG, JPEG, PNG, and GIF files are allowed.";
        }

        // Hapus file lama
        unlink($imageInfo['LokasiFile']);

        // Pindahkan file baru
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            // Update caption di database
            $query = "UPDATE foto SET LokasiFile = '$targetFile', DeskripsiFoto = '$newCaption' WHERE FotoID = $FotoID";
            $this->db->query($query);
            return true;
        } else {
            return "Gagal mengupload file.";
        }
    }

    public function getImageInfo($FotoID)
    {
        $query = "SELECT * FROM foto WHERE FotoID = $FotoID";
        $result = $this->db->query($query);
        return $result->fetch_assoc();
    }

    // logout
    public function logout()
    {
        // Hapus session yang terkait dengan pengguna yang telah login
        session_start();
        session_unset();
        session_destroy();

        // Redirect pengguna ke halaman login atau halaman lain yang sesuai
        header("Location: login.php");
        exit;
    }

    // edit user
    public function editUser($UserID, $Username, $password)
    {

        if (empty($password)) {
            $query = "UPDATE user SET Username = '$Username' WHERE UserID = $UserID";
            // Eksekusi query
            if ($this->db->query($query) === TRUE) {
                $this->logout(); // Menjalankan proses logout otomatis
                return true;
            } else {
                echo "Error: " . $this->db->error;
            }
        }

        // Menghash password menggunakan SHA-256
        $hashedPassword = hash('sha256', $password);

        // Buat query untuk menyimpan komentar
        $query = "UPDATE user SET Username = '$Username', Password = '$hashedPassword' WHERE UserID = $UserID";


        // Eksekusi query
        if ($this->db->query($query) === TRUE) {
            $this->logout(); // Menjalankan proses logout otomatis
            return true;
        } else {
            echo "Error: " . $this->db->error;
        }
    }

    public function getUserInfo($UserID)
    {
        $query = "SELECT * FROM user WHERE UserID = $UserID";
        $result = $this->db->query($query);
        return $result->fetch_assoc();
    }

    public function getJumlahPengguna()
    {
        $queryPengguna = "SELECT COUNT(*) as total FROM user";
        $resultPengguna = $this->db->query($queryPengguna);
        $dataPengguna = $resultPengguna->fetch_assoc();
        $jumlahPengguna = $dataPengguna['total'];

        return $jumlahPengguna;
    }

    public function getJumlahFoto()
    {
        $queryFoto = "SELECT COUNT(*) as total FROM foto";
        $resultFoto = $this->db->query($queryFoto);
        $dataFoto = $resultFoto->fetch_assoc();
        $jumlahFoto = $dataFoto['total'];

        return $jumlahFoto;
    }

}
?>