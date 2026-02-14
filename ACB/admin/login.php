<?php
session_start();
include("../includes/db.php");

// If admin already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM admin WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if (!$result) {
        $colCheck = $conn->prepare("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'admin' AND COLUMN_NAME = 'username'");
        $colCheck->execute();
        $colRes = $colCheck->get_result()->fetch_assoc();
        if ($colRes && (int)$colRes['cnt'] > 0) {
            $stmt2 = $conn->prepare("SELECT * FROM admin WHERE username = ?");
            $stmt2->bind_param("s", $email);
            $stmt2->execute();
            $result = $stmt2->get_result()->fetch_assoc();
        }
    }

    if ($result && password_verify($password, $result['password'])) {
        $_SESSION['admin_id'] = $result['id'];
        $_SESSION['admin_email'] = isset($result['email']) ? $result['email'] : '';
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="d-flex align-items-center justify-content-center min-vh-100 bg-light py-5" style="margin-top: -80px; padding-top: 120px !important;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-5 col-lg-4">
        <div class="text-center mb-4">
            <span class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width:60px; height:60px; font-size: 24px;"><i class="bi bi-shield-lock"></i></span>
            <h2 class="mt-3 fw-bold font-heading">Admin Portal</h2>
            <p class="text-muted">Secure access only</p>
        </div>

        <div class="card border-0 shadow-sm">
          <div class="card-body p-4">
            <?php if (!empty($error)): ?>
              <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-octagon-fill me-2"></i>
                <div><?php echo $error; ?></div>
              </div>
            <?php endif; ?>

            <form method="post" action="">
              <div class="mb-3">
                <label class="form-label text-muted small fw-bold">Email / Username</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0"><i class="bi bi-person-badge text-muted"></i></span>
                  <input type="text" name="email" class="form-control bg-light border-start-0" required autofocus>
                </div>
              </div>

              <div class="mb-4">
                <label class="form-label text-muted small fw-bold">Password</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0"><i class="bi bi-key text-muted"></i></span>
                  <input type="password" name="password" class="form-control bg-light border-start-0" required>
                </div>
              </div>

              <div class="d-grid">
                <button type="submit" class="btn btn-primary py-2 fw-medium">Access Dashboard</button>
              </div>
            </form>
          </div>
          <div class="card-footer bg-white border-0 text-center py-3">
            <a href="<?php echo base_url('index.php'); ?>" class="text-muted small text-decoration-none"><i class="bi bi-arrow-left me-1"></i> Back to Store</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
