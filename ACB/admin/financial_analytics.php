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

// Helper function to get single value
function get_scalar($conn, $query) {
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_row()) {
        return $row[0];
    }
    return 0;
}

// 1. Key Metrics
$total_revenue = get_scalar($conn, "
    SELECT SUM(oi.total_price) 
    FROM order_items oi 
    JOIN orders o ON oi.order_id = o.id 
    WHERE o.status != 'Canceled'
");

$total_orders = get_scalar($conn, "SELECT COUNT(*) FROM orders WHERE status != 'Canceled'");

$total_products_sold = get_scalar($conn, "
    SELECT SUM(oi.quantity) 
    FROM order_items oi 
    JOIN orders o ON oi.order_id = o.id 
    WHERE o.status != 'Canceled'
");

$avg_order_value = $total_orders > 0 ? $total_revenue / $total_orders : 0;

// 2. Revenue by Category
$category_revenue_query = "
    SELECT c.name as category_name, SUM(oi.total_price) as revenue, COUNT(DISTINCT o.id) as order_count
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    JOIN products p ON oi.product_id = p.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE o.status != 'Canceled'
    GROUP BY c.name
    ORDER BY revenue DESC
";
$category_revenue = $conn->query($category_revenue_query);

// 3. Top Selling Products
$top_products_query = "
    SELECT p.name, SUM(oi.quantity) as sold_count, SUM(oi.total_price) as revenue
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    JOIN products p ON oi.product_id = p.id
    WHERE o.status != 'Canceled'
    GROUP BY p.id, p.name
    ORDER BY sold_count DESC
    LIMIT 5
";
$top_products = $conn->query($top_products_query);

// 4. Recent Orders (Mini view)
$recent_orders_query = "
    SELECT o.id, u.name as user, o.created_at, SUM(oi.total_price) as total, o.status
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN order_items oi ON oi.order_id = o.id
    GROUP BY o.id, u.name, o.created_at, o.status
    ORDER BY o.created_at DESC
    LIMIT 5
";
$recent_orders = $conn->query($recent_orders_query);

include '../includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Financial Analytics</h2>
        <div>
            <button class="btn btn-outline-primary" onclick="window.print()"><i class="bi bi-printer"></i> Print Report</button>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title opacity-75">Total Revenue</h6>
                    <h3 class="fw-bold mb-0">PKR <?php echo number_format((float)$total_revenue, 2); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title opacity-75">Total Orders</h6>
                    <h3 class="fw-bold mb-0"><?php echo number_format((int)$total_orders); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title opacity-75">Avg Order Value</h6>
                    <h3 class="fw-bold mb-0">PKR <?php echo number_format((float)$avg_order_value, 2); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-warning text-dark">
                <div class="card-body">
                    <h6 class="card-title opacity-75">Products Sold</h6>
                    <h3 class="fw-bold mb-0"><?php echo number_format((int)$total_products_sold); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Revenue by Category -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Revenue by Category</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Category</th>
                                    <th class="text-end">Orders</th>
                                    <th class="text-end">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($category_revenue->num_rows > 0): ?>
                                    <?php while($cat = $category_revenue->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($cat['category_name'] ?? 'Uncategorized'); ?></td>
                                        <td class="text-end"><?php echo $cat['order_count']; ?></td>
                                        <td class="text-end fw-bold">PKR <?php echo number_format((float)$cat['revenue'], 2); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center text-muted">No data available</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Selling Products -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Top Selling Products</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Sold</th>
                                    <th class="text-end">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($top_products->num_rows > 0): ?>
                                    <?php while($prod = $top_products->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($prod['name']); ?></td>
                                        <td class="text-end"><?php echo $prod['sold_count']; ?></td>
                                        <td class="text-end fw-bold">PKR <?php echo number_format((float)$prod['revenue'], 2); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center text-muted">No data available</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Orders Snapshot -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold">Recent Transactions</h5>
            <a href="manage_orders.php" class="btn btn-sm btn-outline-primary">View All Orders</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recent_orders->num_rows > 0): ?>
                            <?php while($ord = $recent_orders->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4">#<?php echo $ord['id']; ?></td>
                                <td><?php echo htmlspecialchars($ord['user']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($ord['created_at'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo ($ord['status'] == 'Pending' ? 'warning text-dark' : ($ord['status'] == 'Shipped' ? 'info text-dark' : ($ord['status'] == 'Delivered' ? 'success' : 'secondary'))); ?>">
                                        <?php echo htmlspecialchars($ord['status']); ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4 fw-bold">PKR <?php echo number_format((float)$ord['total'], 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No recent orders</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php include '../includes/footer.php'; ?>
