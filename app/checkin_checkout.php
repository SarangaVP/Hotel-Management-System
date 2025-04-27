<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: index.php");
require_once '../includes/db_connect.php';
require_once '../includes/header.php';

// Initialize alert message
$alert_message = '';
$alert_type = '';

// Handle Check-in
if (isset($_POST['checkin'])) {
    try {
        $stmt = $pdo->prepare("UPDATE bookings SET actual_checkin_date = CURDATE(), actual_checkin_time = CURTIME(), booking_status = 'Confirmed' WHERE booking_id = ?");
        $stmt->execute([$_POST['booking_id']]);
        $alert_message = "Check-in completed successfully!";
        $alert_type = "success";
    } catch (PDOException $e) {
        $alert_message = "Error during check-in: " . $e->getMessage();
        $alert_type = "danger";
    }
}

// Handle Check-out
if (isset($_POST['checkout'])) {
    try {
        $stmt = $pdo->prepare("UPDATE bookings SET actual_checkout_date = CURDATE(), actual_checkout_time = CURTIME(), booking_status = 'Completed' WHERE booking_id = ?");
        $stmt->execute([$_POST['booking_id']]);
        $pdo->prepare("UPDATE rooms SET room_status = 'Available' WHERE room_id = (SELECT room_id FROM bookings WHERE booking_id = ?)")
            ->execute([$_POST['booking_id']]);
        $alert_message = "Check-out completed successfully!";
        $alert_type = "success";
    } catch (PDOException $e) {
        $alert_message = "Error during check-out: " . $e->getMessage();
        $alert_type = "danger";
    }
}

// Fetch bookings
$bookings = $pdo->query("SELECT b.*, g.first_name, g.last_name, r.room_number FROM bookings b JOIN guests g ON b.guest_id = g.guest_id JOIN rooms r ON b.room_id = r.room_id WHERE b.booking_status IN ('Confirmed', 'Pending')")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS - Check-in/Check-out</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/forms.css">
</head>
<body>
    <main>
        <div class="container mt-5 pt-5">
            <h1 class="text-center mb-4"><b>Check-in/Check-out Management</b></h1>

            <!-- Alert Message -->
            <?php if ($alert_message): ?>
                <div class="alert alert-<?php echo htmlspecialchars($alert_type); ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($alert_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Check-in/Check-out Table -->
            <div class="card standard-card p-4 mb-4">
                <!-- <h4 class="mb-3 text-center"><b>Manage Check-in/Check-out</b></h4> -->
                <div class="table-responsive">
                    <table class="table table-standard">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Guest</th>
                                <th>Room</th>
                                <th>Scheduled Check-in Date</th>
                                <th>Actual Check-in</th>
                                <th>Scheduled Check-out Date</th>
                                <th>Actual Check-out</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($bookings)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">No active bookings found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['room_number']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['checkin_date']); ?></td>
                                        <td>
                                            <?php
                                            if ($booking['actual_checkin_date']) {
                                                echo htmlspecialchars($booking['actual_checkin_date'] . ' ' . $booking['actual_checkin_time']);
                                            } else {
                                                echo 'Not checked in';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($booking['checkout_date']); ?></td>
                                        <td>
                                            <?php
                                            if ($booking['actual_checkout_date']) {
                                                echo htmlspecialchars($booking['actual_checkout_date'] . ' ' . $booking['actual_checkout_time']);
                                            } else {
                                                echo 'Not checked out';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($booking['booking_status']); ?></td>
                                        <td class="d-flex gap-2 justify-content-center">
                                            <?php if ($booking['actual_checkin_date'] == null): ?>
                                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#confirmModal" data-action="checkin" data-booking-id="<?php echo htmlspecialchars($booking['booking_id']); ?>">Check-in</button>
                                            <?php endif; ?>
                                            <?php if ($booking['actual_checkout_date'] == null && $booking['actual_checkin_date'] != null): ?>
                                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#confirmModal" data-action="checkout" data-booking-id="<?php echo htmlspecialchars($booking['booking_id']); ?>">Check-out</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Confirmation Modal -->
            <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content standard-card">
                        <div class="modal-header">
                            <h5 class="modal-title" id="confirmModalLabel">Confirm Action</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p id="confirmMessage"></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-primary btn-outline-navy" data-bs-dismiss="modal">Cancel</button>
                            <form id="confirmForm" method="POST" style="display:inline;">
                                <input type="hidden" name="booking_id" id="modalBookingId">
                                <button type="submit" id="confirmButton" class="btn btn-primary btn-navy">Confirm</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php require_once '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
    <script>
        // JavaScript to handle modal content dynamically
        const confirmModal = document.getElementById('confirmModal');
        confirmModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // Button that triggered the modal
            const action = button.getAttribute('data-action'); // Check-in or Check-out
            const bookingId = button.getAttribute('data-booking-id'); // Booking ID

            const modalMessage = document.getElementById('confirmMessage');
            const confirmButton = document.getElementById('confirmButton');
            const confirmForm = document.getElementById('confirmForm');
            const modalBookingId = document.getElementById('modalBookingId');

            // Set the message and form action based on the action type
            if (action === 'checkin') {
                modalMessage.textContent = 'Are you sure you want to check in this booking?';
                confirmForm.querySelector('button').setAttribute('name', 'checkin');
                confirmButton.classList.remove('btn-success');
                confirmButton.classList.add('btn-primary', 'btn-navy');
            } else if (action === 'checkout') {
                modalMessage.textContent = 'Are you sure you want to check out this booking?';
                confirmForm.querySelector('button').setAttribute('name', 'checkout');
                confirmButton.classList.remove('btn-primary', 'btn-navy');
                confirmButton.classList.add('btn-success');
            }

            // Set the booking ID in the hidden input
            modalBookingId.value = bookingId;
        });
    </script>
</body>
</html>