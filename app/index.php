<?php
session_start();
if (isset($_SESSION['user_id']) || isset($_SESSION['guest_id'])) {
    header("Location: dashboard.php");
    exit;
}
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['staff_login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $stmt = $pdo->prepare("SELECT * FROM staff WHERE email = ? AND password = ?");
    $stmt->execute([$email, $password]);
    $user = $stmt->fetch();
    if ($user) {
        $_SESSION['user_id'] = $user['staff_id'];
        $_SESSION['role'] = $user['role'];
        header("Location: dashboard.php");
    } else {
        $staff_error = "Invalid staff credentials!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS - Staff Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css?v=<?php echo time(); ?>">
</head>
<body class="lobby-bg">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card glass-card p-4 shadow-lg" style="max-width: 450px; width: 100%;">
            <div class="card-body text-center">
                <h2 class="card-title mb-3 text-navy"><b>Hotel Management System</b></h2>
                <h4 class="card-subtitle mb-4 text-muted"><b>Staff Login</b></h4>
                <?php if (isset($staff_error)) { ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $staff_error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php } ?>
                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="email" class="form-label text-navy">Email</label>
                        <input type="email" class="form-control form-control-lg" id="email" name="email" required>
                        <div class="invalid-feedback">Please enter a valid email.</div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label text-navy">Password</label>
                        <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                        <div class="invalid-feedback">Please enter your password.</div>
                    </div>
                    <button type="submit" name="staff_login" class="btn btn-primary btn-gold w-100">Login</button>
                </form>
                <div class="mt-4 d-flex justify-content-center gap-3">
                    <a href="guest_login.php" class="btn btn-outline-primary btn-outline-navy btn-sm">Guest Login</a>
                    <a href="guest_register.php" class="btn btn-outline-primary btn-outline-navy btn-sm">Register as Guest</a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
    <script>
        // Bootstrap form validation
        (function () {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html>