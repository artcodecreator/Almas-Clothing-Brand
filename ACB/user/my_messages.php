<?php
session_start();
include("../includes/db.php");

// Protect: check if logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user messages
$stmt = $conn->prepare("SELECT * FROM messages WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

include '../includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">My Messages</h2>
        <a href="contact.php" class="btn btn-primary"><i class="bi bi-pencil-square"></i> New Message</a>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <div class="row g-4">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                            <div>
                                <h5 class="mb-0 fw-bold"><?php echo htmlspecialchars($row['subject']); ?></h5>
                                <small class="text-muted"><?php echo date("M d, Y h:i A", strtotime($row['created_at'])); ?></small>
                            </div>
                            <span class="badge bg-<?php echo $row['status'] == 'Replied' ? 'success' : 'secondary'; ?>">
                                <?php echo htmlspecialchars($row['status']); ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <p class="mb-3 text-secondary">
                                <i class="bi bi-person-fill"></i> You: <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                            </p>
                            
                            <?php if (!empty($row['admin_reply'])): ?>
                                <div class="bg-light p-3 rounded border-start border-4 border-primary">
                                    <h6 class="fw-bold text-primary mb-2"><i class="bi bi-reply-fill"></i> Admin Reply:</h6>
                                    <p class="mb-1"><?php echo nl2br(htmlspecialchars($row['admin_reply'])); ?></p>
                                    <small class="text-muted d-block text-end">
                                        Replied on: <?php echo date("M d, Y h:i A", strtotime($row['replied_at'])); ?>
                                    </small>
                                </div>
                            <?php else: ?>
                                <div class="text-muted small fst-italic">
                                    <i class="bi bi-hourglass-split"></i> Awaiting response...
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">
            <i class="bi bi-inbox fs-4 d-block mb-2"></i>
            You haven't sent any messages yet. Need help? <a href="contact.php" class="alert-link">Contact Us</a>.
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
