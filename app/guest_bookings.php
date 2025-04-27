<?php
session_start();
if (!isset($_SESSION['guest_id'])) header("Location: guest_login.php");
require_once '../includes/db_connect.php';
require_once '../includes/header.php';

// Initialize alert message
$alert_message = '';
$alert_type = '';
$available_rooms = [];
$last_booking_id = null; // To store the booking ID after insertion

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search'])) {
    $checkin = $_POST['checkin_date'];
    $checkout = $_POST['checkout_date'];
    $room_type = $_POST['room_type'];
    $num_guests = (int)$_POST['num_guests'];

    // Validate form inputs
    if (strtotime($checkout) <= strtotime($checkin)) {
        $alert_message = "Error: Check-out date must be after check-in date.";
        $alert_type = "danger";
    } elseif ($num_guests <= 0) {
        $alert_message = "Error: Number of guests must be greater than 0.";
        $alert_type = "danger";
    } else {
        // Search for available rooms with capacity check
        $stmt = $pdo->prepare("
            SELECT * FROM rooms 
            WHERE room_type = ? 
            AND room_status = 'Available' 
            AND room_capacity >= ?
            AND room_id NOT IN (
                SELECT room_id FROM bookings 
                WHERE (checkin_date <= ? AND checkout_date >= ?)
            )
        ");
        $stmt->execute([$room_type, $num_guests, $checkout, $checkin]);
        $available_rooms = $stmt->fetchAll();
        if (empty($available_rooms)) {
            $alert_message = "No rooms available for the selected criteria.";
            $alert_type = "warning";
        }
    }
}

