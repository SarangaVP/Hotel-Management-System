<?php
session_start();
if (!isset($_SESSION['guest_id'])) {
    header("Location: guest_login.php");
    exit;
}
require_once '../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS - Guest Dashboard</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <h1>Welcome, Guest!</h1>
    <div class="dashboard">
        <a href="guest_bookings.php">Book a Room</a>
        <a href="guest_profile.php">My Profile</a>
        <a href="guest_feedback.php">Submit Feedback</a>
        <a href="logout.php">Logout</a>
    </div>
</body>
<?php require_once '../includes/footer.php'; ?>
</html>