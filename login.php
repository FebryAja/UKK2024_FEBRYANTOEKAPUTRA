<?php
session_start();

if (isset($_SESSION['error'])) {
    $errorMessage = $_SESSION['error'];
    unset($_SESSION['error']);
} else {
    $errorMessage = '';
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

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

        <h2>Login</h2>
        <form method="POST" action="proses_login.php">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" class="form-control text-white bg-transparent mt-2" id="username" name="username"
                    required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control text-white bg-transparent mt-2" id="password" name="password"
                    required>
            </div>
            <?php
            if (!empty($errorMessage)) {
                echo '<p class="text-danger mt-2 mb-0">' . $errorMessage . '</p>';
            }
            ?>
            <button type="submit" class="btn btn-secondary w-100 mt-2 ">Login</button>
            <p class="ms-2">Dont have an account? <a href="register.php">Buat akun di sini</a></p>

        </form>
    </div>
</body>

</html>