if (isset($_POST['book'])) {
    $checkin = $_POST['checkin_date'];
    $checkout = $_POST['checkout_date'];
    $num_guests = (int)$_POST['num_guests'];
    $room_id = (int)$_POST['room_id'];

    // Fetch room capacity
    $stmt = $pdo->prepare("SELECT room_capacity FROM rooms WHERE room_id = ?");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch();
    $room_capacity = $room ? $room['room_capacity'] : 0;

    // Re-validate before booking
    if (strtotime($checkout) <= strtotime($checkin)) {
        $alert_message = "Error: Check-out date must be after check-in date.";
        $alert_type = "danger";
    } elseif ($num_guests <= 0) {
        $alert_message = "Error: Number of guests must be greater than 0.";
        $alert_type = "danger";
    } elseif ($num_guests > $room_capacity) {
        $alert_message = "Error: Number of guests ($num_guests) exceeds the room capacity ($room_capacity).";
        $alert_type = "danger";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO bookings (guest_id, room_id, checkin_date, checkout_date, num_guests, booking_status) VALUES (?, ?, ?, ?, ?, 'Pending')");
            $stmt->execute([$_SESSION['guest_id'], $room_id, $checkin, $checkout, $num_guests]);
            
            // Get the last inserted booking ID
            $last_booking_id = $pdo->lastInsertId();
            
            $pdo->prepare("UPDATE rooms SET room_status = 'Booked' WHERE room_id = ?")->execute([$room_id]);
            $alert_message = "Booking submitted successfully! Awaiting confirmation.";
            $alert_type = "success";
            $available_rooms = []; // Clear available rooms after booking
        } catch (PDOException $e) {
            $alert_message = "Error submitting booking: " . $e->getMessage();
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
    <title>HMS - Book a Room</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/forms.css">
</head>
<body>
    <main>
        <div class="container mt-5 pt-5">
            <h1 class="text-center mb-4"><b>Book Rooms</b></h1>

            <!-- Alert Message -->
            <?php if ($alert_message && !$last_booking_id): ?>
                <div class="alert alert-<?php echo htmlspecialchars($alert_type); ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($alert_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Section 1: Search for a Room -->
            <div class="card standard-card p-4 mb-4">
                <h4 class="mb-3 text-center"><b>Search for a Room</b></h4>
                <form method="POST" class="date-filter-form" id="searchForm">
                    <div class="form-group">
                        <label for="checkin_date" class="form-label">Check-in Date</label>
                        <input type="date" name="checkin_date" id="checkin_date" class="form-control form-control-lg date-input" value="<?php echo isset($_POST['checkin_date']) ? htmlspecialchars($_POST['checkin_date']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="checkout_date" class="form-label">Check-out Date</label>
                        <input type="date" name="checkout_date" id="checkout_date" class="form-control form-control-lg date-input" value="<?php echo isset($_POST['checkout_date']) ? htmlspecialchars($_POST['checkout_date']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="room_type" class="form-label">Room Type</label>
                        <select name="room_type" id="room_type" class="form-control form-control-lg" required>
                            <option value="" disabled <?php echo !isset($_POST['room_type']) ? 'selected' : ''; ?>>Select Room Type</option>
                            <option value="Single" <?php echo isset($_POST['room_type']) && $_POST['room_type'] == 'Single' ? 'selected' : ''; ?>>Single</option>
                            <option value="Double" <?php echo isset($_POST['room_type']) && $_POST['room_type'] == 'Double' ? 'selected' : ''; ?>>Double</option>
                            <option value="Suite" <?php echo isset($_POST['room_type']) && $_POST['room_type'] == 'Suite' ? 'selected' : ''; ?>>Suite</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="num_guests" class="form-label">Number of Guests</label>
                        <input type="number" name="num_guests" id="num_guests" class="form-control form-control-lg" placeholder="Number of Guests" value="<?php echo isset($_POST['num_guests']) ? htmlspecialchars($_POST['num_guests']) : ''; ?>" min="1" required>
                    </div>
                    <div class="form-group form-group-button">
                        <button type="submit" name="search" class="btn btn-primary btn-navy btn-lg filter-btn">Search Rooms</button>
                    </div>
                </form>
            </div>

            <!-- Section 2: Available Rooms -->
            <?php if (!empty($available_rooms)): ?>
                <div class="card standard-card p-4 mb-4">
                    <h4 class="mb-3 text-center">Available Rooms</h4>
                    <div class="table-responsive">
                        <table class="table table-standard">
                            <thead>
                                <tr>
                                    <th>Room Number</th>
                                    <th>Type</th>
                                    <th>Capacity</th>
                                    <th>Price/Night</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($available_rooms as $room): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($room['room_number']); ?></td>
                                        <td><?php echo htmlspecialchars($room['room_type']); ?></td>
                                        <td><?php echo htmlspecialchars($room['room_capacity']); ?></td>
                                        <td><?php echo '$' . number_format($room['price_per_night'], 2); ?></td>
                                        <td>
                                            <form method="POST" style="display:inline;" class="book-form">
                                                <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($room['room_id']); ?>">
                                                <input type="hidden" name="room_capacity" value="<?php echo htmlspecialchars($room['room_capacity']); ?>">
                                                <input type="hidden" name="checkin_date" value="<?php echo htmlspecialchars($_POST['checkin_date']); ?>">
                                                <input type="hidden" name="checkout_date" value="<?php echo htmlspecialchars($_POST['checkout_date']); ?>">
                                                <input type="hidden" name="num_guests" value="<?php echo htmlspecialchars($_POST['num_guests']); ?>">
                                                <input type="hidden" name="book" value="true">
                                                <button type="button" class="btn btn-primary btn-sm book-btn" data-bs-toggle="modal" data-bs-target="#confirmBookingModal">Book</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Validation Error Modal -->
            <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content standard-card">
                        <div class="modal-header">
                            <h5 class="modal-title" id="errorModalLabel">Invalid Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria ⎯

-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p id="errorMessage"></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary btn-navy" data-bs-dismiss="modal">OK</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Confirmation Modal -->
            <div class="modal fade" id="confirmBookingModal" tabindex="-1" aria-labelledby="confirmBookingModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content standard-card">
                        <div class="modal-header">
                            <h5 class="modal-title" id="confirmBookingModalLabel">Confirm Booking</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to book this room?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-primary btn-outline-navy" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" id="confirmBookingBtn" class="btn btn-primary btn-navy">Yes, Book</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success Modal -->
            <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content standard-card">
                        <div class="modal-header">
                            <h5 class="modal-title" id="successModalLabel">Booking Confirmed</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Your booking has been submitted successfully! Awaiting confirmation.</p>
                            <p>Booking ID: <strong id="bookingIdDisplay"><?php echo $last_booking_id ? htmlspecialchars(str_pad($last_booking_id)) : ''; ?></strong></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary btn-navy" data-bs-dismiss="modal">OK</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php require_once '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
    <script>
        // Client-side validation for search form
        document.getElementById('searchForm').addEventListener('submit', function (e) {
            const checkin = new Date(document.getElementById('checkin_date').value);
            const checkout = new Date(document.getElementById('checkout_date').value);
            const numGuests = parseInt(document.getElementById('num_guests').value);

            const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
            const errorMessage = document.getElementById('errorMessage');

            if (checkout <= checkin) {
                e.preventDefault();
                errorMessage.textContent = 'Check-out date must be after check-in date.';
                errorModal.show();
                return;
            }
            if (numGuests <= 0) {
                e.preventDefault();
                errorMessage.textContent = 'Number of guests must be greater than 0.';
                errorModal.show();
                return;
            }
        });

        // Client-side validation for booking forms and custom confirmation modal
        let activeForm = null;
        document.querySelectorAll('.book-btn').forEach(button => {
            button.addEventListener('click', function () {
                activeForm = this.closest('form');
                console.log('Book button clicked, activeForm set:', activeForm); // Debugging

                const checkin = new Date(activeForm.querySelector('input[name="checkin_date"]').value);
                const checkout = new Date(activeForm.querySelector('input[name="checkout_date"]').value);
                const numGuests = parseInt(activeForm.querySelector('input[name="num_guests"]').value);
                const roomCapacity = parseInt(activeForm.querySelector('input[name="room_capacity"]').value);

                const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                const errorMessage = document.getElementById('errorMessage');

                if (checkout <= checkin) {
                    errorMessage.textContent = 'Check-out date must be after check-in date.';
                    errorModal.show();
                    return;
                }
                if (numGuests <= 0) {
                    errorMessage.textContent = 'Number of guests must be greater than 0.';
                    errorModal.show();
                    return;
                }
                if (numGuests > roomCapacity) {
                    errorMessage.textContent = `Number of guests (${numGuests}) exceeds the room capacity (${roomCapacity}).`;
                    errorModal.show();
                    return;
                }

                // Show the confirmation modal
                const confirmModal = new bootstrap.Modal(document.getElementById('confirmBookingModal'));
                confirmModal.show();
            });
        });

        // Handle confirmation
        document.getElementById('confirmBookingBtn').addEventListener('click', function () {
            console.log('Confirm button clicked, activeForm:', activeForm); // Debugging
            if (activeForm) {
                console.log('Submitting form...');
                activeForm.submit();
            } else {
                console.error('No active form found to submit.');
            }
        });

        // Show success modal if booking was successful
        <?php if ($last_booking_id): ?>
            document.addEventListener('DOMContentLoaded', function () {
                const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                successModal.show();
            });
        <?php endif; ?>
    </script>
</body>
</html>