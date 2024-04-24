<?php
// index.php
require_once 'gallery.php';
$gallery = new Gallery();
// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $uploadResult = $gallery->uploadImage($_FILES['image'], $_POST['DeskripsiFoto'], $_POST['AlbumID'], $_POST['TanggalUnggah'], $_POST['UserID']);
    if ($uploadResult === true) {
        header("Location: index.php");
        exit();
    } else {
        $error = $uploadResult;
    }
}

// Handle new hashtag
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Deskripsi'])) {
    $namaHashtag = $_POST['NamaHashtag'];
    $deskripsi = $_POST['Deskripsi'];
    $tanggalDibuat = $_POST['TanggalDibuat'];
    $userID = $_POST['UserID'];

    // Check if the hashtag already exists in the database
    $existingHashtag = $gallery->checkHashtagExists($namaHashtag);
    if ($existingHashtag) {
        $error = "The hashtag already exists.";
    } else {
        $addHashtagResult = $gallery->addHashtag($namaHashtag, $deskripsi, $tanggalDibuat, $userID);
        if ($addHashtagResult === true) {
            header("Location: index.php");
            exit();
        } else {
            $error = $addHashtagResult;
        }
    }
}

// hapus photo
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['FotoID'])) {
    $deleteResult = $gallery->deleteImage($_GET['FotoID']);
    if ($deleteResult === true) {
        header("Location: index.php");
        exit();
    } else {
        $error = $deleteResult;
    }
}

$postingan = $gallery->getPostingan();
session_start();
if (isset($_SESSION['currentUser'])) {
    $userID = $_SESSION['currentUser']['id'];
    $username = $_SESSION['currentUser']['username'];
} else {
    // Redirect atau tindakan lain jika pengguna tidak login
    // Misalnya:
    header('Location: login.php');
    exit();
}

// Handle new comment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['komentar'])) {
    $isiKomentar = $_POST['komentar'];
    $userID = $_SESSION['currentUser']['id'];
    $fotoID = $_POST['fotoIDKomen'];
    $tanggalKomentar = date('Y-m-d'); // Format tanggal sesuaikan dengan kebutuhan

    $addCommentResult = $gallery->saveComment($fotoID, $userID, $isiKomentar, $tanggalKomentar);
    if ($addCommentResult === true) {
        header("Location: index.php");
        exit();
    } else {
        $error = $addCommentResult;
    }
}

// Proses like dan unlike
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit'])) {
        $fotoID = $_POST['fotoID'];
        $userID = $_SESSION['currentUser']['id'];

        // Membuat instance dari class Gallery
        $gallery = new Gallery();

        // Periksa apakah data like sudah ada
        if ($gallery->isLikedByUser($fotoID, $userID)) {
            // Jika sudah ada, hapus data like
            $gallery->removeLike($fotoID, $userID);
        } else {
            // Jika belum ada, simpan data like
            $timestamp = date("Y-m-d H:i:s");
            $gallery->saveLike($fotoID, $userID, $timestamp);
        }
    }
}

$namaAlbum = $_GET['album'] ?? null;
$postingan = $gallery->getPostingan($namaAlbum);

// Gunakan $postingan untuk menampilkan postingan yang sesuai dengan album yang dipilih

$albums = $gallery->getAlbum();

$jumlahPengguna = $gallery->getJumlahPengguna();
$jumlahFoto = $gallery->getJumlahFoto();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- jquery -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"
        integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>

    <!-- ajax -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />

    <!-- chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>



    <link rel="stylesheet" href="./assets/css/style.css">
</head>


