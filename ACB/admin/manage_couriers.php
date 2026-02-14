<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Handle Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_courier'])) {
        $name = trim($_POST['name']);
        $url = trim($_POST['tracking_url']);
        $charge = (float)$_POST['per_unit_charge'];
        $tax = (float)$_POST['tax_percent'];
        
        $stmt = $conn->prepare("INSERT INTO couriers (name, tracking_url, per_unit_charge, tax_percent) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssdd", $name, $url, $charge, $tax);
        $stmt->execute();
        
        header("Location: manage_couriers.php");
        exit;
    } elseif (isset($_POST['edit_courier'])) {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name']);
        $url = trim($_POST['tracking_url']);
        $charge = (float)$_POST['per_unit_charge'];
        $tax = (float)$_POST['tax_percent'];
        $status = isset($_POST['is_active']) ? 1 : 0;

        $stmt = $conn->prepare("UPDATE couriers SET name=?, tracking_url=?, per_unit_charge=?, tax_percent=?, is_active=? WHERE id=?");
        $stmt->bind_param("ssddii", $name, $url, $charge, $tax, $status, $id);
        $stmt->execute();
        
        header("Location: manage_couriers.php");
        exit;
    } elseif (isset($_POST['delete_id'])) {
        $id = (int)$_POST['delete_id'];
        $conn->query("DELETE FROM couriers WHERE id = $id");
        header("Location: manage_couriers.php");
        exit;
    }
}

$couriers = $conn->query("SELECT * FROM couriers ORDER BY id DESC");
include '../includes/header.php';
?>

<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Manage Couriers</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCourierModal">
      <i class="bi bi-plus-lg"></i> Add New Courier
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
              <th>Per Unit Charge</th>
              <th>Tax (%)</th>
              <th>Tracking URL Pattern</th>
              <th>Status</th>
              <th class="text-end pe-4">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while($c = $couriers->fetch_assoc()): ?>
            <tr>
              <td class="ps-4 fw-bold">#<?php echo $c['id']; ?></td>
              <td class="fw-bold text-primary"><?php echo htmlspecialchars($c['name']); ?></td>
              <td>PKR <?php echo number_format($c['per_unit_charge'], 2); ?></td>
              <td><?php echo $c['tax_percent']; ?>%</td>
              <td><small class="text-muted text-break"><?php echo htmlspecialchars($c['tracking_url']); ?></small></td>
              <td>
                <?php if($c['is_active']): ?>
                  <span class="badge bg-success">Active</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Inactive</span>
                <?php endif; ?>
              </td>
              <td class="text-end pe-4">
                <div class="d-flex justify-content-end gap-2">
                    <button class="btn btn-sm btn-outline-primary" onclick='editCourier(<?php echo json_encode($c); ?>)'>
                        <i class="bi bi-pencil"></i>
                    </button>
                    <form method="POST" onsubmit="return confirm('Are you sure?');" style="margin:0;">
                      <input type="hidden" name="delete_id" value="<?php echo $c['id']; ?>">
                      <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addCourierModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <input type="hidden" name="add_courier" value="1">
      <div class="modal-header">
        <h5 class="modal-title">Add Courier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Courier Name</label>
          <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Tracking URL Pattern</label>
          <input type="text" name="tracking_url" class="form-control" placeholder="e.g. https://site.com/track?id={TRACKING_NO}" required>
          <small class="text-muted">Use <code>{TRACKING_NO}</code> as placeholder for tracking number.</small>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Per Unit Charge (PKR)</label>
                <input type="number" step="0.01" name="per_unit_charge" class="form-control" value="0.00">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Tax (%)</label>
                <input type="number" step="0.01" name="tax_percent" class="form-control" value="0.00">
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save Courier</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editCourierModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <input type="hidden" name="edit_courier" value="1">
      <input type="hidden" name="id" id="edit_id">
      <div class="modal-header">
        <h5 class="modal-title">Edit Courier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Courier Name</label>
          <input type="text" name="name" id="edit_name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Tracking URL Pattern</label>
          <input type="text" name="tracking_url" id="edit_url" class="form-control" required>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Per Unit Charge (PKR)</label>
                <input type="number" step="0.01" name="per_unit_charge" id="edit_charge" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Tax (%)</label>
                <input type="number" step="0.01" name="tax_percent" id="edit_tax" class="form-control">
            </div>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" name="is_active" class="form-check-input" id="edit_active">
            <label class="form-check-label" for="edit_active">Active</label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Update Courier</button>
      </div>
    </form>
  </div>
</div>

<script>
function editCourier(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_name').value = data.name;
    document.getElementById('edit_url').value = data.tracking_url;
    document.getElementById('edit_charge').value = data.per_unit_charge;
    document.getElementById('edit_tax').value = data.tax_percent;
    document.getElementById('edit_active').checked = data.is_active == 1;
    
    new bootstrap.Modal(document.getElementById('editCourierModal')).show();
}
</script>

<?php include '../includes/footer.php'; ?>