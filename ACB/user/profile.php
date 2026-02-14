<?php 
session_start();
include("../includes/db.php");
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Protect: check if logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    echo "User not found.";
    exit;
}

$user = $result->fetch_assoc();

include '../includes/header.php';
?>


<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">


<!-- Profile Page -->
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card shadow-lg border-0">
        <div class="card-header bg-gradient text-succees text-center" style="background: linear-gradient(45deg, #007bff, #00c6ff);">
          <?php 
          $imgSrc = !empty($user['profile_image']) ? $user['profile_image'] : "https://ui-avatars.com/api/?name=".urlencode($user['name'])."&background=random";
          ?>
          <img src="<?php echo $imgSrc; ?>" class="rounded-circle mb-3 border border-3 border-white shadow-sm" style="width: 100px; height: 100px; object-fit: cover;" alt="Profile">
          <h3 class="mb-0 text-white">Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h3>
          <small class="text-white-50">Your personal dashboard</small>
        </div>
        <div class="card-body">
          <div class="row mb-3">
            <div class="col-4 text-end fw-bold">
              <i class="bi bi-person-fill"></i> Name:
            </div>
            <div class="col-8">
              <?php echo htmlspecialchars((string)($user['name'] ?? '')); ?>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-4 text-end fw-bold">
              <i class="bi bi-envelope-fill"></i> Email:
            </div>
            <div class="col-8">
              <?php echo htmlspecialchars((string)($user['email'] ?? '')); ?>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-4 text-end fw-bold">
              <i class="bi bi-geo-alt-fill"></i> Address:
            </div>
            <div class="col-8">
              <?php echo htmlspecialchars((string)($user['address'] ?? '')); ?>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-4 text-end fw-bold">
              <i class="bi bi-telephone-fill"></i> Phone:
            </div>
            <div class="col-8">
              <?php echo htmlspecialchars((string)($user['phone'] ?? '')); ?>
            </div>
          </div>
        </div>
        <div class="card-footer bg-light text-end">
          <a href="logout.php" class="btn btn-outline-danger">
            <i class="bi bi-box-arrow-right"></i> Logout
          </a>
          <a href="<?php echo base_url('user/edit_profile.php'); ?>" class="btn btn-outline-primary">
            <i class="bi bi-pencil-square"></i> Edit Profile
        </a>

        </div>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
