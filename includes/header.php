<?php
// Ensure session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<header class="solid-header">
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand logo-highlight" href="<?php echo isset($_SESSION['user_id']) ? 'dashboard.php' : (isset($_SESSION['guest_id']) ? 'guest_dashboard.php' : '../index.php'); ?>">HMS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['user_id'])) { // Staff navigation ?>
                        <li class="nav-item">
                            <a class="nav-link nav-text" href="dashboard.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-text" href="rooms.php">Rooms</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-text" href="bookings.php">Bookings</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-text" href="checkin_checkout.php">Check-in/Out</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-text" href="billing.php">Billing</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-text" href="customers.php">Customers</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-text" href="feedback.php">Feedback</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-text" href="reports.php">Reports</a>
                        </li>
                        <?php if ($_SESSION['role'] == 'Administrator') { ?>
                            <li class="nav-item">
                                <a class="nav-link nav-text" href="users.php">Users</a>
                            </li>
                        <?php } ?>
                    <?php } elseif (isset($_SESSION['guest_id'])) { // Guest navigation ?>
                        <li class="nav-item">
                            <a class="nav-link nav-text" href="guest_dashboard.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-text" href="guest_bookings.php">Book a Room</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-text" href="guest_profile.php">My Profile</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-text" href="guest_feedback.php">Submit Feedback</a>
                        </li>
                    <?php } ?>
                    <li class="nav-item">
                        <a class="nav-link nav-text" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>