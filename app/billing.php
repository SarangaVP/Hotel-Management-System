<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: index.php");
require_once '../includes/db_connect.php';
require_once '../includes/header.php';

if (isset($_POST['generate_invoice'])) {
    $booking_id = $_POST['booking_id'];
    $stmt = $pdo->prepare("SELECT price_per_night FROM rooms WHERE room_id = (SELECT room_id FROM bookings WHERE booking_id = ?)");
    $stmt->execute([$booking_id]);
    $price = $stmt->fetchColumn();
    $days = $pdo->query("SELECT DATEDIFF(checkout_date, checkin_date) FROM bookings WHERE booking_id = $booking_id")->fetchColumn();
    $total = $price * $days;

    $stmt = $pdo->prepare("INSERT INTO invoices (booking_id, guest_id, invoice_date, invoice_time, total_amount_due, amount_paid, balance_due, payment_status) VALUES (?, (SELECT guest_id FROM bookings WHERE booking_id = ?), CURDATE(), CURTIME(), ?, 0, ?, 'Unpaid')");
    $stmt->execute([$booking_id, $booking_id, $total, $total]);
}

$bookings = $pdo->query("SELECT b.*, g.first_name, g.last_name, r.room_number FROM bookings b JOIN guests g ON b.guest_id = g.guest_id JOIN rooms r ON b.room_id = r.room_id WHERE b.booking_status = 'Completed'")->fetchAll();
$invoices = $pdo->query("SELECT i.*, b.booking_id, g.first_name, g.last_name FROM invoices i JOIN bookings b ON i.booking_id = b.booking_id JOIN guests g ON i.guest_id = g.guest_id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS - Billing</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/forms.css">
</head>
<body>
    <h1>Billing and Payments</h1>
    <h2>Generate Invoice</h2>
    <div class="table-container">
        <table>
            <tr><th>ID</th><th>Guest</th><th>Room</th><th>Action</th></tr>
            <?php foreach ($bookings as $booking) { ?>
                <tr>
                    <td><?php echo $booking['booking_id']; ?></td>
                    <td><?php echo $booking['first_name'] . ' ' . $booking['last_name']; ?></td>
                    <td><?php echo $booking['room_number']; ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                            <button type="submit" name="generate_invoice">Generate</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
    <h2>Invoices</h2>
    <div class="table-container">
        <table>
            <tr><th>ID</th><th>Booking</th><th>Guest</th><th>Total Due</th><th>Paid</th><th>Balance</th><th>Status</th></tr>
            <?php foreach ($invoices as $invoice) { ?>
                <tr>
                    <td><?php echo $invoice['invoice_id']; ?></td>
                    <td><?php echo $invoice['booking_id']; ?></td>
                    <td><?php echo $invoice['first_name'] . ' ' . $invoice['last_name']; ?></td>
                    <td><?php echo $invoice['total_amount_due']; ?></td>
                    <td><?php echo $invoice['amount_paid']; ?></td>
                    <td><?php echo $invoice['balance_due']; ?></td>
                    <td><?php echo $invoice['payment_status']; ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>
<?php require_once '../includes/footer.php'; ?>
</html>