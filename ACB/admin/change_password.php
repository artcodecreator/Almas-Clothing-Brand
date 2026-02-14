<?php 
session_start();
include("../includes/db.php");
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$message = "";

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Fetch admin's current hashed password
    $stmt = $conn->prepare("SELECT password FROM admin WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['admin_id']);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();

    // Check current password
    if (!password_verify($current_password, $hashed_password)) {
        $message = "<div class='alert alert-danger'>Current password is incorrect.</div>";
    } elseif ($new_password !== $confirm_password) {
        $message = "<div class='alert alert-warning'>New passwords do not match.</div>";
    } elseif (strlen($new_password) < 6) {
        $message = "<div class='alert alert-warning'>New password must be at least 6 characters.</div>";
    } else {
        // Update with new hashed password
        $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE admin SET password = ? WHERE id = ?");
        $update->bind_param("si", $new_hashed, $_SESSION['admin_id']);
        $update->execute();

        $message = "<div class='alert alert-success'>Password updated successfully.</div>";
    }
}

include '../includes/header.php';
?>

<div class="container py-5">
  <h2 class="text-center mb-4">Change Admin Password</h2>

  <div class="row justify-content-center">
    <div class="col-md-6">
      <?php echo $message; ?>

      <div class="card shadow-sm">
        <div class="card-body">
          <form method="post">
            <div class="mb-3">
              <label class="form-label">Current Password</label>
              <input type="password" name="current_password" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">New Password</label>
              <input type="password" name="new_password" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Confirm New Password</label>
              <input type="password" name="confirm_password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Update Password</button>
          </form>
        </div>
      </div>

      <div class="text-center mt-3">
        <a href="dashboard.php" class="btn btn-secondary btn-sm">
          <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
