<?php
session_start();
if (!isset($_SESSION['guest_id'])) header("Location: guest_login.php");
require_once '../includes/db_connect.php';
require_once '../includes/header.php';

$guest_id = $_SESSION['guest_id'];
$completed_bookings = $pdo->query("SELECT b.booking_id, r.room_number FROM bookings b JOIN rooms r ON b.room_id = r.room_id WHERE b.guest_id = $guest_id AND b.booking_status = 'Completed'")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("INSERT INTO feedback (guest_id, booking_id, feedback_date, feedback_time, feedback_rating, feedback_comments) VALUES (?, ?, CURDATE(), CURTIME(), ?, ?)");
    $stmt->execute([$guest_id, $_POST['booking_id'], $_POST['rating'], $_POST['comments']]);
    $success = "Feedback submitted successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS - Submit Feedback</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/forms.css">
</head>
<body>
    <h1>Submit Feedback</h1>
    <form method="POST">
        <label>Select Booking:</label>
        <select name="booking_id" required>
            <?php foreach ($completed_bookings as $booking) { ?>
                <option value="<?php echo $booking['booking_id']; ?>"><?php echo $booking['room_number'] . ' (ID: ' . $booking['booking_id'] . ')'; ?></option>
            <?php } ?>
        </select>
        <label>Rating (1-5):</label>
        <input type="number" name="rating" min="1" max="5" required>
        <label>Comments:</label>
        <textarea name="comments" rows="4"></textarea>
        <button type="submit">Submit Feedback</button>
    </form>
    <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
</body>
<?php require_once '../includes/footer.php'; ?>
</html>