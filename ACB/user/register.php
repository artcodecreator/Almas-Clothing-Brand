<?php 
session_start();
include("../includes/db.php");
error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = "";

// If form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);

    if (!empty($name) && !empty($email) && !empty($password)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if email exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
             $message = "<div class='alert alert-danger'>Email already exists. Please login.</div>";
        } else {
            // Insert into DB
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, address, phone) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $hashed_password, $address, $phone);

            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Registration successful! <a href='login.php' class='alert-link'>Click here to login</a>.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error: Could not register.</div>";
            }
        }
    } else {
        $message = "<div class='alert alert-danger'>Please fill in all required fields.</div>";
    }
}

include '../includes/header.php';
?>

<div class="d-flex align-items-center justify-content-center min-vh-100 bg-light py-5" style="margin-top: -80px; padding-top: 120px !important;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5">
        <div class="text-center mb-4">
            <span class="bg-black text-white rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width:60px; height:60px; font-size: 24px;">A</span>
            <h2 class="mt-3 fw-bold font-heading">Create Account</h2>
            <p class="text-muted">Join Almas for exclusive offers & updates</p>
        </div>

        <div class="card border-0 shadow-sm">
          <div class="card-body p-4">
            <?php if ($message) echo $message; ?>

            <form method="post">
              <div class="mb-3">
                <label class="form-label text-muted small fw-bold">Full Name</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                  <input type="text" name="name" class="form-control bg-light border-start-0" placeholder="John Doe" required>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label text-muted small fw-bold">Email Address</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                  <input type="email" name="email" class="form-control bg-light border-start-0" placeholder="name@example.com" required>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label text-muted small fw-bold">Password</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                  <input type="password" name="password" class="form-control bg-light border-start-0" placeholder="Create a password" required>
                </div>
              </div>

              <div class="row mb-3">
                  <div class="col-md-6">
                    <label class="form-label text-muted small fw-bold">Phone</label>
                    <div class="input-group">
                      <span class="input-group-text bg-light border-end-0"><i class="bi bi-telephone text-muted"></i></span>
                      <input type="text" name="phone" class="form-control bg-light border-start-0" placeholder="+123...">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label text-muted small fw-bold">Address</label>
                    <div class="input-group">
                      <span class="input-group-text bg-light border-end-0"><i class="bi bi-geo-alt text-muted"></i></span>
                      <input type="text" name="address" class="form-control bg-light border-start-0" placeholder="City, Country">
                    </div>
                  </div>
              </div>

              <div class="d-grid mb-3">
                <button type="submit" class="btn btn-dark py-2 fw-medium">Register</button>
              </div>
              
              <div class="text-center">
                <p class="text-muted small mb-0">Already have an account? <a href="login.php" class="text-dark fw-bold text-decoration-none">Login</a></p>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
