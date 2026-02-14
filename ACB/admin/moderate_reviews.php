<?php 
session_start();
include("../includes/db.php");
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Secure admin-only page
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

//  Handle Approve/Unapprove
if (isset($_GET['toggle'])) {
    $review_id = intval($_GET['toggle']);
    $result = $conn->query("SELECT is_approved FROM review WHERE id=$review_id");
    if ($result && $result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $new_status = $row['is_approved'] ? 0 : 1;
        $conn->query("UPDATE review SET is_approved=$new_status WHERE id=$review_id");
    }
    header("Location: moderate_reviews.php");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $conn->query("DELETE FROM review WHERE id=$delete_id");
    header("Location: moderate_reviews.php");
    exit;
}

// Fetch all reviews with user and product details
$sql = "
  SELECT r.id, r.comment, r.rating, r.is_approved, r.created_at,
         u.name AS user_name,
         p.name AS product_name
  FROM review r
  JOIN users u ON r.user_id = u.id
  JOIN products p ON r.product_id = p.id
  ORDER BY r.id DESC
";
$result = $conn->query($sql);
?>

<?php include '../includes/header.php'; ?>
<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Moderate Reviews</h2>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-light">
            <tr>
              <th class="ps-4 py-3">ID</th>
              <th>User</th>
              <th>Product</th>
              <th>Rating</th>
              <th>Comment</th>
              <th>Status</th>
              <th>Date</th>
              <th class="text-end pe-4">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result->num_rows > 0): ?>
              <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td class="ps-4 fw-bold">#<?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                <td>
                  <span class="text-warning">
                    <?php for($i=1; $i<=5; $i++) echo ($i <= $row['rating']) ? '★' : '☆'; ?>
                  </span>
                </td>
                <td><small class="text-muted"><?php echo nl2br(htmlspecialchars($row['comment'])); ?></small></td>
                <td>
                  <?php echo $row['is_approved'] ? '<span class="badge bg-success">Approved</span>' : '<span class="badge bg-warning text-dark">Pending</span>'; ?>
                </td>
                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                <td class="text-end pe-4">
                  <a href="moderate_reviews.php?toggle=<?php echo $row['id']; ?>" 
                     class="btn btn-sm <?php echo $row['is_approved'] ? 'btn-outline-warning' : 'btn-outline-success'; ?> me-1">
                     <?php echo $row['is_approved'] ? '<i class="bi bi-x-circle"></i>' : '<i class="bi bi-check-circle"></i>'; ?>
                  </a>
                  <a href="moderate_reviews.php?delete=<?php echo $row['id']; ?>" 
                     class="btn btn-sm btn-outline-danger"
                     onclick="return confirm('Are you sure you want to delete this review?');">
                     <i class="bi bi-trash"></i>
                  </a>
                </td>
              </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" class="text-center py-4 text-muted">No reviews found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
