<?php
session_start();
include("../includes/db.php");

// ✅ Protect admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// 1. Basic Counts
$product_count = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$user_count = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$order_count = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$pending_reviews = $conn->query("SELECT COUNT(*) AS total FROM review WHERE is_approved = 0")->fetch_assoc()['total'];
$coupon_count = $conn->query("SELECT COUNT(*) as count FROM coupons")->fetch_assoc()['count'];
// For chat/messages, we check unread user messages
$unread_chats = $conn->query("SELECT COUNT(DISTINCT user_id) as count FROM chat_messages WHERE sender='user' AND is_read=0")->fetch_assoc()['count'];

// 2. Sales Data (Last 7 Days)
$sales_dates = [];
$sales_totals = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $sales_dates[] = date('M d', strtotime($date));
    
    // Sum final_total for this day
    $stmt = $conn->prepare("SELECT SUM(final_total) as total FROM orders WHERE DATE(created_at) = ? AND status != 'Cancelled'");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $sales_totals[] = $res['total'] ? (float)$res['total'] : 0;
}

// 3. Top 5 Products
$top_products = [];
$top_qtys = [];
$tp_res = $conn->query("SELECT product_name, SUM(quantity) as total_qty FROM order_items GROUP BY product_id ORDER BY total_qty DESC LIMIT 5");
while($row = $tp_res->fetch_assoc()) {
    $top_products[] = $row['product_name'];
    $top_qtys[] = (int)$row['total_qty'];
}

// 4. Order Status Distribution
$statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
$status_counts = [];
foreach($statuses as $s) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE status = ?");
    $stmt->bind_param("s", $s);
    $stmt->execute();
    $status_counts[] = $stmt->get_result()->fetch_assoc()['count'];
}

include '../includes/header.php';
?>

<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold">Admin Dashboard</h2>
        <p class="text-muted">Overview of your store's performance</p>
    </div>
    <div class="d-flex gap-2">
        <a href="chat.php" class="btn btn-primary position-relative">
            <i class="bi bi-chat-dots"></i> Live Chat
            <?php if($unread_chats > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?php echo $unread_chats; ?>
            </span>
            <?php endif; ?>
        </a>
    </div>
  </div>

  <!-- Key Metrics Cards -->
  <div class="row g-4 mb-5">
    <!-- Products -->
    <div class="col-md-6 col-lg-3">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body d-flex align-items-center">
          <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
            <i class="bi bi-box-seam fs-3 text-primary"></i>
          </div>
          <div>
            <h6 class="text-muted mb-1">Products</h6>
            <h3 class="fw-bold mb-0"><?php echo $product_count; ?></h3>
          </div>
          <a href="manage_products.php" class="stretched-link"></a>
        </div>
      </div>
    </div>

    <!-- Orders -->
    <div class="col-md-6 col-lg-3">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body d-flex align-items-center">
          <div class="bg-success bg-opacity-10 p-3 rounded-circle me-3">
            <i class="bi bi-cart-check fs-3 text-success"></i>
          </div>
          <div>
            <h6 class="text-muted mb-1">Orders</h6>
            <h3 class="fw-bold mb-0"><?php echo $order_count; ?></h3>
          </div>
          <a href="manage_orders.php" class="stretched-link"></a>
        </div>
      </div>
    </div>

    <!-- Users -->
    <div class="col-md-6 col-lg-3">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body d-flex align-items-center">
          <div class="bg-info bg-opacity-10 p-3 rounded-circle me-3">
            <i class="bi bi-people fs-3 text-info"></i>
          </div>
          <div>
            <h6 class="text-muted mb-1">Users</h6>
            <h3 class="fw-bold mb-0"><?php echo $user_count; ?></h3>
          </div>
          <a href="manage_users.php" class="stretched-link"></a>
        </div>
      </div>
    </div>

    <!-- Reviews -->
    <div class="col-md-6 col-lg-3">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body d-flex align-items-center">
          <div class="bg-warning bg-opacity-10 p-3 rounded-circle me-3">
            <i class="bi bi-star fs-3 text-warning"></i>
          </div>
          <div>
            <h6 class="text-muted mb-1">Reviews</h6>
            <h3 class="fw-bold mb-0"><?php echo $pending_reviews; ?> <span class="fs-6 text-muted fw-normal">pending</span></h3>
          </div>
          <a href="moderate_reviews.php" class="stretched-link"></a>
        </div>
      </div>
    </div>
  </div>

  <!-- Analytics Charts -->
  <div class="row g-4 mb-5">
    <!-- Sales Chart -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Revenue (Last 7 Days)</h5>
            </div>
            <div class="card-body">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Order Status Chart -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Order Status</h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>
  </div>
  
  <div class="row g-4">
      <!-- Top Products -->
      <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Top Selling Products</h5>
            </div>
            <div class="card-body">
                <canvas id="productsChart" height="100"></canvas>
            </div>
        </div>
      </div>
  </div>

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Data from PHP
const salesDates = <?php echo json_encode($sales_dates); ?>;
const salesData = <?php echo json_encode($sales_totals); ?>;
const topProducts = <?php echo json_encode($top_products); ?>;
const topQty = <?php echo json_encode($top_qtys); ?>;
const statusLabels = <?php echo json_encode($statuses); ?>;
const statusData = <?php echo json_encode($status_counts); ?>;

// Sales Chart
new Chart(document.getElementById('salesChart'), {
    type: 'line',
    data: {
        labels: salesDates,
        datasets: [{
            label: 'Revenue (PKR)',
            data: salesData,
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});

// Status Chart
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: statusLabels,
        datasets: [{
            data: statusData,
            backgroundColor: ['#ffc107', '#0dcaf0', '#0d6efd', '#198754', '#dc3545']
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } }
    }
});

// Products Chart
new Chart(document.getElementById('productsChart'), {
    type: 'bar',
    data: {
        labels: topProducts,
        datasets: [{
            label: 'Units Sold',
            data: topQty,
            backgroundColor: '#20c997'
        }]
    },
    options: {
        responsive: true,
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true } }
    }
});
</script>

<?php include '../includes/footer.php'; ?>
