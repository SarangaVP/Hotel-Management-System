<?php
session_start();
if (isset($_SESSION['user_id']) || isset($_SESSION['guest_id'])) {
    header("Location: dashboard.php"); // Redirects to appropriate dashboard
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
    <title>HMS - Login</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/forms.css">
</head>
<body>
    <div class="login-container">
        <h2>Hotel Management System</h2>
        <h3>Staff Login</h3>
        <?php if (isset($staff_error)) echo "<p class='error'>$staff_error</p>"; ?>
        <form method="POST">
            <label>Email:</label>
            <input type="email" name="email" required>
            <label>Password:</label>
            <input type="password" name="password" required>
            <button type="submit" name="staff_login">Staff Login</button>
        </form>
        <p><a href="guest_login.php">Guest Login</a> | <a href="guest_register.php">Register as Guest</a></p>
    </div>
</body>
</html>