<?php
session_start();
if (!isset($_SESSION['guest_id'])) header("Location: guest_login.php");
require_once '../includes/db_connect.php';
require_once '../includes/header.php';

$guest_id = $_SESSION['guest_id'];
$stmt = $pdo->prepare("SELECT * FROM guests WHERE guest_id = ?");
$stmt->execute([$guest_id]);
$guest = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("UPDATE guests SET first_name = ?, last_name = ?, phone_number = ?, email = ?, password = ? WHERE guest_id = ?");
    $stmt->execute([$_POST['first_name'], $_POST['last_name'], $_POST['phone'], $_POST['email'], $_POST['password'], $guest_id]);
    header("Location: guest_profile.php");
    exit;
}

$bookings = $pdo->query("SELECT b.*, r.room_number FROM bookings b JOIN rooms r ON b.room_id = r.room_id WHERE b.guest_id = $guest_id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS - My Profile</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/forms.css">
</head>
<body>
    <h1>My Profile</h1>
    <form method="POST">
        <label>First Name:</label>
        <input type="text" name="first_name" value="<?php echo $guest['first_name']; ?>" required>
        <label>Last Name:</label>
        <input type="text" name="last_name" value="<?php echo $guest['last_name']; ?>" required>
        <label>Phone:</label>
        <input type="text" name="phone" value="<?php echo $guest['phone_number']; ?>">
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo $guest['email']; ?>" required>
        <label>Password:</label>
        <input type="password" name="password" value="<?php echo $guest['password']; ?>" required>
        <button type="submit">Update Profile</button>
    </form>
    <h2>My Bookings</h2>
    <div class="table-container">
        <table>
            <tr><th>ID</th><th>Room</th><th>Check-in</th><th>Check-out</th><th>Guests</th><th>Status</th></tr>
            <?php foreach ($bookings as $booking) { ?>
                <tr>
                    <td><?php echo $booking['booking_id']; ?></td>
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