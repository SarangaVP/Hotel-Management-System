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
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM invoices WHERE booking_id = ?");
        $stmt->execute([$booking_id]);
        if ($stmt->fetchColumn() > 0) {
            $alert_message = "Error: An invoice already exists for this booking.";
            $alert_type = "danger";
        } else {
            $stmt = $pdo->prepare("SELECT price_per_night FROM rooms WHERE room_id = (SELECT room_id FROM bookings WHERE booking_id = ?)");
            $stmt->execute([$booking_id]);
            $price = $stmt->fetchColumn();
            $days = $pdo->query("SELECT DATEDIFF(checkout_date, checkin_date) FROM bookings WHERE booking_id = $booking_id")->fetchColumn();
            $total = $price * $days;

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

// Handle Record Payment
if (isset($_POST['record_payment'])) {
    $invoice_id = $_POST['invoice_id'];
    $booking_id = $_POST['booking_id'];
    $guest_id = $_POST['guest_id'];
    $payment_received = (float)$_POST['payment_received'];
    $payment_method = $_POST['payment_method'];
    $discount_applied = (float)$_POST['discount_applied'];
    $total_amount_due = (float)$_POST['total_amount_due'];
    $current_amount_paid = (float)$_POST['current_amount_paid'];

    // Validate inputs
    if ($payment_received <= 0) {
        $alert_message = "Error: Payment amount must be greater than 0.";
        $alert_type = "danger";
    } elseif (empty($payment_method)) {
        $alert_message = "Error: Please select a payment method.";
        $alert_type = "danger";
    } elseif ($discount_applied < 0) {
        $alert_message = "Error: Discount cannot be negative.";
        $alert_type = "danger";
    } else {
        try {
            // Calculate new amounts
            $total_after_discount = $total_amount_due - $discount_applied;
            $new_amount_paid = $current_amount_paid + $payment_received;
            $balance_due = $total_after_discount - $new_amount_paid;
            $refund_processed = 0;

            // Handle overpayment as refund
            if ($balance_due < 0) {
                $refund_processed = abs($balance_due);
                $balance_due = 0;
                $new_amount_paid = $total_after_discount;
            }

            // Update invoice
            $payment_status = $balance_due == 0 ? 'Paid' : 'Unpaid';
            $stmt = $pdo->prepare("UPDATE invoices SET amount_paid = ?, balance_due = ?, payment_status = ? WHERE invoice_id = ?");
            $stmt->execute([$new_amount_paid, $balance_due, $payment_status, $invoice_id]);

            // Record payment
            $stmt = $pdo->prepare("INSERT INTO payments (booking_id, guest_id, payment_received, payment_date, payment_time, payment_method, total_amount, discount_applied, refund_processed) VALUES (?, ?, ?, CURDATE(), CURTIME(), ?, ?, ?, ?)");
            $stmt->execute([$booking_id, $guest_id, $payment_received, $payment_method, $total_after_discount, $discount_applied, $refund_processed]);

            $alert_message = "Payment recorded successfully!" . ($refund_processed > 0 ? " A refund of $" . number_format($refund_processed, 2) . " was processed." : "");
            $alert_type = "success";
        } catch (PDOException $e) {
            $alert_message = "Error recording payment: " . $e->getMessage();
            $alert_type = "danger";
        }
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
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($invoices)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">No invoices found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($invoices as $invoice): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($invoice['invoice_id']); ?></td>
                                        <td><?php echo htmlspecialchars($invoice['booking_id']); ?></td>
                                        <td><?php echo htmlspecialchars($invoice['first_name'] . ' ' . $invoice['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($invoice['invoice_date'] . ' ' . $invoice['invoice_time']); ?></td>
                                        <td><?php echo '$' . number_format($invoice['total_amount_due'], 2); ?></td>
                                        <td><?php echo '$' . number_format($invoice['amount_paid'], 2); ?></td>
                                        <td><?php echo '$' . number_format($invoice['balance_due'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($invoice['payment_status']); ?></td>
                                        <td>
                                            <?php if ($invoice['balance_due'] > 0): ?>
                                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#paymentModal<?php echo $invoice['invoice_id']; ?>">Pay</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                    <!-- Payment Modal -->
                                    <div class="modal fade" id="paymentModal<?php echo $invoice['invoice_id']; ?>" tabindex="-1" aria-labelledby="paymentModalLabel<?php echo $invoice['invoice_id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="paymentModalLabel<?php echo $invoice['invoice_id']; ?>">Record Payment for Invoice #<?php echo $invoice['invoice_id']; ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form method="POST" class="date-filter-form">
                                                        <input type="hidden" name="invoice_id" value="<?php echo htmlspecialchars($invoice['invoice_id']); ?>">
                                                        <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($invoice['booking_id']); ?>">
                                                        <input type="hidden" name="guest_id" value="<?php echo htmlspecialchars($invoice['guest_id']); ?>">
                                                        <input type="hidden" name="total_amount_due" value="<?php echo htmlspecialchars($invoice['total_amount_due']); ?>">
                                                        <input type="hidden" name="current_amount_paid" value="<?php echo htmlspecialchars($invoice['amount_paid']); ?>">
                                                        <div class="form-group">
                                                            <label for="payment_received_<?php echo $invoice['invoice_id']; ?>" class="form-label">Amount Paid</label>
                                                            <input type="number" step="0.01" name="payment_received" id="payment_received_<?php echo $invoice['invoice_id']; ?>" class="form-control form-control-lg" placeholder="Enter amount" min="0.01" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="payment_method_<?php echo $invoice['invoice_id']; ?>" class="form-label">Payment Method</label>
                                                            <select name="payment_method" id="payment_method_<?php echo $invoice['invoice_id']; ?>" class="form-control form-control-lg" required>
                                                                <option value="" disabled selected>Select payment method</option>
                                                                <option value="Cash">Cash</option>
                                                                <option value="Credit Card">Credit Card</option>
                                                                <option value="Bank Transfer">Bank Transfer</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="discount_applied_<?php echo $invoice['invoice_id']; ?>" class="form-label">Discount Applied</label>
                                                            <input type="number" step="0.01" name="discount_applied" id="discount_applied_<?php echo $invoice['invoice_id']; ?>" class="form-control form-control-lg" placeholder="Enter discount (if any)" value="0" min="0" required>
                                                        </div>
                                                        <div class="form-group form-group-button mt-3">
                                                            <button type="submit" name="record_payment" class="btn btn-success btn-lg filter-btn">Record Payment</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
    <script>
        // Client-side validation for payment form
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function (e) {
                const paymentReceived = parseFloat(form.querySelector('[name="payment_received"]').value);
                const paymentMethod = form.querySelector('[name="payment_method"]').value;
                const discountApplied = parseFloat(form.querySelector('[name="discount_applied"]').value);

                if (paymentReceived <= 0) {
                    e.preventDefault();
                    alert('Payment amount must be greater than 0.');
                    return;
                }

                if (!paymentMethod) {
                    e.preventDefault();
                    alert('Please select a payment method.');
                    return;
                }

                if (discountApplied < 0) {
                    e.preventDefault();
                    alert('Discount cannot be negative.');
                }
            });
        });
    </script>
</body>
</html>