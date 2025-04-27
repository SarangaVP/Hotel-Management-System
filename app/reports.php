<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: index.php");
require_once '../includes/db_connect.php';
require_once '../includes/header.php';

// Default date range (last 30 days)
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : date('Y-m-d');

// Reformat dates for display in MM/DD/YYYY format
$start_date_display = (new DateTime($start_date))->format('m/d/Y');
$end_date_display = (new DateTime($end_date))->format('m/d/Y');

// Fetch occupancy data
$occupancy = $pdo->query("SELECT COUNT(*) as booked, (SELECT COUNT(*) FROM rooms) as total FROM rooms WHERE room_status = 'Booked'")->fetch();
$occupancy_percentage = $occupancy['total'] > 0 ? round(($occupancy['booked'] / $occupancy['total']) * 100) : 0;

// Fetch revenue data with date filter
$stmt = $pdo->prepare("SELECT SUM(total_amount) as total_revenue FROM payments WHERE payment_received = 'Yes' AND payment_date BETWEEN ? AND ?");
$stmt->execute([$start_date, $end_date]);
$revenue = $stmt->fetch();

// Fetch payment history with date filter
$stmt = $pdo->prepare("
    SELECT p.*, g.first_name, g.last_name, b.booking_id 
    FROM payments p 
    JOIN guests g ON p.guest_id = g.guest_id 
    JOIN bookings b ON p.booking_id = b.booking_id 
    WHERE p.payment_received = 'Yes' 
    AND p.payment_date BETWEEN ? AND ? 
    ORDER BY p.payment_date DESC, p.payment_time DESC
");
$stmt->execute([$start_date, $end_date]);
$payments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS - Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/forms.css">
</head>
<body>
    <main>
        <div class="container mt-5 pt-5">
            <div class="card standard-card p-4 mb-4">
                <h1 class="text-center mb-4"><b>Reports and Analytics</b></h1>

                <!-- Date Range Filter for Revenue -->
                <div class="mb-4">
                    <h5 class="mb-3 mt-3"><b>Filter Revenue by Date Range</b></h5>
                    <form method="POST" class="date-filter-form">
                        <div class="form-group">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control form-control-lg date-input" value="<?php echo htmlspecialchars($start_date); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control form-control-lg date-input" value="<?php echo htmlspecialchars($end_date); ?>" required>
                        </div>
                        <div class="form-group form-group-button">
                            <button type="submit" class="btn btn-primary btn-navy btn-lg filter-btn">Filter</button>
                        </div>
                    </form>
                </div>

                <!-- Revenue Report -->
                <div class="mb-4">
                    <h4 class="mb-3"><b>Revenue Report</b></h4>
                    <div class="card standard-card p-3">
                        <p>Total Revenue (<?php echo htmlspecialchars($start_date_display); ?> to <?php echo htmlspecialchars($end_date_display); ?>): Rs <?php echo htmlspecialchars(number_format($revenue['total_revenue'] ?: 0, 2)); ?></p>
                    </div>
                </div>

                <!-- Payment History -->
                <div class="mb-4">
                    <h4 class="mb-3"><b>Payment History</b></h4>
                    <div class="card standard-card p-3">
                        <div class="table-responsive">
                            <table class="table table-standard">
                                <thead>
                                    <tr>
                                        <th>Payment ID</th>
                                        <th>Guest</th>
                                        <th>Booking ID</th>
                                        <th>Amount (Rs)</th>
                                        <th>Payment Date & Time</th>
                                        <th>Payment Method</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($payments)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No payments found for the selected date range.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($payments as $payment): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($payment['payment_id']); ?></td>
                                                <td><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($payment['booking_id']); ?></td>
                                                <td><?php echo htmlspecialchars(number_format($payment['total_amount'], 2)); ?></td>
                                                <td><?php echo htmlspecialchars($payment['payment_date'] . ' ' . $payment['payment_time']); ?></td>
                                                <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Occupancy Report -->
                <div class="mb-4">
                    <h4 class="mb-3"><b>Occupancy Report</b></h4>
                    <div class="card standard-card p-3">
                        <p class="mb-2">Booked Rooms: <?php echo htmlspecialchars($occupancy['booked']); ?> / <?php echo htmlspecialchars($occupancy['total']); ?></p>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-navy" role="progressbar" style="width: <?php echo $occupancy_percentage; ?>%;" aria-valuenow="<?php echo $occupancy_percentage; ?>" aria-valuemin="0" aria-valuemax="100">
                                <?php echo $occupancy_percentage; ?>%
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
    <?php require_once '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
</body>
</html>