<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: index.php");
require_once '../includes/db_connect.php';
require_once '../includes/header.php';

// Initialize alert message
$alert_message = '';
$alert_type = '';

// Handle Add Booking
if (isset($_POST['add_booking'])) {
    $checkin_date = $_POST['checkin_date'];
    $checkout_date = $_POST['checkout_date'];
    $num_guests = (int)$_POST['num_guests'];

    // Validate dates and number of guests
    if (strtotime($checkout_date) <= strtotime($checkin_date)) {
        $alert_message = "Error: Check-out date must be after check-in date.";
        $alert_type = "danger";
    } elseif ($num_guests <= 0) {
        $alert_message = "Error: Number of guests must be greater than 0.";
        $alert_type = "danger";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO bookings (guest_id, room_id, checkin_date, checkout_date, num_guests, booking_status) VALUES (?, ?, ?, ?, ?, 'Confirmed')");
            $stmt->execute([$_POST['guest_id'], $_POST['room_id'], $checkin_date, $checkout_date, $num_guests]);
            $pdo->prepare("UPDATE rooms SET room_status = 'Booked' WHERE room_id = ?")->execute([$_POST['room_id']]);
            $alert_message = "Booking added successfully!";
            $alert_type = "success";
        } catch (PDOException $e) {
            $alert_message = "Error adding booking: " . $e->getMessage();
            $alert_type = "danger";
        }
    }
}

// Handle Cancel Booking
if (isset($_POST['cancel_booking'])) {
    try {
        $booking_id = $_POST['booking_id'];
        $room_id = $_POST['room_id'];
        $pdo->prepare("UPDATE bookings SET booking_status = 'Cancelled' WHERE booking_id = ?")->execute([$booking_id]);
        $pdo->prepare("UPDATE rooms SET room_status = 'Available' WHERE room_id = ?")->execute([$room_id]);
        $alert_message = "Booking cancelled successfully!";
        $alert_type = "success";
    } catch (PDOException $e) {
        $alert_message = "Error cancelling booking: " . $e->getMessage();
        $alert_type = "danger";
    }
}

// Fetch data
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/forms.css">
</head>
<body>
    <main>
        <div class="container mt-5 pt-5">
            <h1 class="text-center mb-4">Booking Management</h1>

            <!-- Alert Message -->
            <?php if ($alert_message): ?>
                <div class="alert alert-<?php echo htmlspecialchars($alert_type); ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($alert_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Section 1: Add New Booking -->
            <div class="card standard-card p-4 mb-4">
                <h4 class="mb-3 text-center">Add New Booking</h4>
                <form method="POST" class="date-filter-form">
                    <div class="form-group">
                        <label for="guest_id" class="form-label">Guest</label>
                        <select name="guest_id" id="guest_id" class="form-control form-control-lg" required>
                            <option value="" disabled selected>Select Guest</option>
                            <?php foreach ($guests as $guest): ?>
                                <option value="<?php echo htmlspecialchars($guest['guest_id']); ?>">
                                    <?php echo htmlspecialchars($guest['first_name'] . ' ' . $guest['last_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="room_id" class="form-label">Room</label>
                        <select name="room_id" id="room_id" class="form-control form-control-lg" required>
                            <option value="" disabled selected>Select Room</option>
                            <?php if (empty($rooms)): ?>
                                <option value="" disabled>No available rooms</option>
                            <?php else: ?>
                                <?php foreach ($rooms as $room): ?>
                                    <option value="<?php echo htmlspecialchars($room['room_id']); ?>">
                                        <?php echo htmlspecialchars($room['room_number']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="checkin_date" class="form-label">Check-in Date</label>
                        <input type="date" name="checkin_date" id="checkin_date" class="form-control form-control-lg date-input" required>
                    </div>
                    <div class="form-group">
                        <label for="checkout_date" class="form-label">Check-out Date</label>
                        <input type="date" name="checkout_date" id="checkout_date" class="form-control form-control-lg date-input" required>
                    </div>
                    <div class="form-group">
                        <label for="num_guests" class="form-label">Number of Guests</label>
                        <input type="number" name="num_guests" id="num_guests" class="form-control form-control-lg" placeholder="Number of Guests" min="1" required>
                    </div>
                    <div class="form-group form-group-button">
                        <button type="submit" name="add_booking" class="btn btn-primary btn-navy btn-lg filter-btn">Add Booking</button>
                    </div>
                </form>
            </div>

            <!-- Section 2: Current Bookings -->
            <div class="card standard-card p-4 mb-4">
                <h4 class="mb-3 text-center">Current Bookings</h4>
                <div class="table-responsive">
                    <table class="table table-standard">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Guest</th>
                                <th>Room</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Guests</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($bookings)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">No bookings found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['room_number']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['checkin_date']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['checkout_date']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['num_guests']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['booking_status']); ?></td>
                                        <td>
                                            <?php if ($booking['booking_status'] == 'Confirmed'): ?>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking['booking_id']); ?>">
                                                    <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($booking['room_id']); ?>">
                                                    <button type="submit" name="cancel_booking" class="btn btn-danger btn-sm">Cancel</button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
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
        // Client-side validation for dates
        document.querySelector('form').addEventListener('submit', function (e) {
            const checkin = new Date(document.getElementById('checkin_date').value);
            const checkout = new Date(document.getElementById('checkout_date').value);
            const numGuests = parseInt(document.getElementById('num_guests').value);

            if (checkout <= checkin) {
                e.preventDefault();
                alert('Check-out date must be after check-in date.');
            }
            if (numGuests <= 0) {
                e.preventDefault();
                alert('Number of guests must be greater than 0.');
            }
        });
    </script>
</body>
</html>