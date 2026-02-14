<?php 
session_start();
include("../includes/db.php");
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Secure admin-only page
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$order_id = intval($_GET['id'] ?? 0);
if ($order_id < 1) {
    header("Location: manage_orders.php");
    exit;
}

// Fetch Order Info
$order_stmt = $conn->prepare("
    SELECT o.*, u.name AS user_name, u.email AS user_email 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    WHERE o.id = ?
");
$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$order = $order_stmt->get_result()->fetch_assoc();

if (!$order) {
    echo "Order not found.";
    exit;
}

// Fetch Order Items
$items_stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items = $items_stmt->get_result();

include '../includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Order #<?php echo $order['id']; ?> Details</h2>
        <a href="manage_orders.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Orders</a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Order Items</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th class="text-end pe-4">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($item = $items->fetch_assoc()): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                        </td>
                                        <td>PKR <?php echo number_format($item['unit_price'], 2); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td class="text-end pe-4 fw-bold">PKR <?php echo number_format($item['total_price'], 2); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Subtotal:</td>
                                    <td class="text-end pe-4 fw-bold">PKR <?php echo number_format($order['subtotal'], 2); ?></td>
                                </tr>
                                <?php if (!empty($order['coupon_code']) && $order['discount_amount'] > 0): ?>
                                <tr>
                                    <td colspan="3" class="text-end text-success">Discount (<?php echo htmlspecialchars($order['coupon_code']); ?>):</td>
                                    <td class="text-end pe-4 text-success">- PKR <?php echo number_format($order['discount_amount'], 2); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Total:</td>
                                    <td class="text-end pe-4 fw-bold">PKR <?php echo number_format($order['final_total'], 2); ?></td>
                                </tr>
                                <?php endif; ?>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Customer Info</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                    <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                    <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                    <hr>
                    <p class="mb-1"><strong>Shipping Address:</strong></p>
                    <p class="text-muted mb-0"><?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
                    <p class="text-muted"><?php echo htmlspecialchars($order['city']); ?></p>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Order Status</h5>
                </div>
                <div class="card-body">
                    <form action="manage_orders.php" method="post">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Current Status</label>
                            <select name="status" class="form-select" id="detail_status" onchange="toggleDetailTracking()">
                                <option value="Pending" <?php if ($order['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                                <option value="Shipped" <?php if ($order['status'] == 'Shipped') echo 'selected'; ?>>Shipped</option>
                                <option value="Delivered" <?php if ($order['status'] == 'Delivered') echo 'selected'; ?>>Delivered</option>
                                <option value="Canceled" <?php if ($order['status'] == 'Canceled') echo 'selected'; ?>>Canceled</option>
                            </select>
                        </div>
                        
                        <div id="detail_tracking_fields" style="display: <?php echo ($order['status'] == 'Shipped' || $order['status'] == 'Delivered') ? 'block' : 'none'; ?>;">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Courier Name</label>
                                <select name="courier_name" class="form-select">
                                    <option value="">Select Courier</option>
                                    <?php
                                    $couriers = $conn->query("SELECT name FROM couriers WHERE is_active = 1 ORDER BY name ASC");
                                    $courier_list = [];
                                    while($c = $couriers->fetch_assoc()):
                                        $courier_list[] = $c['name'];
                                    ?>
                                        <option value="<?php echo htmlspecialchars($c['name']); ?>" <?php if (($order['courier_name'] ?? '') == $c['name']) echo 'selected'; ?>><?php echo htmlspecialchars($c['name']); ?></option>
                                    <?php endwhile; ?>
                                    <option value="Other" <?php if (!in_array(($order['courier_name'] ?? ''), $courier_list) && !empty($order['courier_name'])) echo 'selected'; ?>>Other</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small">Tracking Number</label>
                                <input type="text" name="tracking_number" class="form-control" value="<?php echo htmlspecialchars($order['tracking_number'] ?? ''); ?>" placeholder="e.g. 123456789">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Update Status</button>
                    </form>
                    <div class="mt-3 text-center">
                        <small class="text-muted">Order Date: <?php echo date("M d, Y H:i", strtotime($order['created_at'])); ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleDetailTracking() {
    const status = document.getElementById('detail_status').value;
    const fields = document.getElementById('detail_tracking_fields');
    if (status === 'Shipped' || status === 'Delivered') {
        fields.style.display = 'block';
    } else {
        fields.style.display = 'none';
    }
}
</script>

<?php include '../includes/footer.php'; ?>
