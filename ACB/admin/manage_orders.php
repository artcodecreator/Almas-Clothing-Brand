<?php 
session_start();
include("../includes/db.php");
error_reporting(E_ALL);
ini_set('display_errors', 1);

//Check if admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

//Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id = intval($_POST['order_id']);
    $status = trim($_POST['status']);
    $courier = !empty($_POST['courier_name']) ? trim($_POST['courier_name']) : NULL;
    $tracking = !empty($_POST['tracking_number']) ? trim($_POST['tracking_number']) : NULL;

    $update = $conn->prepare("UPDATE orders SET status = ?, courier_name = ?, tracking_number = ? WHERE id = ?");
    $update->bind_param("sssi", $status, $courier, $tracking, $order_id);
    $update->execute();

    header("Location: manage_orders.php");
    exit;
}

//Fetch all orders
$query = "
    SELECT o.id, o.created_at AS order_date, o.status, o.courier_name, o.tracking_number,
           u.name AS user_name, u.email AS user_email,
           COUNT(oi.id) AS items_count,
           SUM(oi.total_price) AS order_total
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    LEFT JOIN order_items oi ON oi.order_id = o.id
    GROUP BY o.id, o.created_at, o.status, u.name, u.email
    ORDER BY o.created_at DESC
";
$result = $conn->query($query);

include '../includes/header.php';
?>

<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Manage Orders</h2>
  </div>

  <?php if ($result->num_rows > 0): ?>
    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
              <tr>
                <th class="ps-4 py-3">ID</th>
                <th>User</th>
                <th>Items</th>
                <th>Total</th>
                <th>Order Date</th>
                <th>Status</th>
                <th class="text-end pe-4">Update Status</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td class="ps-4 fw-bold">#<?php echo $row['id']; ?></td>
                  <td>
                    <div class="fw-bold"><?php echo htmlspecialchars($row['user_name']); ?></div>
                    <div class="text-muted small"><?php echo htmlspecialchars($row['user_email']); ?></div>
                  </td>
                  <td><?php echo (int)$row['items_count']; ?> Items</td>
                  <td class="fw-bold">PKR <?php echo number_format((float)$row['order_total'], 2); ?></td>
                  <td><?php echo date("M d, Y", strtotime($row['order_date'])); ?></td>
                  <td>
                    <span class="badge bg-<?php echo ($row['status'] == 'Pending' ? 'warning text-dark' : ($row['status'] == 'Shipped' ? 'info text-dark' : ($row['status'] == 'Delivered' ? 'success' : 'secondary'))); ?>">
                      <?php echo htmlspecialchars($row['status']); ?>
                    </span>
                  </td>
                  <td class="text-end pe-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="order_details.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-info btn-sm" title="View Details">
                            <i class="bi bi-eye"></i>
                        </a>
                        <button type="button" 
                                class="btn btn-primary btn-sm" 
                                onclick="openStatusModal(<?php echo $row['id']; ?>, '<?php echo $row['status']; ?>', '<?php echo htmlspecialchars($row['courier_name'] ?? ''); ?>', '<?php echo htmlspecialchars($row['tracking_number'] ?? ''); ?>')">
                            Update
                        </button>
                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="alert alert-info text-center shadow-sm border-0">
      <i class="bi bi-info-circle me-2"></i> No orders found.
    </div>
  <?php endif; ?>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Update Order Status</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="order_id" id="modal_order_id">
        
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" id="modal_status" class="form-select" onchange="toggleTrackingFields()">
                <option value="Pending">Pending</option>
                <option value="Shipped">Shipped</option>
                <option value="Delivered">Delivered</option>
                <option value="Canceled">Canceled</option>
            </select>
        </div>

        <div id="tracking_fields" style="display:none;">
            <div class="mb-3">
                <label class="form-label">Courier Name</label>
                <select name="courier_name" id="modal_courier" class="form-select">
                    <option value="">Select Courier</option>
                    <?php
                    $couriers = $conn->query("SELECT name FROM couriers WHERE is_active = 1 ORDER BY name ASC");
                    while($c = $couriers->fetch_assoc()):
                    ?>
                        <option value="<?php echo htmlspecialchars($c['name']); ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                    <?php endwhile; ?>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Tracking Number</label>
                <input type="text" name="tracking_number" id="modal_tracking" class="form-control" placeholder="e.g. 123456789">
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function openStatusModal(id, status, courier, tracking) {
    document.getElementById('modal_order_id').value = id;
    document.getElementById('modal_status').value = status;
    document.getElementById('modal_courier').value = courier;
    document.getElementById('modal_tracking').value = tracking;
    
    toggleTrackingFields();
    new bootstrap.Modal(document.getElementById('statusModal')).show();
}

function toggleTrackingFields() {
    const status = document.getElementById('modal_status').value;
    const fields = document.getElementById('tracking_fields');
    if (status === 'Shipped' || status === 'Delivered') {
        fields.style.display = 'block';
    } else {
        fields.style.display = 'none';
    }
}
</script>

<?php include '../includes/footer.php'; ?>
