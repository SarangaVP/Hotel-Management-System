<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Administrator') header("Location: index.php");
require_once '../includes/db_connect.php';
require_once '../includes/header.php';

if (isset($_POST['add_staff'])) {
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO staff (first_name, last_name, role, phone_number, email, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['first_name'], $_POST['last_name'], $_POST['role'], $_POST['phone'], $_POST['email'], $password]);
    header("Location: staff.php"); // Redirect to refresh the page after adding
    exit;
}

if (isset($_POST['edit_staff'])) {
    $stmt = $pdo->prepare("UPDATE staff SET first_name = ?, last_name = ?, role = ?, phone_number = ?, email = ? WHERE staff_id = ?");
    $stmt->execute([$_POST['first_name'], $_POST['last_name'], $_POST['role'], $_POST['phone'], $_POST['email'], $_POST['staff_id']]);
    
    // Update password if provided
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE staff SET password = ? WHERE staff_id = ?");
        $stmt->execute([$password, $_POST['staff_id']]);
    }
    header("Location: staff.php"); // Redirect to refresh the page after updating
    exit;
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM staff WHERE staff_id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: staff.php"); // Redirect to refresh the page after deletion
    exit;
}

$staff = $pdo->query("SELECT * FROM staff")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS - Staff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/forms.css">
</head>
<body>
    <main>
        <div class="container mt-5 pt-5">
            <div class="card standard-card p-4 mb-4">
                <h2 class="text-center mb-4">Staff Management</h2>
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <input type="text" name="first_name" class="form-control form-control-lg" placeholder="First Name" required>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="last_name" class="form-control form-control-lg" placeholder="Last Name" required>
                        </div>
                        <div class="col-md-6">
                            <select name="role" class="form-select form-select-lg" required>
                                <option value="" disabled selected>Select Role</option>
                                <option value="Receptionist">Receptionist</option>
                                <option value="Manager">Manager</option>
                                <option value="Administrator">Administrator</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="phone" class="form-control form-control-lg" placeholder="Phone">
                        </div>
                        <div class="col-md-6">
                            <input type="email" name="email" class="form-control form-control-lg" placeholder="Email" required>
                        </div>
                        <div class="col-md-6">
                            <input type="password" name="password" class="form-control form-control-lg" placeholder="Password" required>
                        </div>
                        <div class="col-12 text-center">
                            <button type="submit" name="add_staff" class="btn btn-primary btn-navy btn-lg">Add Staff</button>
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
                                <th>Role</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($staff as $user) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['staff_id']); ?></td>
                                    <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['phone_number'] ?? 'N/A'); ?></td>
                                    <td>
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button type="button" class="btn btn-primary btn-navy btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $user['staff_id']; ?>">Edit</button>
                                            <button type="button" class="btn btn-outline-primary btn-outline-navy btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $user['staff_id']; ?>">Delete</button>
                                        </div>
                                        <!-- Edit Staff Modal -->
                                        <div class="modal fade" id="editModal<?php echo $user['staff_id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $user['staff_id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content standard-card">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editModalLabel<?php echo $user['staff_id']; ?>">Edit Staff</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form method="POST">
                                                            <input type="hidden" name="staff_id" value="<?php echo htmlspecialchars($user['staff_id']); ?>">
                                                            <div class="row g-3">
                                                                <div class="col-md-6">
                                                                    <input type="text" name="first_name" class="form-control form-control-lg" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <input type="text" name="last_name" class="form-control form-control-lg" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <select name="role" class="form-select form-select-lg" required>
                                                                        <option value="Receptionist" <?php if ($user['role'] == 'Receptionist') echo 'selected'; ?>>Receptionist</option>
                                                                        <option value="Manager" <?php if ($user['role'] == 'Manager') echo 'selected'; ?>>Manager</option>
                                                                        <option value="Administrator" <?php if ($user['role'] == 'Administrator') echo 'selected'; ?>>Administrator</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <input type="text" name="phone" class="form-control form-control-lg" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>" placeholder="Phone">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <input type="email" name="email" class="form-control form-control-lg" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <input type="password" name="password" class="form-control form-control-lg" placeholder="New Password (optional)">
                                                                </div>
                                                                <div class="col-12 text-center">
                                                                    <button type="submit" name="edit_staff" class="btn btn-primary btn-navy btn-lg">Update Staff</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Delete Confirmation Modal -->
                                        <div class="modal fade" id="deleteModal<?php echo $user['staff_id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $user['staff_id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content standard-card">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteModalLabel<?php echo $user['staff_id']; ?>">Confirm Deletion</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Are you sure you want to delete <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-primary btn-outline-navy" data-bs-dismiss="modal">Cancel</button>
                                                        <a href="?delete=<?php echo $user['staff_id']; ?>" class="btn btn-primary btn-navy">Delete</a>
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