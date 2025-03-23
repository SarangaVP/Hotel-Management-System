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
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM rooms WHERE room_id = ?");
    $stmt->execute([$_GET['delete']]);
}

$rooms = $pdo->query("SELECT * FROM rooms")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS - Rooms</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/forms.css">
</head>
<body>
    <h1>Room Management</h1>
    <form method="POST">
        <input type="text" name="room_number" placeholder="Room Number" required>
        <select name="room_type" required>
            <option value="Single">Single</option>
            <option value="Double">Double</option>
            <option value="Suite">Suite</option>
        </select>
        <input type="number" name="capacity" placeholder="Capacity" required>
        <input type="number" step="0.01" name="price" placeholder="Price/Night" required>
        <input type="number" name="floor" placeholder="Floor">
        <button type="submit" name="add_room">Add Room</button>
    </form>
    <div class="table-container">
        <table>
            <tr><th>ID</th><th>Number</th><th>Type</th><th>Capacity</th><th>Price</th><th>Floor</th><th>Status</th><th>Actions</th></tr>
            <?php foreach ($rooms as $room) { ?>
                <tr>
                    <td><?php echo $room['room_id']; ?></td>
                    <td><?php echo $room['room_number']; ?></td>
                    <td><?php echo $room['room_type']; ?></td>
                    <td><?php echo $room['room_capacity']; ?></td>
                    <td><?php echo $room['price_per_night']; ?></td>
                    <td><?php echo $room['floor_number']; ?></td>
                    <td><?php echo $room['room_status']; ?></td>
                    <td>
                        <a href="?edit=<?php echo $room['room_id']; ?>">Edit</a>
                        <a href="?delete=<?php echo $room['room_id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
    <?php if (isset($_GET['edit'])) {
        $stmt = $pdo->prepare("SELECT * FROM rooms WHERE room_id = ?");
        $stmt->execute([$_GET['edit']]);
        $room = $stmt->fetch();
    ?>
        <h2>Edit Room</h2>
        <form method="POST">
            <input type="hidden" name="room_id" value="<?php echo $room['room_id']; ?>">
            <input type="text" name="room_number" value="<?php echo $room['room_number']; ?>" required>
            <select name="room_type" required>
                <option value="Single" <?php if ($room['room_type'] == 'Single') echo 'selected'; ?>>Single</option>
                <option value="Double" <?php if ($room['room_type'] == 'Double') echo 'selected'; ?>>Double</option>
                <option value="Suite" <?php if ($room['room_type'] == 'Suite') echo 'selected'; ?>>Suite</option>
            </select>
            <input type="number" name="capacity" value="<?php echo $room['room_capacity']; ?>" required>
            <input type="number" step="0.01" name="price" value="<?php echo $room['price_per_night']; ?>" required>
            <input type="number" name="floor" value="<?php echo $room['floor_number']; ?>">
            <select name="status">
                <option value="Available" <?php if ($room['room_status'] == 'Available') echo 'selected'; ?>>Available</option>
                <option value="Booked" <?php if ($room['room_status'] == 'Booked') echo 'selected'; ?>>Booked</option>
                <option value="Maintenance" <?php if ($room['room_status'] == 'Maintenance') echo 'selected'; ?>>Maintenance</option>
            </select>
            <button type="submit" name="edit_room">Update Room</button>
        </form>
    <?php } ?>
</body>
<?php require_once '../includes/footer.php'; ?>
</html>