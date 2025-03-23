<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: index.php");
require_once '../includes/db_connect.php';
require_once '../includes/header.php';

$occupancy = $pdo->query("SELECT COUNT(*) as booked, (SELECT COUNT(*) FROM rooms) as total FROM rooms WHERE room_status = 'Booked'")->fetch();
$revenue = $pdo->query("SELECT SUM(total_amount) as total_revenue FROM payments WHERE payment_received = 'Yes'")->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS - Reports</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/forms.css">
</head>
<body>
    <h1>Reports and Analytics</h1>
    <div class="report-container">
        <h2>Occupancy Report</h2>
        <p>Booked Rooms: <?php echo $occupancy['booked']; ?> / <?php echo $occupancy['total']; ?></p>
        <h2>Revenue Report</h2>
        <p>Total Revenue: $<?php echo $revenue['total_revenue'] ?: 0; ?></p>
    </div>
</body>
<?php require_once '../includes/footer.php'; ?>
</html>