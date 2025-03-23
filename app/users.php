<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Administrator') header("Location: index.php");
require_once '../includes/db_connect.php';
require_once '../includes/header.php';

if (isset($_POST['add_staff'])) {
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO staff (first_name, last_name, role, phone_number, email, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['first_name'], $_POST['last_name'], $_POST['role'], $_POST['phone'], $_POST['email'], $password]);
}

$staff = $pdo->query("SELECT * FROM staff")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS - Users</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/forms.css">
</head>
<body>
    <h1>Staff Management</h1>
    <form method="POST">
        <input type="text" name="first_name" placeholder="First Name" required>
        <input type="text" name="last_name" placeholder="Last Name" required>
        <select name="role" required>
            <option value="Receptionist">Receptionist</option>
            <option value="Manager">Manager</option>
            <option value="Administrator">Administrator</option>
        </select>
        <input type="text" name="phone" placeholder="Phone">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="add_staff">Add Staff</button>
    </form>
    <div class="table-container">
        <table>
            <tr><th>ID</th><th>Name</th><th>Role</th><th>Email</th><th>Phone</th></tr>
            <?php foreach ($staff as $user) { ?>
                <tr>
                    <td><?php echo $user['staff_id']; ?></td>
                    <td><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></td>
                    <td><?php echo $user['role']; ?></td>
                    <td><?php echo $user['email']; ?></td>
                    <td><?php echo $user['phone_number']; ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>
<?php require_once '../includes/footer.php'; ?>
</html>