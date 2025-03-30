<?php
session_start();
if (isset($_SESSION['guest_id'])) {
    header("Location: guest_dashboard.php");
    exit;
}
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $stmt = $pdo->prepare("SELECT * FROM guests WHERE email = ? AND password = ?");
    $stmt->execute([$email, $password]);
    $guest = $stmt->fetch();
    if ($guest) {
        $_SESSION['guest_id'] = $guest['guest_id'];
        header("Location: guest_dashboard.php");
    } else {
        $error = "Invalid guest credentials!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS - Guest Login</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/forms.css">
</head>
<body>
    <div class="login-container">
        <h2>Guest Login</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <label>Email:</label>
            <input type="email" name="email" required>
            <label>Password:</label>
            <input type="password" name="password" required>
            <button type="submit">Login</button>
        </form>
        <p>New user? <a href="guest_register.php">Register here</a></p>
        <p><a href="index.php">Staff Login</a></p>
    </div>
</body>
</html>