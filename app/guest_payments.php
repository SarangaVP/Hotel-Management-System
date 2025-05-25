<?php
session_start();
if (!isset($_SESSION['guest_id'])) header("Location: guest_login.php");
require_once '../includes/db_connect.php';
require_once '../includes/header.php';
// Initialize alert message
$alert_message = '';
$alert_type = '';

$guest_id = $_SESSION['guest_id'];

// Fetch previously recorded payments
$previous_payments = $pdo->query("
    SELECT p.*, r.room_number
    FROM payments p
    JOIN bookings b ON p.booking_id = b.booking_id
    JOIN rooms r ON b.room_id = r.room_id
    WHERE p.guest_id = $guest_id
    ORDER BY p.payment_date DESC, p.payment_time DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS - Guest Payments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/forms.css">
</head>
<body>
    <main>
        <div class="container mt-5 pt-5">
            <h1 class="text-center mb-4"><b>Payments</b></h1>

            <?php if ($alert_message): ?>
                <div class="alert alert-<?php echo htmlspecialchars($alert_type); ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($alert_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card standard-card p-4 mb-4">
                <h4 class="mb-3 text-center"><b>My Payments</b></h4>
                <div class="table-responsive">
                    <table class="table table-standard">
                        <thead>
                            <tr>
                                <th>Payment ID</th>
                                <th>Booking ID</th>
                                <th>Room</th>
                                <th>Payment Date</th>
                                <th>Payment Time</th>
                                <th>Payment Method</th>
                                <th>Amount (Rs)</th>
                                <th>Discount Applied</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($previous_payments)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">No payments recorded yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($previous_payments as $payment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($payment['payment_id']); ?></td>
                                        <td><?php echo htmlspecialchars($payment['booking_id']); ?></td>
                                        <td><?php echo htmlspecialchars($payment['room_number']); ?></td>
                                        <td><?php echo htmlspecialchars($payment['payment_date']); ?></td>
                                        <td><?php echo htmlspecialchars($payment['payment_time']); ?></td>
                                        <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                                        <td><?php echo number_format($payment['total_amount'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($payment['discount_applied']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <?php require_once '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
</body>
</html>