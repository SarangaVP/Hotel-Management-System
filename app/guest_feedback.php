<?php
session_start();
if (!isset($_SESSION['guest_id'])) header("Location: guest_login.php");
require_once '../includes/db_connect.php';
require_once '../includes/header.php';

// Initialize alert message
$alert_message = '';
$alert_type = '';

$guest_id = $_SESSION['guest_id'];

// Fetch completed bookings that haven't had feedback submitted yet
$completed_bookings = $pdo->query("
    SELECT b.booking_id, r.room_number 
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.room_id 
    WHERE b.guest_id = $guest_id 
    AND b.booking_status = 'Completed'
    AND b.booking_id NOT IN (SELECT booking_id FROM feedback WHERE guest_id = $guest_id)
")->fetchAll();

// Fetch previously submitted feedback
$previous_feedback = $pdo->query("
    SELECT f.*, r.room_number 
    FROM feedback f 
    JOIN bookings b ON f.booking_id = b.booking_id 
    JOIN rooms r ON b.room_id = r.room_id 
    WHERE f.guest_id = $guest_id
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $booking_id = $_POST['booking_id'];
    $rating = (int)$_POST['rating'];
    $comments = trim($_POST['comments']);

    // Validate inputs
    if (empty($booking_id)) {
        $alert_message = "Error: Please select a booking.";
        $alert_type = "danger";
    } elseif ($rating < 1 || $rating > 5) {
        $alert_message = "Error: Rating must be between 1 and 5.";
        $alert_type = "danger";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO feedback (guest_id, booking_id, feedback_date, feedback_time, feedback_rating, feedback_comments) VALUES (?, ?, CURDATE(), CURTIME(), ?, ?)");
            $stmt->execute([$guest_id, $booking_id, $rating, $comments]);
            $alert_message = "Feedback submitted successfully!";
            $alert_type = "success";
            // Refresh completed bookings and feedback
            $completed_bookings = $pdo->query("
                SELECT b.booking_id, r.room_number 
                FROM bookings b 
                JOIN rooms r ON b.room_id = r.room_id 
                WHERE b.guest_id = $guest_id 
                AND b.booking_status = 'Completed'
                AND b.booking_id NOT IN (SELECT booking_id FROM feedback WHERE guest_id = $guest_id)
            ")->fetchAll();
            $previous_feedback = $pdo->query("
                SELECT f.*, r.room_number 
                FROM feedback f 
                JOIN bookings b ON f.booking_id = b.booking_id 
                JOIN rooms r ON b.room_id = r.room_id 
                WHERE f.guest_id = $guest_id
            ")->fetchAll();
        } catch (PDOException $e) {
            $alert_message = "Error submitting feedback: " . $e->getMessage();
            $alert_type = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS - Submit Feedback</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/forms.css">
</head>
<body>
    <main>
        <div class="container mt-5 pt-5">
            <h1 class="text-center mb-4"><b>Feedbacks</b></h1>

            <!-- Alert Message -->
            <?php if ($alert_message): ?>
                <div class="alert alert-<?php echo htmlspecialchars($alert_type); ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($alert_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Section 1: Submit Feedback -->
            <div class="card standard-card p-4 mb-4">
                <h4 class="mb-3 text-center"><b>Provide Feedback</b></h4>
                <form method="POST" class="date-filter-form">
                    <div class="form-group">
                        <label for="booking_id" class="form-label">Select Booking</label>
                        <select name="booking_id" id="booking_id" class="form-control form-control-lg" required>
                            <?php if (empty($completed_bookings)): ?>
                                <option value="" disabled selected>No completed bookings available</option>
                            <?php else: ?>
                                <option value="" disabled selected>Select a booking</option>
                                <?php foreach ($completed_bookings as $booking): ?>
                                    <option value="<?php echo htmlspecialchars($booking['booking_id']); ?>">
                                        <?php echo htmlspecialchars($booking['room_number'] . ' (ID: ' . $booking['booking_id'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="rating" class="form-label">Rating (1-5)</label>
                        <input type="number" name="rating" id="rating" class="form-control form-control-lg" min="1" max="5" required>
                    </div>
                    <div class="form-group">
                        <label for="comments" class="form-label">Comments</label>
                        <textarea name="comments" id="comments" class="form-control form-control-lg" rows="1" placeholder="Share your experience..."></textarea>
                    </div>
                    <div class="form-group form-group-button">
                        <button type="submit" class="btn btn-primary btn-navy btn-lg filter-btn" <?php echo empty($completed_bookings) ? 'disabled' : ''; ?>>Submit Feedback</button>
                    </div>
                </form>
            </div>

            <!-- Section 2: My Feedback -->
            <div class="card standard-card p-4 mb-4">
                <h4 class="mb-3 text-center"><b>My Feedbacks</b></h4>
                <div class="table-responsive">
                    <table class="table table-standard">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Room</th>
                                <th>Rating</th>
                                <th>Comments</th>
                                <th>Submitted On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($previous_feedback)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">No feedback submitted yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($previous_feedback as $feedback): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($feedback['booking_id']); ?></td>
                                        <td><?php echo htmlspecialchars($feedback['room_number']); ?></td>
                                        <td><?php echo htmlspecialchars($feedback['feedback_rating']); ?>/5</td>
                                        <td><?php echo htmlspecialchars($feedback['feedback_comments'] ?: 'No comments'); ?></td>
                                        <td><?php echo htmlspecialchars($feedback['feedback_date'] . ' ' . $feedback['feedback_time']); ?></td>
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
    <script>
        // Client-side validation
        document.querySelector('form').addEventListener('submit', function (e) {
            const rating = parseInt(document.getElementById('rating').value);
            const bookingId = document.getElementById('booking_id').value;

            if (!bookingId) {
                e.preventDefault();
                alert('Please select a booking to provide feedback for.');
                return;
            }

            if (rating < 1 || rating > 5 || isNaN(rating)) {
                e.preventDefault();
                alert('Rating must be a number between 1 and 5.');
            }
        });
    </script>
</body>
</html>