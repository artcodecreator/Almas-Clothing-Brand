<?php 
session_start();
include("../includes/db.php");
include '../includes/header.php';

$cart = $_SESSION['cart'] ?? [];
$total = 0;
?>

<div class="container py-5">
  <div class="row">
    <div class="col-12">
      <h2 class="fw-bold mb-4">Shopping Cart</h2>
    </div>
  </div>

  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  
  <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if (!empty($cart)): ?>
    <div class="row">
      <!-- Cart Items -->
      <div class="col-lg-8">
        <form method="post" action="update_cart.php" id="cartForm">
          <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="bg-light">
                    <tr>
                      <th class="ps-4 py-3">Product</th>
                      <th class="py-3">Price</th>
                      <th class="py-3">Quantity</th>
                      <th class="py-3 text-end pe-4">Subtotal</th>
                      <th class="py-3"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($cart as $product_id => $quantity): ?>
                      <?php
                        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
                        $stmt->bind_param("i", $product_id);
                        $stmt->execute();
                        $product = $stmt->get_result()->fetch_assoc();
                        if (!$product) continue;

                        $subtotal = $product['price'] * $quantity;
                        $total += $subtotal;
                      ?>
                      <tr>
                        <td class="ps-4 py-3">
                          <div class="d-flex align-items-center">
                            <img src="<?php echo htmlspecialchars(image_or_placeholder((string)($product['image_url'] ?? ''))); ?>" class="rounded" width="60" height="60" style="object-fit:cover;">
                            <div class="ms-3">
                              <h6 class="mb-0 fw-semibold"><a href="product_details.php?id=<?php echo $product['id']; ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($product['name']); ?></a></h6>
                            </div>
                          </div>
                        </td>
                        <td>PKR <?php echo number_format((float)$product['price'], 0); ?></td>
                        <td style="width: 120px;">
                          <input type="number" name="quantities[<?php echo $product_id; ?>]" value="<?php echo $quantity; ?>" min="1" max="<?php echo $product['stock']; ?>" class="form-control form-control-sm text-center">
                        </td>
                        <td class="text-end pe-4 fw-semibold">PKR <?php echo number_format((float)$subtotal, 0); ?></td>
                        <td class="text-end">
                          <a href="remove_from_cart.php?id=<?php echo $product_id; ?>" class="text-danger"><i class="bi bi-trash"></i></a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="card-footer bg-white py-3">
              <div class="d-flex justify-content-between align-items-center">
                <a href="shop.php" class="btn btn-outline-dark"><i class="bi bi-arrow-left me-2"></i> Continue Shopping</a>
                <button type="submit" class="btn btn-dark">Update Cart</button>
              </div>
            </div>
          </div>
        </form>
      </div>

      <!-- Summary -->
      <div class="col-lg-4">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <h5 class="fw-bold mb-4">Order Summary</h5>
            
            <div class="d-flex justify-content-between mb-2">
              <span class="text-muted">Subtotal</span>
              <span class="fw-semibold">PKR <?php echo number_format($total, 0); ?></span>
            </div>
            
            <?php 
            $discount = 0;
            if (isset($_SESSION['coupon'])) {
                $c_type = $_SESSION['coupon']['type'];
                $c_val = $_SESSION['coupon']['value'];
                
                if ($c_type == 'percent') {
                    $discount = ($total * $c_val) / 100;
                } else {
                    $discount = $c_val;
                }
                // Ensure discount doesn't exceed total
                if ($discount > $total) $discount = $total;
            }
            $final_total = $total - $discount;
            ?>

            <?php if (isset($_SESSION['coupon'])): ?>
              <div class="d-flex justify-content-between mb-2 text-success">
                <span>Discount (<?php echo $_SESSION['coupon']['code']; ?>)</span>
                <span>- PKR <?php echo number_format($discount, 0); ?></span>
              </div>
              <div class="mb-3">
                <span class="badge bg-success">
                  <?php echo $_SESSION['coupon']['code']; ?> Applied
                  <form action="apply_coupon.php" method="POST" class="d-inline">
                    <input type="hidden" name="action" value="remove">
                    <button type="submit" class="btn-close btn-close-white ms-2" style="font-size: 0.5em;"></button>
                  </form>
                </span>
              </div>
            <?php else: ?>
              <form action="apply_coupon.php" method="POST" class="mb-3 mt-3">
                <div class="input-group">
                  <input type="text" name="coupon_code" class="form-control" placeholder="Coupon Code">
                  <button class="btn btn-outline-secondary" type="submit">Apply</button>
                </div>
              </form>
            <?php endif; ?>

            <hr>
            
            <div class="d-flex justify-content-between mb-4">
              <span class="fw-bold fs-5">Total</span>
              <span class="fw-bold fs-5">PKR <?php echo number_format($final_total, 0); ?></span>
            </div>
            
            <a href="checkout.php" class="btn btn-primary w-100 py-2">Proceed to Checkout</a>
          </div>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="text-center py-5 bg-light rounded-3">
      <i class="bi bi-cart-x display-1 text-muted"></i>
      <h4 class="mt-3">Your cart is empty</h4>
      <p class="text-muted mb-4">Looks like you haven't added anything to your cart yet.</p>
      <a href="shop.php" class="btn btn-primary">Start Shopping</a>
    </div>
  <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
