<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: index.php");
require_once '../includes/db_connect.php';
require_once '../includes/header.php';

if (isset($_POST['checkin'])) {
    $stmt = $pdo->prepare("UPDATE bookings SET actual_checkin_date = CURDATE(), actual_checkin_time = CURTIME(), booking_status = 'Confirmed' WHERE booking_id = ?");
    $stmt->execute([$_POST['booking_id']]);
}

if (isset($_POST['checkout'])) {
    $stmt = $pdo->prepare("UPDATE bookings SET actual_checkout_date = CURDATE(), actual_checkout_time = CURTIME(), booking_status = 'Completed' WHERE booking_id = ?");
    $stmt->execute([$_POST['booking_id']]);
    $pdo->prepare("UPDATE rooms SET room_status = 'Available' WHERE room_id = (SELECT room_id FROM bookings WHERE booking_id = ?)")
        ->execute([$_POST['booking_id']]);
}

$bookings = $pdo->query("SELECT b.*, g.first_name, g.last_name, r.room_number FROM bookings b JOIN guests g ON b.guest_id = g.guest_id JOIN rooms r ON b.room_id = r.room_id WHERE b.booking_status IN ('Confirmed', 'Pending')")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS - Check-in/Check-out</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/forms.css">
</head>
<body>
    <h1>Check-in/Check-out</h1>
    <div class="table-container">
        <table>
            <tr><th>ID</th><th>Guest</th><th>Room</th><th>Check-in</th><th>Check-out</th><th>Status</th><th>Actions</th></tr>
            <?php foreach ($bookings as $booking) { ?>
                <tr>
                    <td><?php echo $booking['booking_id']; ?></td>
                    <td><?php echo $booking['first_name'] . ' ' . $booking['last_name']; ?></td>
                    <td><?php echo $booking['room_number']; ?></td>
                    <td><?php echo $booking['checkin_date']; ?></td>
                    <td><?php echo $booking['checkout_date']; ?></td>
                    <td><?php echo $booking['booking_status']; ?></td>
                    <td>
                        <?php if ($booking['actual_checkin_date'] == null) { ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                <button type="submit" name="checkin">Check-in</button>
                            </form>
                        <?php } ?>
                        <?php if ($booking['actual_checkout_date'] == null && $booking['actual_checkin_date'] != null) { ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                <button type="submit" name="checkout">Check-out</button>
                            </form>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>
<?php require_once '../includes/footer.php'; ?>
</html>