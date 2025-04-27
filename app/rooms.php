<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: index.php");
require_once '../includes/db_connect.php';
require_once '../includes/header.php';

if (isset($_POST['add_room'])) {
    $stmt = $pdo->prepare("INSERT INTO rooms (room_number, room_type, room_capacity, price_per_night, floor_number) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['room_number'], $_POST['room_type'], $_POST['capacity'], $_POST['price'], $_POST['floor']]);
}

if (isset($_POST['edit_room'])) {
    $stmt = $pdo->prepare("UPDATE rooms SET room_number = ?, room_type = ?, room_capacity = ?, price_per_night = ?, floor_number = ?, room_status = ? WHERE room_id = ?");
    $stmt->execute([$_POST['room_number'], $_POST['room_type'], $_POST['capacity'], $_POST['price'], $_POST['floor'], $_POST['status'], $_POST['room_id']]);
    header("Location: rooms.php"); // Redirect to refresh the page after update
    exit;
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM rooms WHERE room_id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: rooms.php"); // Redirect to refresh the page after deletion
    exit;
}

$rooms = $pdo->query("SELECT * FROM rooms")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS - Rooms</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/forms.css">
</head>
<body>
    <main>
    <div class="container mt-5 pt-5">
        <div class="card standard-card p-4 mb-4">
            <h1 class="text-center mb-4"><b>Room Management</b></h1>
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <input type="text" name="room_number" class="form-control form-control-lg" placeholder="Room Number" required>
                    </div>
                    <div class="col-md-6">
                        <select name="room_type" class="form-select form-select-lg" required>
                            <option value="" disabled selected>Select Room Type</option>
                            <option value="Single">Single</option>
                            <option value="Double">Double</option>
                            <option value="Suite">Suite</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="number" name="capacity" class="form-control form-control-lg" placeholder="Capacity" required>
                    </div>
                    <div class="col-md-4">
                        <input type="number" step="100" name="price" class="form-control form-control-lg" placeholder="Price/Day (Rs)" required>
                    </div>
                    <div class="col-md-4">
                        <input type="number" name="floor" class="form-control form-control-lg" placeholder="Floor">
                    </div>
                    <div class="col-12 text-center">
                        <button type="submit" name="add_room" class="btn btn-primary btn-navy btn-lg">Add Room</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card standard-card p-4 mb-4">
            <div class="table-responsive">
                <table class="table table-standard">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Number</th>
                            <th>Type</th>
                            <th>Capacity</th>
                            <th>Price/Day (Rs)</th>
                            <th>Floor</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rooms as $room) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($room['room_id']); ?></td>
                                <td><?php echo htmlspecialchars($room['room_number']); ?></td>
                                <td><?php echo htmlspecialchars($room['room_type']); ?></td>
                                <td><?php echo htmlspecialchars($room['room_capacity']); ?></td>
                                <td><?php echo htmlspecialchars($room['price_per_night']); ?></td>
                                <td><?php echo htmlspecialchars($room['floor_number'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($room['room_status'] ?? 'Available'); ?></td>
                                <td>
                                    <div class="d-flex gap-2 justify-content-center">
                                        <button type="button" class="btn btn-primary btn-navy btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $room['room_id']; ?>">Edit</button>
                                        <button type="button" class="btn btn-outline-primary btn-outline-navy btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $room['room_id']; ?>">Delete</button>
                                    </div>
                                    <!-- Edit Room Modal -->
                                    <div class="modal fade" id="editModal<?php echo $room['room_id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $room['room_id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content standard-card">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editModalLabel<?php echo $room['room_id']; ?>">Edit Room</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form method="POST">
                                                        <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($room['room_id']); ?>">
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <input type="text" name="room_number" class="form-control form-control-lg" value="<?php echo htmlspecialchars($room['room_number']); ?>" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <select name="room_type" class="form-select form-select-lg" required>
                                                                    <option value="" disabled>Select Room Type</option>
                                                                    <option value="Single" <?php if ($room['room_type'] == 'Single') echo 'selected'; ?>>Single</option>
                                                                    <option value="Double" <?php if ($room['room_type'] == 'Double') echo 'selected'; ?>>Double</option>
                                                                    <option value="Suite" <?php if ($room['room_type'] == 'Suite') echo 'selected'; ?>>Suite</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <input type="number" name="capacity" class="form-control form-control-lg" value="<?php echo htmlspecialchars($room['room_capacity']); ?>" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <input type="number" step="0.01" name="price" class="form-control form-control-lg" value="<?php echo htmlspecialchars($room['price_per_night']); ?>" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <input type="number" name="floor" class="form-control form-control-lg" value="<?php echo htmlspecialchars($room['floor_number']); ?>">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <select name="status" class="form-select form-select-lg">
                                                                    <option value="Available" <?php if ($room['room_status'] == 'Available') echo 'selected'; ?>>Available</option>
                                                                    <option value="Booked" <?php if ($room['room_status'] == 'Booked') echo 'selected'; ?>>Booked</option>
                                                                    <option value="Maintenance" <?php if ($room['room_status'] == 'Maintenance') echo 'selected'; ?>>Maintenance</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-12 text-center">
                                                                <button type="submit" name="edit_room" class="btn btn-primary btn-navy btn-lg">Update Room</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Delete Confirmation Modal -->
                                    <div class="modal fade" id="deleteModal<?php echo $room['room_id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $room['room_id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content standard-card">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel<?php echo $room['room_id']; ?>">Confirm Deletion</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete Room <?php echo htmlspecialchars($room['room_number']); ?>?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-primary btn-outline-navy" data-bs-dismiss="modal">Cancel</button>
                                                    <a href="?delete=<?php echo $room['room_id']; ?>" class="btn btn-primary btn-navy">Delete</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
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