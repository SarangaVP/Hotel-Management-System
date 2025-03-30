<?php
session_start();
if (!isset($_SESSION['guest_id'])) header("Location: guest_login.php");
require_once '../includes/db_connect.php';
require_once '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search'])) {
    $checkin = $_POST['checkin_date'];
    $checkout = $_POST['checkout_date'];
    $room_type = $_POST['room_type'];
    $num_guests = $_POST['num_guests'];

    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE room_type = ? AND room_status = 'Available' AND room_id NOT IN (SELECT room_id FROM bookings WHERE (checkin_date <= ? AND checkout_date >= ?))");
    $stmt->execute([$room_type, $checkout, $checkin]);
    $available_rooms = $stmt->fetchAll();
}

if (isset($_POST['book'])) {
    $stmt = $pdo->prepare("INSERT INTO bookings (guest_id, room_id, checkin_date, checkout_date, num_guests, booking_status) VALUES (?, ?, ?, ?, ?, 'Pending')");
    $stmt->execute([$_SESSION['guest_id'], $_POST['room_id'], $_POST['checkin_date'], $_POST['checkout_date'], $_POST['num_guests']]);
    $pdo->prepare("UPDATE rooms SET room_status = 'Booked' WHERE room_id = ?")->execute([$_POST['room_id']]);
    $success = "Booking submitted successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS - Book a Room</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/forms.css">
</head>
<body>
    <h1>Book a Room</h1>
    <form method="POST">
        <input type="date" name="checkin_date" required>
        <input type="date" name="checkout_date" required>
        <select name="room_type" required>
            <option value="Single">Single</option>
            <option value="Double">Double</option>
            <option value="Suite">Suite</option>
        </select>
        <input type="number" name="num_guests" placeholder="Number of Guests" required>
        <button type="submit" name="search">Search Rooms</button>
    </form>
    <?php if (isset($available_rooms)) { ?>
        <h2>Available Rooms</h2>
        <div class="table-container">
            <table>
                <tr><th>Room Number</th><th>Type</th><th>Price/Night</th><th>Action</th></tr>
                <?php foreach ($available_rooms as $room) { ?>
                    <tr>
                        <td><?php echo $room['room_number']; ?></td>
                        <td><?php echo $room['room_type']; ?></td>
                        <td><?php echo $room['price_per_night']; ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="room_id" value="<?php echo $room['room_id']; ?>">
                                <input type="hidden" name="checkin_date" value="<?php echo $_POST['checkin_date']; ?>">
                                <input type="hidden" name="checkout_date" value="<?php echo $_POST['checkout_date']; ?>">
                                <input type="hidden" name="num_guests" value="<?php echo $_POST['num_guests']; ?>">
                                <button type="submit" name="book">Book</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    <?php } ?>
    <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
</body>
<?php require_once '../includes/footer.php'; ?>
</html>