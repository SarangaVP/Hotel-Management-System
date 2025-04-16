<?php
session_start();
if (!isset($_SESSION['guest_id'])) header("Location: guest_login.php");
require_once '../includes/db_connect.php';
require_once '../includes/header.php';

// Initialize alert message
$alert_message = '';
$alert_type = '';

$guest_id = $_SESSION['guest_id'];
$stmt = $pdo->prepare("SELECT * FROM guests WHERE guest_id = ?");
$stmt->execute([$guest_id]);
$guest = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validate inputs
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $alert_message = "Error: All required fields must be filled.";
        $alert_type = "danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $alert_message = "Error: Invalid email format.";
        $alert_type = "danger";
    } elseif ($phone && !preg_match('/^\+?[1-9]\d{1,14}$/', $phone)) {
        $alert_message = "Error: Invalid phone number format.";
        $alert_type = "danger";
    } else {
        try {
            // Hash the password before storing
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE guests SET first_name = ?, last_name = ?, phone_number = ?, email = ?, password = ? WHERE guest_id = ?");
            $stmt->execute([$first_name, $last_name, $phone, $email, $hashed_password, $guest_id]);
            $alert_message = "Profile updated successfully!";
            $alert_type = "success";
            // Refresh guest data
            $stmt = $pdo->prepare("SELECT * FROM guests WHERE guest_id = ?");
            $stmt->execute([$guest_id]);
            $guest = $stmt->fetch();
        } catch (PDOException $e) {
            $alert_message = "Error updating profile: " . $e->getMessage();
            $alert_type = "danger";
        }
    }
}

// Fetch bookings
$bookings = $pdo->query("SELECT b.*, r.room_number FROM bookings b JOIN rooms r ON b.room_id = r.room_id WHERE b.guest_id = $guest_id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS - My Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/forms.css">
</head>
<body>
    <main>
        <div class="container mt-5 pt-5">
            <h1 class="text-center mb-4">My Profile</h1>

            <!-- Alert Message -->
            <?php if ($alert_message): ?>
                <div class="alert alert-<?php echo htmlspecialchars($alert_type); ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($alert_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Section 1: Update Profile -->
            <div class="card standard-card p-4 mb-4">
                <h4 class="mb-3 text-center">Update Profile</h4>
                <form method="POST" class="date-filter-form">
                    <div class="form-group">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" name="first_name" id="first_name" class="form-control form-control-lg" value="<?php echo htmlspecialchars($guest['first_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" name="last_name" id="last_name" class="form-control form-control-lg" value="<?php echo htmlspecialchars($guest['last_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" name="phone" id="phone" class="form-control form-control-lg" value="<?php echo htmlspecialchars($guest['phone_number']); ?>" placeholder="e.g., +1234567890">
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control form-control-lg" value="<?php echo htmlspecialchars($guest['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password" class="form-control form-control-lg" required>
                    </div>
                    <div class="form-group form-group-button">
                        <button type="submit" class="btn btn-primary btn-navy btn-lg filter-btn">Update Profile</button>
                    </div>
                </form>
            </div>

            <!-- Section 2: My Bookings -->
            <div class="card standard-card p-4 mb-4">
                <h4 class="mb-3 text-center">My Bookings</h4>
                <div class="table-responsive">
                    <table class="table table-standard">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Room</th>
                                <th>Check-in</th>
                                <th>Actual Check-in</th>
                                <th>Check-out</th>
                                <th>Actual Check-out</th>
                                <th>Guests</th>
                                <th>Status</th>
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
    <script>
        // Client-side validation
        document.querySelector('form').addEventListener('submit', function (e) {
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            const password = document.getElementById('password').value;

            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const phonePattern = /^\+?[1-9]\d{1,14}$/;

            if (!emailPattern.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                return;
            }

            if (phone && !phonePattern.test(phone)) {
                e.preventDefault();
                alert('Please enter a valid phone number (e.g., +1234567890).');
                return;
            }

            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long.');
            }
        });
    </script>
</body>
</html>