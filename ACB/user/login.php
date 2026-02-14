<?php 
session_start();
include("../includes/db.php");
error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = "";

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: profile.php");
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                header("Location: profile.php");
                exit;
            } else {
                $message = "Invalid email or password.";
            }
        } else {
            $message = "Invalid email or password.";
        }
    } else {
        $message = "Please fill in all fields.";
    }
}

include '../includes/header.php';
?>

<div class="d-flex align-items-center justify-content-center min-vh-100 bg-light py-5" style="margin-top: -80px; padding-top: 120px !important;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-5 col-lg-4">
        <div class="text-center mb-4">
            <span class="bg-black text-white rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width:60px; height:60px; font-size: 24px;">A</span>
            <h2 class="mt-3 fw-bold font-heading">Welcome Back</h2>
            <p class="text-muted">Sign in to continue shopping</p>
        </div>

        <div class="card border-0 shadow-sm">
          <div class="card-body p-4">
            <?php if ($message): ?>
              <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2"></i>
                <div><?php echo $message; ?></div>
              </div>
            <?php endif; ?>

            <form method="post">
              <div class="mb-3">
                <label class="form-label text-muted small fw-bold">Email Address</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                  <input type="email" name="email" class="form-control bg-light border-start-0" placeholder="name@example.com" required>
                </div>
              </div>
              
              <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label text-muted small fw-bold mb-0">Password</label>
                    <a href="#" class="small text-decoration-none">Forgot password?</a>
                </div>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                  <input type="password" name="password" class="form-control bg-light border-start-0" placeholder="Enter your password" required>
                </div>
              </div>

              <div class="d-grid mb-3">
                <button type="submit" class="btn btn-dark py-2 fw-medium">Sign In</button>
              </div>
              
              <div class="text-center">
                <p class="text-muted small mb-0">Don't have an account? <a href="register.php" class="text-dark fw-bold text-decoration-none">Create Account</a></p>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
