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

            $stmt = $pdo->prepare("INSERT INTO invoices (booking_id, guest_id, invoice_date, invoice_time, total_amount_due, amount_paid, balance_due, payment_status, discount) VALUES (?, (SELECT guest_id FROM bookings WHERE booking_id = ?), CURDATE(), CURTIME(), ?, 0, ?, 'Unpaid', 0)");
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
    $payment_amount = (float)$_POST['payment_received'];
    $payment_method = $_POST['payment_method'];
    $discount_amount = (float)$_POST['discount_applied'];
    $total_amount_due = (float)$_POST['total_amount_due'];
    $current_amount_paid = (float)$_POST['current_amount_paid'];
    $current_discount = isset($_POST['current_discount']) ? (float)$_POST['current_discount'] : 0;

    // Validate inputs
    if ($payment_amount <= 0) {
        $alert_message = "Error: Payment amount must be greater than 0.";
        $alert_type = "danger";
    } elseif (empty($payment_method)) {
        $alert_message = "Error: Please select a payment method.";
        $alert_type = "danger";
    } elseif ($discount_amount < 0) {
        $alert_message = "Error: Discount cannot be negative.";
        $alert_type = "danger";
    } else {
        try {
            // Calculate new amounts
            $new_discount = $current_discount + $discount_amount;
            $new_total_amount_due = $total_amount_due - $discount_amount;
            $new_amount_paid = $current_amount_paid + $payment_amount;
            $balance_due = $new_total_amount_due - $new_amount_paid;

            // Handle overpayment
            $overpayment = 0;
            if ($balance_due < 0) {
                $overpayment = abs($balance_due);
                $balance_due = 0;
                $new_amount_paid = $new_total_amount_due;
            }

            // Update invoice
            $payment_status = $balance_due == 0 ? 'Paid' : ($new_amount_paid > 0 ? 'Partially Paid' : 'Unpaid');
            $stmt = $pdo->prepare("UPDATE invoices SET total_amount_due = ?, amount_paid = ?, balance_due = ?, payment_status = ?, discount = ? WHERE invoice_id = ?");
            $stmt->execute([$new_total_amount_due, $new_amount_paid, $balance_due, $payment_status, $new_discount, $invoice_id]);

            // Record payment
            $discount_applied = $discount_amount > 0 ? 'Yes' : 'No';
            $stmt = $pdo->prepare("INSERT INTO payments (booking_id, guest_id, payment_received, payment_date, payment_time, payment_method, total_amount, discount_applied) VALUES (?, ?, 'Yes', CURDATE(), CURTIME(), ?, ?, ?)");
            $stmt->execute([$booking_id, $guest_id, $payment_method, $payment_amount, $discount_applied]);

            $alert_message = "Payment recorded successfully!" . ($overpayment > 0 ? " An overpayment of $" . number_format($overpayment, 2) . " was noted." : "");
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
            <h1 class="text-center mb-4"><b>Billing and Payments</b></h1>

            <!-- Alert Message -->
            <?php if ($alert_message): ?>
                <div class="alert alert-<?php echo htmlspecialchars($alert_type); ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($alert_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Section 1: Generate Invoice -->
            <div class="card standard-card p-4 mb-4">
                <h4 class="mb-3 text-center"><b>Generate Invoice</b></h4>
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
                                            <button type="button" class="btn btn-primary btn-navy btn-sm" data-bs-toggle="modal" data-bs-target="#confirmModal" data-booking-id="<?php echo htmlspecialchars($booking['booking_id']); ?>">Generate</button>
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
                <h4 class="mb-3 text-center"><b>Invoices</b></h4>
                <div class="table-responsive">
                    <table class="table table-standard">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Booking</th>
                                <th>Guest</th>
                                <th>Invoice Date</th>
                                <th>Total Due (Rs)</th>
                                <th>Discount (Rs)</th>
                                <th>Paid (Rs)</th>
                                <th>Balance (Rs)</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($invoices)): ?>
                                <tr>
                                    <td colspan="10" class="text-center">No invoices found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($invoices as $invoice): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($invoice['invoice_id']); ?></td>
                                        <td><?php echo htmlspecialchars($invoice['booking_id']); ?></td>
                                        <td><?php echo htmlspecialchars($invoice['first_name'] . ' ' . $invoice['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($invoice['invoice_date'] . ' ' . $invoice['invoice_time']); ?></td>
                                        <td><?php echo number_format($invoice['total_amount_due'], 2); ?></td>
                                        <td><?php echo number_format(isset($invoice['discount']) ? $invoice['discount'] : 0, 2); ?></td>
                                        <td><?php echo number_format($invoice['amount_paid'], 2); ?></td>
                                        <td><?php echo number_format($invoice['balance_due'], 2); ?></td>
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
                                                        <input type="hidden" name="current_discount" value="<?php echo htmlspecialchars(isset($invoice['discount']) ? $invoice['discount'] : 0); ?>">
                                                        <div class="form-group">
                                                            <label for="payment_received_<?php echo $invoice['invoice_id']; ?>" class="form-label">Amount Paid</label>
                                                            <input type="number" step="0.01" name="payment_received" id="payment_received_<?php echo $invoice['invoice_id']; ?>" class="form-control form-control-lg" placeholder="Enter amount (Rs)" min="0.01" required>
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
                                                            <label for="discount_applied_<?php echo $invoice['invoice_id']; ?>" class="form-label">Discount Amount</label>
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

            <!-- Confirmation Modal -->
            <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content standard-card">
                        <div class="modal-header">
                            <h5 class="modal-title" id="confirmModalLabel">Confirm Action</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p id="confirmMessage">Are you sure you want to generate an invoice for this booking?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-primary btn-outline-navy" data-bs-dismiss="modal">Cancel</button>
                            <form id="confirmForm" method="POST" style="display:inline;">
                                <input type="hidden" name="booking_id" id="modalBookingId">
                                <button type="submit" name="generate_invoice" class="btn btn-primary btn-navy">Confirm</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php require_once '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
    <script>
        // JavaScript to handle modal content dynamically
        const confirmModal = document.getElementById('confirmModal');
        confirmModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // Button that triggered the modal
            const bookingId = button.getAttribute('data-booking-id'); // Booking ID

            const modalBookingId = document.getElementById('modalBookingId');

            // Set the booking ID in the hidden input
            modalBookingId.value = bookingId;
        });

        // Client-side validation for payment form
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function (e) {
                const paymentReceived = form.querySelector('[name="payment_received"]');
                const paymentMethod = form.querySelector('[name="payment_method"]');
                const discountApplied = form.querySelector('[name="discount_applied"]');

                if (paymentReceived && parseFloat(paymentReceived.value) <= 0) {
                    e.preventDefault();
                    alert('Payment amount must be greater than 0.');
                    return;
                }

                if (paymentMethod && !paymentMethod.value) {
                    e.preventDefault();
                    alert('Please select a payment method.');
                    return;
                }

                if (discountApplied && parseFloat(discountApplied.value) < 0) {
                    e.preventDefault();
                    alert('Discount cannot be negative.');
                }
            });
        });
    </script>
</body>
</html>