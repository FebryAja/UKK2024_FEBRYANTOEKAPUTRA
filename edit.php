<!-- edit.php -->
<?php
require_once 'Gallery.php';

$gallery = new Gallery();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    // Ambil ID dari formulir
    $FotoID = $_POST['FotoID'];

    // Dapatkan informasi foto
    $image = $gallery->getImageInfo($FotoID);

    if (!$image) {
        echo "Foto tidak ditemukan.";
        exit();
    }

    // Proses edit foto
    $editResult = $gallery->editImage($FotoID, $_FILES['image'], $_POST['newCaption']);

    if ($editResult === true) {
        header("Location: index.php");
        exit();
    } else {
        $error = $editResult;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['FotoID'])) {
    // Ambil ID dari URL
    $FotoID = $_GET['FotoID'];

    // Dapatkan informasi foto
    $image = $gallery->getImageInfo($FotoID);

    if (!$image) {
        echo "Foto tidak ditemukan.";
        exit();
    }
} else {
    echo "ID foto tidak valid.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>edit foto</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- jquery -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"
        integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>

    <!-- ajax -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />


    <link rel="stylesheet" href="./assets/css/style.css">
</head>

<body class="bg-black">
    <div
        class="container position-fixed text-white w-50 bg-dark top-50 start-50 translate-middle py-3 rounded rounded-3">

        <h2>Edit The Post</h2>

        <form action="edit.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="FotoID" value="<?php echo $image['FotoID']; ?>">

            <div class="mb-3">
                <label for="newImage" class="form-label">Photo</label>
                <input class="form-control" type="file" name="image" id="newImage" accept="image/*">
            </div>

            <div class="mb-3">
                <label for="newCaption">Caption</label>
                <textarea class="form-control" name="newCaption" placeholder="Leave a comment here" id="newCaption"><?php echo $image['DeskripsiFoto']; ?></textarea>
            </div>


            <button class="btn btn-secondary" type="submit">Edit The Post</button>
        </form>
    </div>

    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
</body>

</html>