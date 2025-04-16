<?php
session_start();
if (!isset($_SESSION['guest_id'])) {
    header("Location: guest_login.php");
    exit;
}
require_once '../includes/db_connect.php';
require_once '../includes/header.php';

// Fetch guest's name
$guest_id = $_SESSION['guest_id'];
$stmt = $pdo->prepare("SELECT first_name, last_name FROM guests WHERE guest_id = ?");
$stmt->execute([$guest_id]);
$guest = $stmt->fetch();
$guest_name = $guest ? htmlspecialchars($guest['first_name'] . ' ' . $guest['last_name']) : 'Guest';

// Fetch upcoming bookings (Confirmed bookings with check-in date on or after today)
$current_date = date('Y-m-d');
$upcoming_bookings = $pdo->prepare("
    SELECT b.*, r.room_number 
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.room_id 
    WHERE b.guest_id = ? 
    AND b.booking_status = 'Confirmed' 
    AND b.checkin_date >= ?
");
$upcoming_bookings->execute([$guest_id, $current_date]);
$bookings = $upcoming_bookings->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS - Guest Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/forms.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <main>
        <div class="container mt-5 pt-5">
            <h1 class="text-center mb-4">Welcome, <?php echo $guest_name; ?>!</h1>

            <!-- Section 1: Navigation Links -->
            <!-- <div class="card standard-card p-4 mb-4">
                <div class="dashboard-links">
                    <a href="guest_bookings.php" class="btn btn-primary btn-navy btn-lg">Book a Room</a>
                    <a href="guest_profile.php" class="btn btn-primary btn-navy btn-lg">My Profile</a>
                    <a href="guest_feedback.php" class="btn btn-primary btn-navy btn-lg">Submit Feedback</a>
                    <a href="logout.php" class="btn btn-danger btn-lg">Logout</a>
                </div>
            </div> -->

            <!-- Section 2: Upcoming Bookings -->
            <div class="card standard-card p-4 mb-4">
                <h4 class="mb-3 text-center">Upcoming Bookings</h4>
                <div class="table-responsive">
                    <table class="table table-standard">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Room</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Guests</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($bookings)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No upcoming bookings found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['room_number']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['checkin_date']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['checkout_date']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['num_guests']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['booking_status']); ?></td>
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
</body>
</html>