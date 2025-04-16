<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: index.php");
require_once '../includes/db_connect.php';
require_once '../includes/header.php';

// Initialize alert message
$alert_message = '';
$alert_type = '';

// Handle Generate Invoice
if (isset($_POST['generate_invoice'])) {
    $booking_id = $_POST['booking_id'];
    try {
        // Check if an invoice already exists for this booking
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM invoices WHERE booking_id = ?");
        $stmt->execute([$booking_id]);
        if ($stmt->fetchColumn() > 0) {
            $alert_message = "Error: An invoice already exists for this booking.";
            $alert_type = "danger";
        } else {
            // Calculate total amount due
            $stmt = $pdo->prepare("SELECT price_per_night FROM rooms WHERE room_id = (SELECT room_id FROM bookings WHERE booking_id = ?)");
            $stmt->execute([$booking_id]);
            $price = $stmt->fetchColumn();
            $days = $pdo->query("SELECT DATEDIFF(checkout_date, checkin_date) FROM bookings WHERE booking_id = $booking_id")->fetchColumn();
            $total = $price * $days;

            // Insert invoice
            $stmt = $pdo->prepare("INSERT INTO invoices (booking_id, guest_id, invoice_date, invoice_time, total_amount_due, amount_paid, balance_due, payment_status) VALUES (?, (SELECT guest_id FROM bookings WHERE booking_id = ?), CURDATE(), CURTIME(), ?, 0, ?, 'Unpaid')");
            $stmt->execute([$booking_id, $booking_id, $total, $total]);
            $alert_message = "Invoice generated successfully!";
            $alert_type = "success";
        }
    } catch (PDOException $e) {
        $alert_message = "Error generating invoice: " . $e->getMessage();
        $alert_type = "danger";
    }
}

// Fetch data
$bookings = $pdo->query("SELECT b.*, g.first_name, g.last_name, r.room_number FROM bookings b JOIN guests g ON b.guest_id = g.guest_id JOIN rooms r ON b.room_id = r.room_id WHERE b.booking_status = 'Completed'")->fetchAll();
$invoices = $pdo->query("SELECT i.*, b.booking_id, g.first_name, g.last_name FROM invoices i JOIN bookings b ON i.booking_id = b.booking_id JOIN guests g ON i.guest_id = g.guest_id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS - Billing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/forms.css">
</head>
<body>
    <main>
        <div class="container mt-5 pt-5">
            <h1 class="text-center mb-4">Billing and Payments</h1>

            <!-- Alert Message -->
            <?php if ($alert_message): ?>
                <div class="alert alert-<?php echo htmlspecialchars($alert_type); ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($alert_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Section 1: Generate Invoice -->
            <div class="card standard-card p-4 mb-4">
                <h4 class="mb-3 text-center">Generate Invoice</h4>
                <div class="table-responsive">
                    <table class="table table-standard">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Guest</th>
                                <th>Room</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($bookings)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No completed bookings found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['room_number']); ?></td>
                                        <td>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking['booking_id']); ?>">
                                                <button type="submit" name="generate_invoice" class="btn btn-primary btn-sm" onclick="return confirm('Are you sure you want to generate an invoice for this booking?');">Generate</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Section 2: Invoices -->
            <div class="card standard-card p-4 mb-4">
                <h4 class="mb-3 text-center">Invoices</h4>
                <div class="table-responsive">
                    <table class="table table-standard">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Booking</th>
                                <th>Guest</th>
                                <th>Invoice Date</th>
                                <th>Total Due</th>
                                <th>Paid</th>
                                <th>Balance</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($invoices)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">No invoices found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($invoices as $invoice): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($invoice['invoice_id']); ?></td>
                                        <td><?php echo htmlspecialchars($invoice['booking_id']); ?></td>
                                        <td><?php echo htmlspecialchars($invoice['first_name'] . ' ' . $invoice['last_name']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($invoice['invoice_date'] . ' ' . $invoice['invoice_time']); ?>
                                        </td>
                                        <td><?php echo '$' . number_format($invoice['total_amount_due'], 2); ?></td>
                                        <td><?php echo '$' . number_format($invoice['amount_paid'], 2); ?></td>
                                        <td><?php echo '$' . number_format($invoice['balance_due'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($invoice['payment_status']); ?></td>
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