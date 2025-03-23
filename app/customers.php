<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: index.php");
require_once '../includes/db_connect.php';
require_once '../includes/header.php';

if (isset($_POST['add_guest'])) {
    $stmt = $pdo->prepare("INSERT INTO guests (first_name, last_name, address, phone_number, email, gov_id_number) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['first_name'], $_POST['last_name'], $_POST['address'], $_POST['phone'], $_POST['email'], $_POST['gov_id']]);
}

$guests = $pdo->query("SELECT * FROM guests")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS - Customers</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/forms.css">
</head>
<body>
    <h1>Customer Management</h1>
    <form method="POST">
        <input type="text" name="first_name" placeholder="First Name" required>
        <input type="text" name="last_name" placeholder="Last Name" required>
        <input type="text" name="address" placeholder="Address">
        <input type="text" name="phone" placeholder="Phone">
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="gov_id" placeholder="Gov ID">
        <button type="submit" name="add_guest">Add Guest</button>
    </form>
    <div class="table-container">
        <table>
            <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Gov ID</th></tr>
            <?php foreach ($guests as $guest) { ?>
                <tr>
                    <td><?php echo $guest['guest_id']; ?></td>
                    <td><?php echo $guest['first_name'] . ' ' . $guest['last_name']; ?></td>
                    <td><?php echo $guest['email']; ?></td>
                    <td><?php echo $guest['phone_number']; ?></td>
                    <td><?php echo $guest['gov_id_number']; ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>
<?php require_once '../includes/footer.php'; ?>
</html>