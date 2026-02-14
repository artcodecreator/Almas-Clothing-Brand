<?php
session_start();
include("../includes/db.php");
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $uploadOk = 1;
    $image_url = null;

    // Handle Image Upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $target_dir = "../assets/uploads/users/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
        $new_filename = "user_" . $user_id . "_" . time() . "." . $file_ext;
        $target_file = $target_dir . $new_filename;
        
        // Check file size (max 2MB)
        if ($_FILES["profile_image"]["size"] > 2000000) {
            $message = "<div class='alert alert-danger'>Sorry, your file is too large. Max 2MB.</div>";
            $uploadOk = 0;
        }
        
        // Allow certain file formats
        if($file_ext != "jpg" && $file_ext != "png" && $file_ext != "jpeg" && $file_ext != "gif" ) {
            $message = "<div class='alert alert-danger'>Sorry, only JPG, JPEG, PNG & GIF files are allowed.</div>";
            $uploadOk = 0;
        }
        
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                // Save path relative to project root for consistency with other images
                $image_url = "/ACB/assets/uploads/users/" . $new_filename;
            } else {
                $message = "<div class='alert alert-danger'>Sorry, there was an error uploading your file.</div>";
            }
        }
    }

    if ($name !== '') {
        if ($image_url) {
            $stmt = $conn->prepare("UPDATE users SET name = ?, address = ?, phone = ?, profile_image = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $name, $address, $phone, $image_url, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name = ?, address = ?, phone = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $address, $phone, $user_id);
        }

        if ($stmt->execute()) {
            $_SESSION['user_name'] = $name;
            $message = "<div class='alert alert-success'>Profile updated successfully.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Failed to update profile.</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>Name is required.</div>";
    }
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user) { echo "User not found."; exit; }

include '../includes/header.php';
?>
<div class="container py-5">
  <h2 class="text-center mb-4">Edit Profile</h2>
  <?php echo $message; ?>
  <div class="row justify-content-center">
    <div class="col-md-8">
      <form method="post" enctype="multipart/form-data" class="border p-4 rounded bg-light shadow">
        
        <div class="text-center mb-4">
            <?php 
            $imgSrc = !empty($user['profile_image']) ? $user['profile_image'] : "https://ui-avatars.com/api/?name=".urlencode($user['name'])."&background=random";
            ?>
            <img src="<?php echo $imgSrc; ?>" class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;" alt="Profile">
            <div class="mb-3">
                <label for="profile_image" class="form-label d-block fw-bold text-muted small">Change Profile Picture</label>
                <input type="file" name="profile_image" id="profile_image" class="form-control form-control-sm mx-auto" style="max-width: 300px;">
            </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Name</label>
          <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
          <div class="form-text">Email cannot be changed.</div>
        </div>
        <div class="mb-3">
          <label class="form-label">Address</label>
          <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Phone</label>
          <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
        </div>
        <div class="text-end">
          <a href="profile.php" class="btn btn-secondary">Cancel</a>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
