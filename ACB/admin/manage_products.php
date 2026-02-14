<?php 
session_start();

include("../includes/db.php");
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $conn->query("DELETE FROM products WHERE id=$delete_id");
    header("Location: manage_products.php");
    exit;
}

// Handle Add Product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $image = '';

if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
    $target_dir = "../assets/images/";
    $image = basename($_FILES["image_file"]["name"]);
    $target_file = $target_dir . $image;

    // Optionally add file type validation
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowed)) {
        die("Sorry, only JPG, JPEG, PNG & GIF files are allowed.");
    }

    // Move uploaded file
    if (!move_uploaded_file($_FILES["image_file"]["tmp_name"], $target_file)) {
        die("Sorry, there was an error uploading your file.");
    }
}


    $cat_id = NULL;
    $cat_stmt = $conn->prepare("SELECT id FROM categories WHERE name = ?");
    $cat_stmt->bind_param("s", $category);
    $cat_stmt->execute();
    $cat_res = $cat_stmt->get_result()->fetch_assoc();
    if ($cat_res) { $cat_id = (int)$cat_res['id']; }

    $image_url = '';
    if (!empty($image)) {
        $image_url = '/AlmasClothingBrand/assets/images/' . $image;
    }

    $stmt = $conn->prepare("INSERT INTO products (category_id, name, description, price, stock, image_url, status) VALUES (?, ?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("issdis", $cat_id, $name, $description, $price, $stock, $image_url);
    $stmt->execute();

    header("Location: manage_products.php");
    exit;
}

// Include header
include '../includes/header.php';
?>
<!-- bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Manage Products</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
      <i class="bi bi-plus-lg"></i> Add New Product
    </button>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-light">
            <tr>
              <th class="ps-4 py-3">ID</th>
              <th>Name</th>
              <th>Category</th>
              <th>Price</th>
              <th>Stock</th>
              <th>Featured</th>
              <th>Image</th>
              <th class="text-end pe-4">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $result = $conn->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id ORDER BY p.id DESC");
            if ($result->num_rows > 0):
              while ($row = $result->fetch_assoc()):
            ?>
            <tr>
              <td class="ps-4 fw-bold">#<?php echo $row['id']; ?></td>
              <td class="fw-bold"><?php echo htmlspecialchars($row['name']); ?></td>
              <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['category_name'] ?? 'Uncategorized'); ?></span></td>
              <td class="fw-bold">PKR <?php echo number_format((float)$row['price'], 2); ?></td>
              <td><?php echo $row['stock']; ?></td>
              <td>
                 <?php if($row['is_featured']): ?>
                    <span class="badge bg-warning text-dark"><i class="bi bi-star-fill"></i> Featured</span>
                 <?php else: ?>
                    <span class="text-muted small">No</span>
                 <?php endif; ?>
              </td>
              <td>
                <img src="<?php echo htmlspecialchars(image_or_placeholder((string)($row['image_url'] ?? ''))); ?>" width="50" height="50" alt="Product" class="rounded" style="object-fit:cover">
              </td>
              <td class="text-end pe-4">
                <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
                <a href="manage_products.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this product?');"><i class="bi bi-trash"></i></a>
              </td>
            </tr>
            <?php
              endwhile;
            else:
            ?>
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">No products found.</td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" action="" enctype="multipart/form-data" class="modal-content">
      <input type="hidden" name="add_product" value="1">

      <div class="modal-header">
        <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Product Name</label>
          <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" required></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Category</label>
          <select name="category" class="form-select" required>
            <option value="">Select Category</option>
            <option value="Men">Men</option>
            <option value="Women">Women</option>
            <option value="Kids">Kids</option>
            <option value="Accessories">Accessories</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Price (PKR)</label>
          <input type="number" step="0.01" name="price" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Stock</label>
          <input type="number" name="stock" class="form-control" required>
        </div>
        <div class="mb-3 form-check">
          <input type="checkbox" name="is_featured" class="form-check-input" id="featuredCheck">
          <label class="form-check-label" for="featuredCheck">Featured Product</label>
        </div>
        <div class="mb-3">
          <label class="form-label">Image Filename</label>
         <input type="file" name="image_file" class="form-control" required>
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Add Product</button>
      </div>
    </form>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
