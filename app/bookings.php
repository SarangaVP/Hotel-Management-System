<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: index.php");
require_once '../includes/db_connect.php';
require_once '../includes/header.php';

if (isset($_POST['add_booking'])) {
    $stmt = $pdo->prepare("INSERT INTO bookings (guest_id, room_id, checkin_date, checkout_date, num_guests, booking_status) VALUES (?, ?, ?, ?, ?, 'Confirmed')");
    $stmt->execute([$_POST['guest_id'], $_POST['room_id'], $_POST['checkin_date'], $_POST['checkout_date'], $_POST['num_guests']]);
    $pdo->prepare("UPDATE rooms SET room_status = 'Booked' WHERE room_id = ?")->execute([$_POST['room_id']]);
}

$bookings = $pdo->query("SELECT b.*, g.first_name, g.last_name, r.room_number FROM bookings b JOIN guests g ON b.guest_id = g.guest_id JOIN rooms r ON b.room_id = r.room_id")->fetchAll();
$guests = $pdo->query("SELECT guest_id, first_name, last_name FROM guests")->fetchAll();
$rooms = $pdo->query("SELECT room_id, room_number FROM rooms WHERE room_status = 'Available'")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS - Bookings</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/forms.css">
</head>
<body>
    <h1>Booking Management</h1>
    <form method="POST">
        <select name="guest_id" required>
            <?php foreach ($guests as $guest) { ?>
                <option value="<?php echo $guest['guest_id']; ?>"><?php echo $guest['first_name'] . ' ' . $guest['last_name']; ?></option>
            <?php } ?>
        </select>
        <select name="room_id" required>
            <?php foreach ($rooms as $room) { ?>
                <option value="<?php echo $room['room_id']; ?>"><?php echo $room['room_number']; ?></option>
            <?php } ?>
        </select>
        <input type="date" name="checkin_date" required>
        <input type="date" name="checkout_date" required>
        <input type="number" name="num_guests" placeholder="Number of Guests" required>
        <button type="submit" name="add_booking">Add Booking</button>
    </form>
    <div class="table-container">
        <table>
            <tr><th>ID</th><th>Guest</th><th>Room</th><th>Check-in</th><th>Check-out</th><th>Guests</th><th>Status</th></tr>
            <?php foreach ($bookings as $booking) { ?>
                <tr>
                    <td><?php echo $booking['booking_id']; ?></td>
                    <td><?php echo $booking['first_name'] . ' ' . $booking['last_name']; ?></td>
                    <td><?php echo $booking['room_number']; ?></td>
                    <td><?php echo $booking['checkin_date']; ?></td>
                    <td><?php echo $booking['checkout_date']; ?></td>
                    <td><?php echo $booking['num_guests']; ?></td>
                    <td><?php echo $booking['booking_status']; ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>
<?php require_once '../includes/footer.php'; ?>
</html>