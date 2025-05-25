<?php
session_start();
if (isset($_SESSION['guest_id'])) {
    header("Location: guest_dashboard.php");
    exit;
}
require_once '../includes/db_connect.php';

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $phone = $_POST['phone'] ?? null;

    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM guests WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Email already registered! Please use a different email.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO guests (first_name, last_name, phone_number, email, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$first_name, $last_name, $phone, $email, $password]);
            $success = "Registration successful! Redirecting to login...";
            header("Refresh: 3; URL=guest_login.php");
        }
    } catch (PDOException $e) {
        $error = "Error during registration: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS - Guest Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/forms.css">
</head>
<body class="lobby-bg">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card glass-card p-3 shadow-lg" style="max-width: 450px; width: 100%;">
            <div class="card-body text-center">
                <h2 class="card-title mb-2 text-navy"><b>Hotel Management System</b></h2>
                <h4 class="card-subtitle mb-3 text-muted"><b>Guest Registration</b></h4>
                <?php if ($success) { ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php } elseif ($error) { ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php } ?>
                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-2">
                        <label for="first_name" class="form-label text-navy">First Name</label>
                        <input type="text" class="form-control form-control-lg" id="first_name" name="first_name" required>
                        <div class="invalid-feedback">Please enter your first name.</div>
                    </div>
                    <div class="mb-2">
                        <label for="last_name" class="form-label text-navy">Last Name</label>
                        <input type="text" class="form-control form-control-lg" id="last_name" name="last_name" required>
                        <div class="invalid-feedback">Please enter your last name.</div>
                    </div>
                    <div class="mb-2">
                        <label for="email" class="form-label text-navy">Email</label>
                        <input type="email" class="form-control form-control-lg" id="email" name="email" required>
                        <div class="invalid-feedback">Please enter a valid email.</div>
                    </div>
                    <div class="mb-2">
                        <label for="phone" class="form-label text-navy">Phone (Optional)</label>
                        <input type="text" class="form-control form-control-lg" id="phone" name="phone">
                    </div>
                    <div class="mb-2">
                        <label for="password" class="form-label text-navy">Password</label>
                        <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                        <div class="invalid-feedback">Please enter your password.</div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-gold w-100">Register</button>
                </form>
                <div class="mt-3">
                    <p class="text-white mb-2">Already have an account?</p>
                    <a href="guest_login.php" class="btn btn-outline-primary btn-outline-navy btn-sm">Login here</a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
    <script>
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