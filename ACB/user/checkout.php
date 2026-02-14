<?php 
session_start();
include("../includes/db.php");
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Protect: user must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get cart from session
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
if (empty($cart)) {
    header("Location: cart.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $address = trim((string)($_POST['address'] ?? ''));
    $city = trim((string)($_POST['city'] ?? ''));
    if ($name !== '' && $email !== '' && $address !== '' && $city !== '' && !empty($cart)) {
        $total = 0;
        foreach ($cart as $pid => $qty) {
            $stmtP = $conn->prepare("SELECT name, price FROM products WHERE id = ?");
            $stmtP->bind_param("i", $pid);
            $stmtP->execute();
            $resP = $stmtP->get_result()->fetch_assoc();
            if (!$resP) continue;
            $total += ((float)$resP['price']) * (int)$qty;
        }
        $stmtO = $conn->prepare("INSERT INTO orders (user_id, customer_name, email, phone, address, city, subtotal, coupon_code, discount_amount, final_total, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())");
        
        $coupon_code = isset($_SESSION['coupon']) ? $_SESSION['coupon']['code'] : NULL;
        $discount_amount = 0;
        
        if ($coupon_code) {
             $c_type = $_SESSION['coupon']['type'];
             $c_val = $_SESSION['coupon']['value'];
             if ($c_type == 'percent') {
                $discount_amount = ($total * $c_val) / 100;
             } else {
                $discount_amount = $c_val;
             }
             if ($discount_amount > $total) $discount_amount = $total;
             
             // Update coupon usage
             $conn->query("UPDATE coupons SET used_count = used_count + 1 WHERE code = '$coupon_code'");
        }
        
        $final_total = $total - $discount_amount;

        $stmtO->bind_param("isssssdsdd", $user_id, $name, $email, $phone, $address, $city, $total, $coupon_code, $discount_amount, $final_total);
        $stmtO->execute();
        $order_id = $stmtO->insert_id;
        
        // Clear coupon
        unset($_SESSION['coupon']);

        foreach ($cart as $pid => $qty) {
            $stmtP = $conn->prepare("SELECT name, price FROM products WHERE id = ?");
            $stmtP->bind_param("i", $pid);
            $stmtP->execute();
            $prod = $stmtP->get_result()->fetch_assoc();
            if (!$prod) continue;
            $pname = $prod['name'];
            $price = (float)$prod['price'];
            $line = $price * (int)$qty;
            $stmtI = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, total_price) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtI->bind_param("iisdid", $order_id, $pid, $pname, $price, $qty, $line);
            $stmtI->execute();
        }
        unset($_SESSION['cart']);
        $message = "<div class='alert alert-success'>Your order has been placed successfully! Order #" . $order_id . "</div>";
    }
}

include '../includes/header.php';
?>

<div class="container py-5">
  <h2 class="text-center mb-4">Checkout</h2>

  <?php
  if ($message) {
      echo $message;
  } else if (!empty($cart)) { 
  ?>

  <div class="row">
    <div class="col-md-7">
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">Your Cart Summary</h5>
        </div>
        <div class="card-body">
          <ul class="list-group">
            <?php
            $total = 0;
            foreach ($cart as $product_id => $quantity) {
                $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $product = $stmt->get_result()->fetch_assoc();
                if (!$product) continue;

                $subtotal = $product['price'] * $quantity;
                $total += $subtotal;
            ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>
                  <?php echo htmlspecialchars($product['name']); ?> (x<?php echo $quantity; ?>)
                </span>
                <span>PKR <?php echo number_format((float)$subtotal, 2); ?></span>
              </li>
            <?php } ?>
            <li class="list-group-item d-flex justify-content-between fw-bold">
              <span>Subtotal:</span>
              <span>PKR <?php echo number_format((float)$total, 2); ?></span>
            </li>
            <?php if(isset($_SESSION['coupon'])): 
                 $c_type = $_SESSION['coupon']['type'];
                 $c_val = $_SESSION['coupon']['value'];
                 $discount = ($c_type == 'percent') ? ($total * $c_val / 100) : $c_val;
                 if($discount > $total) $discount = $total;
            ?>
            <li class="list-group-item d-flex justify-content-between text-success">
              <span>Discount (<?php echo $_SESSION['coupon']['code']; ?>):</span>
              <span>- PKR <?php echo number_format($discount, 2); ?></span>
            </li>
            <li class="list-group-item d-flex justify-content-between fw-bold fs-5">
              <span>Total:</span>
              <span>PKR <?php echo number_format($total - $discount, 2); ?></span>
            </li>
            <?php else: ?>
            <li class="list-group-item d-flex justify-content-between fw-bold fs-5">
              <span>Total:</span>
              <span>PKR <?php echo number_format((float)$total, 2); ?></span>
            </li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </div>

    <div class="col-md-5">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-success text-white">
          <h5 class="mb-0">Shipping Details</h5>
        </div>
        <div class="card-body">
          <form method="post">
            <div class="mb-3">
              <label class="form-label">Full Name</label>
              <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Phone</label>
              <input type="text" name="phone" class="form-control">
            </div>
            <div class="mb-3">
              <label class="form-label">Address</label>
              <textarea name="address" class="form-control" rows="3" required></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">City</label>
              <input type="text" name="city" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Place Order</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <?php 
  } else { 
  ?>
    <div class="alert alert-info text-center">
      Your cart is empty. <a href="shop.php" class="btn btn-outline-primary btn-sm">Shop Now</a>
    </div>
  <?php } ?>
</div>

<?php include '../includes/footer.php'; ?>
