<?php
session_start();
if (isset($_SESSION['guest_id'])) {
    header("Location: guest_dashboard.php");
    exit;
}
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $phone = $_POST['phone'];
    
    $stmt = $pdo->prepare("INSERT INTO guests (first_name, last_name, phone_number, email, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$first_name, $last_name, $phone, $email, $password]);
    header("Location: guest_login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS - Guest Registration</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/forms.css">
</head>
<body>
    <div class="login-container">
        <h2>Guest Registration</h2>
        <form method="POST">
            <label>First Name:</label>
            <input type="text" name="first_name" required>
            <label>Last Name:</label>
            <input type="text" name="last_name" required>
            <label>Email:</label>
            <input type="email" name="email" required>
            <label>Phone:</label>
            <input type="text" name="phone">
            <label>Password:</label>
            <input type="password" name="password" required>
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="guest_login.php">Login here</a></p>
    </div>
</body>
</html>