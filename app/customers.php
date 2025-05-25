<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: index.php");
require_once '../includes/db_connect.php';
require_once '../includes/header.php';

if (isset($_POST['add_guest'])) {
    $password = $_POST['password'];
    $stmt = $pdo->prepare("INSERT INTO guests (first_name, last_name, address, phone_number, email, gov_id_number, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['first_name'], $_POST['last_name'], $_POST['address'], $_POST['phone'], $_POST['email'], $_POST['gov_id'], $password]);
    header("Location: customers.php");
    exit;
}

if (isset($_POST['edit_guest'])) {
    $password = !empty($_POST['password']) ? $_POST['password'] : $_POST['current_password'];
    $stmt = $pdo->prepare("UPDATE guests SET first_name = ?, last_name = ?, address = ?, phone_number = ?, email = ?, gov_id_number = ?, password = ? WHERE guest_id = ?");
    $stmt->execute([$_POST['first_name'], $_POST['last_name'], $_POST['address'], $_POST['phone'], $_POST['email'], $_POST['gov_id'], $password, $_POST['guest_id']]);
    header("Location: customers.php"); 
    exit;
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM guests WHERE guest_id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: customers.php");
    exit;
}

$guests = $pdo->query("SELECT * FROM guests")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS - Customers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/forms.css">
</head>
<body>
    <main>
    <div class="container mt-5 pt-5">
        <div class="card standard-card p-4 mb-4">
            <h1 class="text-center mb-4"><b>Customer Management</b></h1>
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <input type="text" name="first_name" class="form-control form-control-lg" placeholder="First Name" required>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="last_name" class="form-control form-control-lg" placeholder="Last Name" required>
                    </div>
                    <div class="col-md-12">
                        <input type="text" name="address" class="form-control form-control-lg" placeholder="Address">
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="phone" class="form-control form-control-lg" placeholder="Phone">
                    </div>
                    <div class="col-md-6">
                        <input type="email" name="email" class="form-control form-control-lg" placeholder="Email" required>
                    </div>
                    <div class="col-md-12">
                        <input type="text" name="gov_id" class="form-control form-control-lg" placeholder="Gov ID">
                    </div>
                    <div class="col-md-12">
                        <input type="password" name="password" class="form-control form-control-lg" placeholder="Password" required>
                    </div>
                    <div class="col-12 text-center">
                        <button type="submit" name="add_guest" class="btn btn-primary btn-navy btn-lg">Add Guest</button>
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
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Gov ID</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($guests as $guest) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($guest['guest_id']); ?></td>
                                <td><?php echo htmlspecialchars($guest['first_name'] . ' ' . $guest['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($guest['email']); ?></td>
                                <td><?php echo htmlspecialchars($guest['phone_number'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($guest['gov_id_number'] ?? 'N/A'); ?></td>
                                <td>
                                    <div class="d-flex gap-2 justify-content-center">
                                        <button type="button" class="btn btn-primary btn-navy btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $guest['guest_id']; ?>">Edit</button>
                                        <button type="button" class="btn btn-outline-primary btn-outline-navy btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $guest['guest_id']; ?>">Delete</button>
                                    </div>
                                    <div class="modal fade" id="editModal<?php echo $guest['guest_id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $guest['guest_id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content standard-card">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editModalLabel<?php echo $guest['guest_id']; ?>">Edit Guest</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form method="POST">
                                                        <input type="hidden" name="guest_id" value="<?php echo htmlspecialchars($guest['guest_id']); ?>">
                                                        <input type="hidden" name="current_password" value="<?php echo htmlspecialchars($guest['password']); ?>">
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <input type="text" name="first_name" class="form-control form-control-lg" value="<?php echo htmlspecialchars($guest['first_name']); ?>" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <input type="text" name="last_name" class="form-control form-control-lg" value="<?php echo htmlspecialchars($guest['last_name']); ?>" required>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <input type="text" name="address" class="form-control form-control-lg" value="<?php echo htmlspecialchars($guest['address'] ?? ''); ?>" placeholder="Address">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <input type="text" name="phone" class="form-control form-control-lg" value="<?php echo htmlspecialchars($guest['phone_number'] ?? ''); ?>" placeholder="Phone">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <input type="email" name="email" class="form-control form-control-lg" value="<?php echo htmlspecialchars($guest['email']); ?>" required>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <input type="text" name="gov_id" class="form-control form-control-lg" value="<?php echo htmlspecialchars($guest['gov_id_number'] ?? ''); ?>" placeholder="Gov ID">
                                                            </div>
                                                            <div class="col-md-12">
                                                                <input type="password" name="password" class="form-control form-control-lg" placeholder="New Password (leave blank to keep current)">
                                                            </div>
                                                            <div class="col-12 text-center">
                                                                <button type="submit" name="edit_guest" class="btn btn-primary btn-navy btn-lg">Update Guest</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal fade" id="deleteModal<?php echo $guest['guest_id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $guest['guest_id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content standard-card">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel<?php echo $guest['guest_id']; ?>">Confirm Deletion</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete <?php echo htmlspecialchars($guest['first_name'] . ' ' . $guest['last_name']); ?>?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-primary btn-outline-navy" data-bs-dismiss="modal">Cancel</button>
                                                    <a href="?delete=<?php echo $guest['guest_id']; ?>" class="btn btn-primary btn-navy">Delete</a>
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