<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: index.php");
require_once '../includes/db_connect.php';
require_once '../includes/header.php';

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM feedback WHERE feedback_id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: feedback.php"); // Redirect to refresh the page after deletion
    exit;
}

$feedbacks = $pdo->query("SELECT f.*, g.first_name, g.last_name, b.booking_id FROM feedback f JOIN guests g ON f.guest_id = g.guest_id JOIN bookings b ON f.booking_id = b.booking_id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS - Feedback</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/forms.css">
</head>
<body>
    <main>
        <div class="container mt-5 pt-5">
            <div class="card standard-card p-4 mb-4">
                <h1 class="text-center mb-4"><b>Customer Feedback</b></h1>
                <div class="table-responsive">
                    <table class="table table-standard">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Guest</th>
                                <th>Booking</th>
                                <th>Rating</th>
                                <th>Comments</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($feedbacks as $feedback) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($feedback['feedback_id']); ?></td>
                                    <td><?php echo htmlspecialchars($feedback['first_name'] . ' ' . $feedback['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($feedback['booking_id']); ?></td>
                                    <td><?php echo htmlspecialchars($feedback['feedback_rating']); ?>/5</td>
                                    <td><?php echo htmlspecialchars($feedback['feedback_comments']); ?></td>
                                    <td><?php echo htmlspecialchars($feedback['feedback_date']); ?></td>
                                    <td>
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button type="button" class="btn btn-outline-primary btn-outline-navy btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $feedback['feedback_id']; ?>">Delete</button>
                                        </div>
                                        <!-- Delete Confirmation Modal -->
                                        <div class="modal fade" id="deleteModal<?php echo $feedback['feedback_id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $feedback['feedback_id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content standard-card">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteModalLabel<?php echo $feedback['feedback_id']; ?>">Confirm Deletion</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Are you sure you want to delete this feedback from <?php echo htmlspecialchars($feedback['first_name'] . ' ' . $feedback['last_name']); ?>?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-primary btn-outline-navy" data-bs-dismiss="modal">Cancel</button>
                                                        <a href="?delete=<?php echo $feedback['feedback_id']; ?>" class="btn btn-primary btn-navy">Delete</a>
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