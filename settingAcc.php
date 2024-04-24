<!-- edit.php -->
<?php
require_once 'Gallery.php';

$gallery = new Gallery();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    // Ambil ID dari formulir
    $UserID = $_POST['UserID'];

    // Dapatkan informasi foto
    $User = $gallery->getUserInfo($UserID);

    if (!$User) {
        echo "User tidak ditemukan.";
        exit();
    }

    // Proses edit foto
    $editResult = $gallery->editUser($UserID, $_POST['username'], $_POST['password']);

    if ($editResult === true) {
        header("Location: index.php");
        exit();
    } else {
        $error = $editResult;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['UserID'])) {
    // Ambil ID dari URL
    $UserID = $_GET['UserID'];

    // Dapatkan informasi foto
    $User = $gallery->getUserInfo($UserID);

    if (!$User) {
        echo "User tidak ditemukan.";
        exit();
    }
} else {
    echo "ID User tidak valid.";
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

        <h2>Edit User</h2>

        <form action="settingAcc.php" method="post">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="UserID" value="<?php echo $User['UserID']; ?>">

            <div class="mb-3">
                <label for="Username" class="form-label">Username</label>
                <input class="form-control" type="text" name="username" id="Username" value="<?php echo $User['Username']; ?>">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input class="form-control" type="password" name="password" id="password">
            </div>

            <button class="btn btn-secondary" type="submit">Edit User</button>
        </form>
    </div>

    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
</body>

</html>