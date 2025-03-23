<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require_once '../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS - Dashboard</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <h1>Welcome, <?php echo $_SESSION['role']; ?>!</h1>
    <div class="dashboard">
        <a href="rooms.php">Room Management</a>
        <a href="bookings.php">Booking Management</a>
        <a href="checkin_checkout.php">Check-in/Check-out</a>
        <a href="billing.php">Billing</a>
        <a href="customers.php">Customers</a>
        <a href="feedback.php">Feedback</a>
        <a href="reports.php">Reports</a>
        <?php if ($_SESSION['role'] == 'Administrator') { ?>
            <a href="users.php">User Management</a>
        <?php } ?>
        <a href="logout.php">Logout</a>
    </div>
</body>
<?php require_once '../includes/footer.php'; ?>
</html>