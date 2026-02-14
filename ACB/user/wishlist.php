<?php
session_start();
include("../includes/db.php");
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT p.*, w.created_at as added_at FROM wishlist w JOIN products p ON w.product_id = p.id WHERE w.user_id = ? ORDER BY w.created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">My Wishlist</h2>
    <a href="shop.php" class="btn btn-outline-dark">Continue Shopping</a>
  </div>

  <?php if ($result->num_rows > 0): ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
      <?php while ($product = $result->fetch_assoc()): ?>
        <div class="col">
          <div class="card h-100 product-card shadow-sm">
            <a href="product_details.php?id=<?php echo $product['id']; ?>" class="text-decoration-none">
              <img src="<?php echo htmlspecialchars(image_or_placeholder((string)($product['image_url'] ?? ''))); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
            </a>
            <div class="card-body d-flex flex-column">
              <h5 class="card-title text-dark fw-bold mb-1">
                <a href="product_details.php?id=<?php echo $product['id']; ?>" class="text-decoration-none text-dark">
                  <?php echo htmlspecialchars($product['name']); ?>
                </a>
              </h5>
              <div class="mb-3">
                <span class="text-primary fw-bold">PKR <?php echo number_format($product['price']); ?></span>
              </div>
              
              <div class="mt-auto d-flex gap-2">
                <form action="add_to_cart.php" method="post" class="flex-grow-1">
                  <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                  <input type="hidden" name="quantity" value="1">
                  <button type="submit" class="btn btn-dark btn-sm w-100">Add to Cart</button>
                </form>
                <form action="wishlist_action.php" method="post">
                  <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                  <input type="hidden" name="action" value="remove">
                  <button type="submit" class="btn btn-outline-danger btn-sm" title="Remove">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <div class="text-center py-5 bg-light rounded-3">
      <i class="bi bi-heart display-1 text-muted"></i>
      <h4 class="mt-3">Your wishlist is empty</h4>
      <p class="text-muted">Save items you love to revisit later.</p>
      <a href="shop.php" class="btn btn-primary mt-3">Explore Products</a>
    </div>
  <?php endif; ?>
</div>

<?php include("../includes/footer.php"); ?>
