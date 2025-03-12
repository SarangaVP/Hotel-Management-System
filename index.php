<?php
session_start();
?>

<?php include_once 'views/includes/header.php'; ?>

<div class="container" style="margin-top: 50px; text-align: center;">
    <h1>Welcome to the Hotel Management System</h1>
    <p>Manage your hotel bookings</p>

    <?php if (isset($_SESSION['username'])): ?>
        <p>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?> 👋</p>
        <a href="views/dashboard.php" class="btn">Go to Dashboard</a><br><br>
        <a href="logout.php" class="btn">Logout</a>
    <?php else: ?>
        <a href="views/auth/login.php" class="btn">Login</a><br><br>
        <a href="views/auth/register.php" class="btn">Register</a>
    <?php endif; ?>
</div>

<?php include_once 'views/includes/footer.php'; ?>
