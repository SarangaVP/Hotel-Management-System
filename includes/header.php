<?php
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
                    <?php if (isset($_SESSION['user_id'])) { ?>
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
                    <?php } elseif (isset($_SESSION['guest_id'])) { ?>
                        <li class="nav-item">
                            <a class="nav-link nav-text" href="guest_dashboard.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-text" href="guest_bookings.php">Bookings</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-text" href="guest_profile.php">Profile</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-text" href="guest_payments.php">Payments</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-text" href="guest_feedback.php">Feedback</a>
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

<style>
    .navbar-nav {
        display: flex;
        gap: 1.5rem;
    }

    .navbar-nav .nav-item {
        position: relative;
    }

    .navbar-nav .nav-item:not(:last-child)::after {
        content: '|';
        color: #d3d3d3;
        position: absolute;
        right: -0.75rem;
        top: 50%;
        transform: translateY(-50%);
    }

    @media (max-width: 991.98px) {
        .navbar-nav {
            flex-direction: column;
            gap: 0.5rem;
        }
        .navbar-nav .nav-item:not(:last-child)::after {
            content: none;
        }
    }
</style>