<body style="background-color: #000;">
    <div class="d-flex ">
        <div class="flex-column flex-shrink-0  text-bg-light  min-vh-100 d-none  d-md-flex bg-transparent"
            style="width: 270px"></div>

        <div class="flex-column  p-3 justify-content-center  flex-shrink-0  min-vh-100 position-fixed d-none d-md-flex z-3"
            style="width: 270px" id="sidebar">
            <a href="index.php"
                class="d-flex align-items-center   mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                <img src="assets/images/logo.svg" class="me-3 ms-2
                " alt="">
                <span class="fs-4">FGram</span>
            </a>
            <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="index.php" class="nav-link active bg-dark " aria-current="page">
                        <i class="bi bi-house me-2 fs-5"></i>
                        Home
                    </a>
                </li>

                <li class="nav-item border rounded-2 rounded border-dark mt-3">
                    <p class="nav-link text-white my-0" aria-current="page">
                        <i class="bi bi-hash me-2 fs-5"></i>
                        Hashtags
                    </p>
                    <ul id="albumFoto" class="p-3 list-unstyled d-flex gap-3 flex-wrap overflow-y-auto">
                        <?php foreach ($albums as $album) {
                            echo '<li><a class="text-info fw-medium text-decoration-none" href="index.php?album=' . $album . '">#' . $album . '</a></li>';
                        } ?>
                    </ul>
                </li>

            </ul>
            <hr>
            <?php
            // Periksa apakah pengguna sudah login
            if (isset($_SESSION['currentUser'])) {

                // Jika pengguna sudah login, tampilkan nama pengguna
                $avatarUrl = "./assets/images/pp.jpg"; // Ganti dengan URL avatar yang sesuai
                ?>
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?php echo $avatarUrl; ?>" alt="" width="32" height="32" class="rounded-circle me-2">
                        <strong><?php echo $username; ?></strong>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                        <!-- tombol logout -->
                        <li><a href="settingAcc.php?UserID=<?php echo $userID; ?>"
                                class="dropdown-item text-white">Setting</a></li>
                        <li><button class="dropdown-item text-danger fw-bold" data-bs-toggle="modal"
                                data-bs-target="#modalLogout">Sign out</button></li>
                    </ul>
                </div>
                <?php
            } else {
                // Jika pengguna belum login, tampilkan tautan login
                ?>
                <a href="login.php" class="d-flex align-items-center text-white text-decoration-none">
                    <strong>Login</strong>
                </a>
                <?php
            }
            ?>
        </div>

        <!-- modal logout -->
        <div class="modal fade" id="modalLogout" tabindex="-1" aria-labelledby="modalLogoutLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="width: 200px;">
                <div class="modal-content bg-dark">
                    <div class="modal-body">
                        <div class="w-100 d-flex justify-content-center align-items-center">
                            <i class="bi bi-door-closed text-center text-white" style="font-size: 6rem;"></i>
                        </div>
                        <div class="d-flex justify-content-center gap-3">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <a href="logout.php" class="btn btn-danger">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>




        <div class="container d-flex  mx-2">

            <div class="col-7">

                <div class="fs-3 fw-bolder text-white opacity-50 ms-3 mb-2">For Your Page</div>


                <?php foreach ($postingan as $image): ?>

                    <div
                        class="card cardPost border-top border-2 border-bottom-0  border-start-0 rounded-0 border-end-0 text-light border-secondary border-opacity-50 bg-transparent">
                        <div class="card-body my-2">
                            <div class="card-title d-flex justify-content-between align-items-center">
                                <h5 class="card-title fw-semibold"><img src="./assets/images/pp.jpg" class="rounded-circle"
                                        style="width: 3rem;"> <?php echo $image['NamaUser']; ?></h5>
                                <?php
                                if ($username === $image['NamaUser']) {
                                    echo '<div class="d-flex gap-2">
        <a href="index.php?action=delete&FotoID=' . $image['FotoID'] . '"
            onclick="return confirm(\'Apakah Anda yakin ingin menghapus foto ini?\')">
            <i class="bi bi-trash2 text-white fs-4"></i>
        </a>
        <a href="edit.php?FotoID=' . $image['FotoID'] . '">
        <i class="bi bi-pencil text-white fs-4"></i></a>
        
    </div>';
                                } ?>



                            </div>
                            <p class="card-text "><?php echo $image['DeskripsiFoto']; ?></p>
                            <a href="index.php?album=<?php echo $image['NamaAlbum']; ?>"
                                class="text-decoration-none card-text text-info">#<?php echo $image['NamaAlbum']; ?></a>
                            <img src="<?php echo $image['LokasiFile']; ?>" class="card-img mt-2" alt="...">

                            <div class="d-flex mt-3 gap-2">
                                <div class="ms-3" style="margin-top: 0.45rem;">
                                    <label for="checkboxHeart">
                                        <form method="POST" action="">
                                            <?php
                                            // Mendapatkan status like saat ini
                                            $fotoID = $image['FotoID'];
                                            $userID = $_SESSION['currentUser']['id'];
                                            $isLiked = $gallery->isLikedByUser($fotoID, $userID);

                                            // Mendapatkan jumlah suka
                                            $likeCount = $gallery->getLikeCount($fotoID);

                                            // Tentukan kelas dan ikon tombol berdasarkan status like
                                            $buttonClass = $isLiked ? 'btn liked' : 'btn';
                                            $buttonIcon = $isLiked ? 'bi-heart-fill' : 'bi-heart';
                                            ?>

                                            <input type="hidden" name="fotoID" value="<?php echo $fotoID ?>">
                                            <div class="d-flex align-items-center"><button type="submit" name="submit"
                                                    class="<?php echo $buttonClass; ?>">
                                                    <i class="bi <?php echo $buttonIcon; ?> text-white"></i>
                                                </button>
                                                <span class="like-count"><?php echo $likeCount; ?></span>
                                            </div>
                                        </form>
                                    </label>
                                </div>

                                <div class="w-100">
                                    <form action="index.php" method="POST" class="d-flex" id="commentForm">
                                        <div class="input-group flex-nowrap">
                                            <textarea
                                                class="form-control bg-transparent komentarPostingan border-0 rounded-0"
                                                placeholder="Leave a comment" aria-label="Komentar"
                                                name="komentar"></textarea>
                                            <input type="text" class="form-control d-none" name="fotoIDKomen"
                                                id="fotoIDKomen" value="<?php echo $image['FotoID']; ?>" required>
                                        </div>
                                        <button type="submit" value="submit" class="btn btn-dark rounded-start-0"
                                            id="submitComment"><i class="bi bi-send fs-5"></i></button>
                                    </form>
                                </div>
                            </div>

                            <div class="w-100">
                                <?php foreach ($image['KomentarFoto'] as $komentar): ?>

                                    <div
                                        class="card-body border-bottom bg-dark bg-opacity-50 border-opacity-25 pb-0 mt-2 border-secondary">
                                        <h5 class="card-title fw-semibold"><img src="./assets/images/pp.jpg"
                                                class="rounded-circle me-2"
                                                style="width: 2.4rem;"><?php echo $komentar['NamaUser']; ?></h5>
                                        <div class="d-flex justify-content-between mt-2">
                                            <p class="card-text"><?php echo $komentar['IsiKomentar']; ?></p>
                                            <p class="card-text"><?php echo $komentar['TanggalKomentar']; ?></p>
                                        </div>
                                        <!-- tambahkan informasi lain yang ingin Anda tampilkan di komentar -->
                                    </div>
                                <?php endforeach; ?>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>



            <div class="col-5">

                <div class="ms-4 mt-4 bg-dark bg-opacity-50 rounded-3 p-3">
                    <form action="index.php" method="post" enctype="multipart/form-data">
                        <p class="text-white fw-bold border-bottom fs-5">Make a New Post</p>
                        <div class="mb-3">
                            <input type="file" class="form-control" name="image" id="image" placeholder=""
                                accept="assets/image/*" required aria-describedby="fileHelpId" required />
                        </div>
                        <div class="mb-3">
                            <label for="DeskripsiFoto" class="form-label text-white fw-medium">Caption</label>
                            <textarea class="form-control" name="DeskripsiFoto" id="DeskripsiFoto"
                                placeholder="Add caption here" aria-describedby="helpId" required></textarea>
                        </div>
                        <div class="mb-3">
                            <?php
                            $selectOptions = $gallery->generateSelectOptions();
                            echo '<label for="" class="form-label text-white">Hashtag</label>
                                  <select class="form-select" name="AlbumID" id="AlbumID">
                                      ' . $selectOptions . '
                                  </select>';
                            ?>
                        </div>

                        <div class="mb-3">
                            <input type="date" class="form-control d-none" name="TanggalUnggah" id="TanggalUnggah"
                                aria-describedby="helpId" placeholder="" value="<?php echo date('Y-m-d'); ?>" />
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control d-none" name="UserID" id="UserID"
                                aria-describedby="helpId" placeholder="" value="<?php if (isset($_SESSION['currentUser'])) {
                                    // Jika pengguna sudah login, tampilkan nama pengguna
                                    echo $userID;
                                } else {
                                    echo "";
                                } ?>" required />
                        </div>
                        <button type="submit" class="btn btn-secondary">Upload</button>

                        <?php if (isset($error)): ?>
                            <?php echo '<p class="text-danger mb-0 mt-2">' . $error . '</p>'; ?>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="ms-4 mt-4 bg-dark bg-opacity-50 rounded-3 p-3">
                    <form action="index.php" method="post" enctype="multipart/form-data">
                        <p class="text-white fw-bold border-bottom fs-5">Make a New Hashtag</p>
                        <div class="mb-3">
                            <label for="NamaHashtag" class="form-label text-white fw-medium">Name of Hashtag</label>
                            <input type="text" name="NamaHashtag" id="NamaHashtag" class="form-control" placeholder=""
                                aria-describedby="helpId" required />
                        </div>
                        <div class="mb-3">
                            <label for="Deskripsi" class="form-label text-white fw-medium">Description</label>
                            <input type="text" name="Deskripsi" id="Deskripsi" class="form-control" placeholder=""
                                aria-describedby="helpId" required />
                        </div>
                        <div class="mb-3">
                            <input type="date" class="form-control d-none" name="TanggalDibuat" id="TanggalDibuat"
                                value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control d-none" name="UserID" id="UserID"
                                placeholder="User ID" value="<?php if (isset($_SESSION['currentUser'])) {
                                    // Jika pengguna sudah login, tampilkan nama pengguna
                                    echo $userID;
                                } else {
                                    echo "";
                                } ?>" required>
                        </div>
                        <button type="submit" class="btn btn-secondary">Add Hashtag</button>
                        <?php if (isset($error)): ?>
                            <?php echo '<p class="text-danger mb-0 mt-2">' . $error . '</p>'; ?>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="ms-4 mt-4 bg-dark bg-opacity-50 rounded-3 p-3">
                    <div class="container">
                        <div class="row">
                            <div class="col-12">
                                <h4 class="text-white">Jumlah Postingan dan User Yang Aktif</h4>
                                <canvas id="combinedChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>

    <script src="assets/js/script.js"></script>

    <script>
        $(document).ready(function () {
            $('.bi-heart').hover(
                function () {
                    $(this).addClass('text-danger');
                    $(this).removeClass('text-white');

                },
                function () {
                    $(this).removeClass('text-danger');
                    $(this).addClass('text-white');

                }
            );

            $('.bi-heart-fill').hover(
                function () {
                    $(this).addClass('text-danger');
                    $(this).removeClass('text-white');

                },
                function () {
                    $(this).removeClass('text-danger');
                    $(this).addClass('text-white');

                }
            );

            $('.komentarPostingan').hover(
                function () {
                    $(this).removeClass('border-0');
                    $(this).addClass('border-secondary border-1 border-opacity-50');
                },
                function () {
                    $(this).removeClass('border-secondary border-1 border-opacity-50');
                    $(this).addClass('border-0');
                }
            );

            $('.komentarPostingan').click(function () {
                if ($(this).hasClass('border-secondary border-1 border-opacity-50')) {
                    $(this).addClass('text-white');
                } else {
                    $(this).removeClass('text-white');
                }
            });
        });

        // Fungsi untuk menangani klik tombol Like
        function likePhoto(fotoID, userID) {
            var data = {
                fotoID: fotoID,
                userID: userID
            };

            $.ajax({
                url: "like.php",
                type: "POST",
                data: data,
                success: function (response) {
                    console.log(response); // Tampilkan respons dari server jika diperlukan
                    // Lakukan tindakan lain setelah mengirim permintaan like
                    // Misalnya, perbarui tampilan halaman atau jumlah suka
                },
                error: function (xhr, status, error) {
                    console.error(error); // Tampilkan pesan kesalahan jika terjadi
                }
            });
        }

        // Fungsi untuk menangani pengiriman komentar
        function submitComment(fotoID, userID, comment) {
            var data = {
                fotoID: fotoID,
                userID: userID,
                comment: comment
            };

            $.ajax({
                url: "comment.php",
                type: "POST",
                data: data,
                success: function (response) {
                    console.log(response); // Tampilkan respons dari server jika diperlukan
                    // Lakukan tindakan lain setelah mengirim komentar
                    // Misalnya, perbarui tampilan halaman dengan komentar baru
                },
                error: function (xhr, status, error) {
                    console.error(error); // Tampilkan pesan kesalahan jika terjadi
                }
            });
        }

        // Membuat diagram lingkaran untuk jumlah pengguna dan foto
        new Chart(document.getElementById("combinedChart"), {
            type: 'doughnut',
            data: {
                labels: ["User", "Postingan"],
                datasets: [{
                    data: [<?php echo $jumlahPengguna; ?>, <?php echo $jumlahFoto; ?>],
                    backgroundColor: ["#007bff", "#28a745"]
                }]
            },
            options: {
                responsive: true
            }
        });


    </script>

    <!-- js bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>

</body>

</html>