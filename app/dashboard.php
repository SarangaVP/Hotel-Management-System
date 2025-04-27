<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require_once '../includes/db_connect.php'; // Added to ensure database connection
require_once '../includes/header.php';

// Fetch real data for dashboard summary
try {
    // Count available rooms
    $stmt = $pdo->query("SELECT COUNT(*) FROM rooms WHERE room_status = 'Available'");
    $available_rooms = $stmt->fetchColumn();

    // Count booked rooms
    $stmt = $pdo->query("SELECT COUNT(*) FROM rooms WHERE room_status = 'Booked'");
    $booked_rooms = $stmt->fetchColumn();

    // Count total feedbacks
    $stmt = $pdo->query("SELECT COUNT(*) FROM feedback");
    $total_feedbacks = $stmt->fetchColumn();
} catch (PDOException $e) {
    // Handle error gracefully (you can customize this as needed)
    $available_rooms = 0;
    $booked_rooms = 0;
    $total_feedbacks = 0;
    echo '<div class="alert alert-danger text-center">Error fetching dashboard data: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
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
                <h2 class="card-title mb-4 text-navy"><b>Hotel Management System</b></h2>
                <h3 class="text-muted mb-5"><b>Welcome, <?php echo htmlspecialchars($_SESSION['role']); ?>!</b></h3>
                <div class="dashboard-summary">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="card glass-card p-3">
                                <h6 class="text-muted mb-2"><b>Available Rooms</b></h6>
                                <p class="text-muted fs-4"><?php echo $available_rooms; ?></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card glass-card p-3">
                                <h6 class="text-muted mb-2"><b>Booked Rooms</b></h6>
                                <p class="text-muted fs-4"><?php echo $booked_rooms; ?></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card glass-card p-3">
                                <h6 class="text-muted mb-2"><b>Total Feedbacks</b></h6>
                                <p class="text-muted fs-4"><?php echo $total_feedbacks; ?></p>
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