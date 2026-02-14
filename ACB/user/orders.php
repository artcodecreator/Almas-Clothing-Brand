<?php 
session_start();
include("../includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch orders grouped by order_id
// We'll fetch all items and group in PHP to avoid complex group_concat handling
$stmt = $conn->prepare("
    SELECT o.id as order_id, o.created_at, o.status, o.final_total, o.subtotal, o.discount_amount,
           o.courier_name, o.tracking_number,
           oi.product_id, oi.product_name, oi.quantity, oi.unit_price, p.image_url
    FROM orders o
    JOIN order_items oi ON oi.order_id = o.id
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[$row['order_id']]['details'] = [
        'id' => $row['order_id'],
        'date' => $row['created_at'],
        'status' => $row['status'],
        'total' => $row['final_total'] ?? $row['subtotal'], // Fallback if final_total null
        'courier' => $row['courier_name'],
        'tracking' => $row['tracking_number']
    ];
    $orders[$row['order_id']]['items'][] = $row;
}

include '../includes/header.php';
?>

<div class="container py-5">
  <h2 class="text-center fw-bold mb-5">My Orders & Tracking</h2>

  <?php if (empty($orders)): ?>
    <div class="text-center py-5 bg-light rounded-3">
      <i class="bi bi-bag-x display-1 text-muted"></i>
      <h4 class="mt-3">No orders found</h4>
      <p class="text-muted mb-4">You haven't placed any orders yet.</p>
      <a href="shop.php" class="btn btn-primary">Start Shopping</a>
    </div>
  <?php else: ?>
    
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <?php foreach ($orders as $order_id => $data): 
            $status = $data['details']['status'];
            // Determine progress width
            $progress = 0;
            $class = 'bg-primary';
            if ($status == 'Pending') $progress = 25;
            elseif ($status == 'Processing') $progress = 50;
            elseif ($status == 'Shipped') $progress = 75;
            elseif ($status == 'Delivered') { $progress = 100; $class = 'bg-success'; }
            elseif ($status == 'Cancelled') { $progress = 100; $class = 'bg-danger'; }
        ?>
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h5 class="mb-0 fw-bold">Order #<?php echo $order_id; ?></h5>
                    <small class="text-muted">Placed on <?php echo date("M d, Y", strtotime($data['details']['date'])); ?></small>
                </div>
                <div class="text-end">
                    <h5 class="mb-0 fw-bold text-primary">PKR <?php echo number_format($data['details']['total'], 2); ?></h5>
                    <span class="badge bg-<?php echo ($status == 'Cancelled') ? 'danger' : (($status == 'Delivered') ? 'success' : 'warning'); ?>">
                        <?php echo $status; ?>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <!-- Tracking Timeline -->
                <?php if($status != 'Cancelled'): ?>
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Order Status</label>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar <?php echo $class; ?>" role="progressbar" style="width: <?php echo $progress; ?>%"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-2 small text-muted">
                        <span>Placed</span>
                        <span>Processing</span>
                        <span>Shipped</span>
                        <span>Delivered</span>
                    </div>
                </div>
                <?php else: ?>
                    <div class="alert alert-danger py-2">This order has been cancelled.</div>
                <?php endif; ?>

                <?php if (!empty($data['details']['tracking'])): ?>
                    <div class="mb-4 p-3 bg-light rounded border">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <div class="bg-white p-2 rounded-circle border">
                                <i class="bi bi-truck fs-4 text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold">Shipped via <?php echo htmlspecialchars($data['details']['courier']); ?></h6>
                                <p class="mb-0 text-muted small">Tracking #: <span class="fw-bold text-dark"><?php echo htmlspecialchars($data['details']['tracking']); ?></span></p>
                            </div>
                            <div class="ms-auto">
                                <a href="track_order.php?tracking=<?php echo urlencode($data['details']['tracking']); ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    Track Shipment <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Items -->
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['items'] as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo htmlspecialchars(image_or_placeholder((string)($item['image_url'] ?? ''))); ?>" width="50" height="50" class="rounded me-3" style="object-fit:cover">
                                        <span class="fw-semibold"><?php echo htmlspecialchars($item['product_name']); ?></span>
                                    </div>
                                </td>
                                <td>PKR <?php echo number_format($item['unit_price'], 2); ?></td>
                                <td>x<?php echo $item['quantity']; ?></td>
                                <td class="text-end">PKR <?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

  <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
