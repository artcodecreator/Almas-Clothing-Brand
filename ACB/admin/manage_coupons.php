<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Handle Add/Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_coupon'])) {
        $code = trim($_POST['code']);
        $type = $_POST['type'];
        $discount = (float)$_POST['discount'];
        $min_spend = (float)$_POST['min_spend'];
        $usage_limit = (int)$_POST['usage_limit'];
        $valid_until = $_POST['valid_until'];
        
        // Basic validation
        if ($type == 'percent' && $discount > 100) $discount = 100;
        
        $stmt = $conn->prepare("INSERT INTO coupons (code, type, discount_value, min_spend, usage_limit, valid_until) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssddis", $code, $type, $discount, $min_spend, $usage_limit, $valid_until);
        $stmt->execute();
        
        header("Location: manage_coupons.php");
        exit;
    } elseif (isset($_POST['delete_id'])) {
        $id = (int)$_POST['delete_id'];
        $conn->query("DELETE FROM coupons WHERE id = $id");
        header("Location: manage_coupons.php");
        exit;
    }
}

$coupons = $conn->query("SELECT * FROM coupons ORDER BY id DESC");
include '../includes/header.php';
?>

<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Manage Coupons</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCouponModal">
      <i class="bi bi-plus-lg"></i> Add Coupon
    </button>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-light">
            <tr>
              <th class="ps-4 py-3">Code</th>
              <th>Type</th>
              <th>Value</th>
              <th>Limits (Min/Usage)</th>
              <th>Used</th>
              <th>Valid Until</th>
              <th>Status</th>
              <th class="text-end pe-4">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php while($c = $coupons->fetch_assoc()): ?>
            <tr>
              <td class="ps-4 fw-bold text-primary"><?php echo htmlspecialchars($c['code']); ?></td>
              <td><span class="badge bg-info text-dark"><?php echo isset($c['type']) ? ucfirst($c['type']) : 'Percent'; ?></span></td>
              <td class="fw-bold">
                  <?php 
                  $discount = isset($c['discount_percent']) ? $c['discount_percent'] : (isset($c['discount_value']) ? $c['discount_value'] : 0);
                  $type = isset($c['type']) ? $c['type'] : 'percent';
                  echo $type == 'percent' ? $discount.'%' : 'PKR '.$discount; 
                  ?>
              </td>
              <td>
                  <small class="d-block text-muted">Min Spend: PKR <?php echo isset($c['min_spend']) ? $c['min_spend'] : '0'; ?></small>
                  <small class="d-block text-muted">Limit: <?php echo isset($c['usage_limit']) ? $c['usage_limit'] : 'Unlimited'; ?></small>
              </td>
              <td><?php echo isset($c['used_count']) ? $c['used_count'] : 0; ?></td>
              <td><?php echo date('M d, Y', strtotime($c['valid_until'])); ?></td>
              <td>
                <?php if(strtotime($c['valid_until']) < time()): ?>
                  <span class="badge bg-secondary">Expired</span>
                <?php elseif(isset($c['usage_limit']) && isset($c['used_count']) && $c['used_count'] >= $c['usage_limit']): ?>
                  <span class="badge bg-warning text-dark">Depleted</span>
                <?php else: ?>
                  <span class="badge bg-success">Active</span>
                <?php endif; ?>
              </td>
              <td class="text-end pe-4">
                <form method="POST" onsubmit="return confirm('Are you sure?');">
                  <input type="hidden" name="delete_id" value="<?php echo $c['id']; ?>">
                  <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="addCouponModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add New Coupon</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <input type="hidden" name="add_coupon" value="1">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Coupon Code</label>
            <input type="text" name="code" class="form-control text-uppercase" placeholder="e.g. SUMMER2025" required>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Discount Type</label>
                <select name="type" class="form-select" id="couponType" onchange="updatePlaceholder()">
                    <option value="percent">Percentage (%)</option>
                    <option value="fixed">Fixed Amount (PKR)</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Discount Value</label>
                <input type="number" step="0.01" name="discount" class="form-control" id="discountInput" required>
            </div>
          </div>

          <div class="row">
             <div class="col-md-6 mb-3">
                <label class="form-label">Min Spend (PKR)</label>
                <input type="number" step="0.01" name="min_spend" class="form-control" value="0">
             </div>
             <div class="col-md-6 mb-3">
                <label class="form-label">Usage Limit</label>
                <input type="number" name="usage_limit" class="form-control" value="100">
             </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Valid Until</label>
            <input type="date" name="valid_until" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Create Coupon</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function updatePlaceholder() {
    const type = document.getElementById('couponType').value;
    const input = document.getElementById('discountInput');
    if (type === 'percent') {
        input.placeholder = "e.g. 20 (for 20%)";
        input.max = 100;
    } else {
        input.placeholder = "e.g. 1500 (for PKR 1500)";
        input.removeAttribute('max');
    }
}
updatePlaceholder();
</script>

<?php include '../includes/footer.php'; ?>
