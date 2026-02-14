<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $conn->query("DELETE FROM messages WHERE id = $id");
    header("Location: manage_messages.php");
    exit;
}

// Handle Reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_id'], $_POST['reply_message'])) {
    $id = (int)$_POST['reply_id'];
    $reply = trim($_POST['reply_message']);
    
    if (!empty($reply)) {
        $stmt = $conn->prepare("UPDATE messages SET admin_reply = ?, status = 'Replied', replied_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $reply, $id);
        $stmt->execute();
        
        // Optional: Send email notification here
        // mail($to, $subject, $message, $headers);
    }
    header("Location: manage_messages.php");
    exit;
}

$messages = $conn->query("SELECT * FROM messages ORDER BY created_at DESC");
include '../includes/header.php';
?>

<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">User Messages</h2>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-light">
            <tr>
              <th class="ps-4 py-3">From</th>
              <th>Subject</th>
              <th>Message</th>
              <th>Status</th>
              <th>Date</th>
              <th class="text-end pe-4">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($messages->num_rows > 0): ?>
                <?php while($msg = $messages->fetch_assoc()): ?>
                <tr>
                  <td class="ps-4">
                    <div class="fw-bold"><?php echo htmlspecialchars($msg['name']); ?></div>
                    <div class="text-muted small"><?php echo htmlspecialchars($msg['email']); ?></div>
                    <?php if ($msg['user_id']): ?>
                        <span class="badge bg-info text-dark" style="font-size: 0.65rem;">Registered User</span>
                    <?php endif; ?>
                  </td>
                  <td class="fw-bold"><?php echo htmlspecialchars($msg['subject']); ?></td>
                  <td style="max-width: 300px;">
                    <div class="text-truncate"><?php echo htmlspecialchars($msg['message']); ?></div>
                    <button class="btn btn-link btn-sm p-0 text-decoration-none" data-bs-toggle="modal" data-bs-target="#msgModal<?php echo $msg['id']; ?>">
                        View & Reply
                    </button>
                    
                    <!-- Message Modal -->
                    <div class="modal fade" id="msgModal<?php echo $msg['id']; ?>" tabindex="-1">
                      <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title"><?php echo htmlspecialchars($msg['subject']); ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body">
                            <div class="bg-light p-3 rounded mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <strong><?php echo htmlspecialchars($msg['name']); ?></strong>
                                    <small class="text-muted"><?php echo $msg['created_at']; ?></small>
                                </div>
                                <p class="mb-0" style="white-space: pre-wrap;"><?php echo htmlspecialchars($msg['message']); ?></p>
                            </div>

                            <hr>

                            <?php if (!empty($msg['admin_reply'])): ?>
                                <div class="alert alert-success">
                                    <h6 class="alert-heading fw-bold"><i class="bi bi-check-circle-fill"></i> Your Reply:</h6>
                                    <p class="mb-1" style="white-space: pre-wrap;"><?php echo htmlspecialchars($msg['admin_reply']); ?></p>
                                    <hr>
                                    <p class="mb-0 small">Replied on: <?php echo $msg['replied_at']; ?></p>
                                </div>
                            <?php else: ?>
                                <form method="post">
                                    <input type="hidden" name="reply_id" value="<?php echo $msg['id']; ?>">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Write a Reply</label>
                                        <textarea name="reply_message" class="form-control" rows="4" placeholder="Type your response here..." required></textarea>
                                    </div>
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Send Reply</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <?php if ($msg['status'] == 'Replied'): ?>
                        <span class="badge bg-success">Replied</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark">Open</span>
                    <?php endif; ?>
                  </td>
                  <td><?php echo date('M d, Y', strtotime($msg['created_at'])); ?></td>
                  <td class="text-end pe-4">
                    <form method="POST" onsubmit="return confirm('Delete this message?');">
                      <input type="hidden" name="delete_id" value="<?php echo $msg['id']; ?>">
                      <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                  </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">No messages found.</td>
                </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
