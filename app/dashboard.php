<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require_once '../includes/header.php';

// Placeholder data for dashboard summary (replace with actual data if available)
$today_check_ins = 5; // Example
$pending_bookings = 3; // Example
$total_revenue = 1500.00; // Example
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body class="lobby-bg">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card glass-card p-4 shadow-lg" style="max-width: 800px; width: 100%;">
            <div class="card-body text-center">
                <h2 class="card-title mb-2 text-navy">Hotel Management System</h2>
                <h4 class="card-subtitle mb-3 text-muted">Staff Dashboard</h4>
                <h5 class="text-white mb-4">Welcome, <?php echo htmlspecialchars($_SESSION['role']); ?>!</h5>
                <div class="dashboard-summary">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="card glass-card p-3">
                                <h6 class="text-white mb-2">Today's Check-ins</h6>
                                <p class="text-navy fs-4"><?php echo $today_check_ins; ?></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card glass-card p-3">
                                <h6 class="text-white mb-2">Pending Bookings</h6>
                                <p class="text-navy fs-4"><?php echo $pending_bookings; ?></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card glass-card p-3">
                                <h6 class="text-white mb-2">Total Revenue</h6>
                                <p class="text-navy fs-4">$<?php echo number_format($total_revenue, 2); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php require_once '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
</body>
</html>