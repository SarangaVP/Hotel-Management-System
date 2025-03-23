<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: index.php");
require_once '../includes/db_connect.php';
require_once '../includes/header.php';

$feedbacks = $pdo->query("SELECT f.*, g.first_name, g.last_name, b.booking_id FROM feedback f JOIN guests g ON f.guest_id = g.guest_id JOIN bookings b ON f.booking_id = b.booking_id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS - Feedback</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/forms.css">
</head>
<body>
    <h1>Customer Feedback</h1>
    <div class="table-container">
        <table>
            <tr><th>ID</th><th>Guest</th><th>Booking</th><th>Rating</th><th>Comments</th><th>Date</th></tr>
            <?php foreach ($feedbacks as $feedback) { ?>
                <tr>
                    <td><?php echo $feedback['feedback_id']; ?></td>
                    <td><?php echo $feedback['first_name'] . ' ' . $feedback['last_name']; ?></td>
                    <td><?php echo $feedback['booking_id']; ?></td>
                    <td><?php echo $feedback['feedback_rating']; ?>/5</td>
                    <td><?php echo $feedback['feedback_comments']; ?></td>
                    <td><?php echo $feedback['feedback_date']; ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>
<?php require_once '../includes/footer.php'; ?>
</html>