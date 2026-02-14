<?php
session_start();
include("../includes/db.php");
include '../includes/header.php';

$tracking_number = isset($_GET['tracking']) ? trim($_GET['tracking']) : '';
$order_info = null;
$error = '';

if (!empty($tracking_number)) {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE tracking_number = ?");
    $stmt->bind_param("s", $tracking_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $order_info = $result->fetch_assoc();
    } else {
        $error = "No order found with tracking number: " . htmlspecialchars($tracking_number);
    }
}

function getCourierLink($conn, $courier_name, $tracking) {
    // Try to find exact match first
    $stmt = $conn->prepare("SELECT tracking_url FROM couriers WHERE name = ? LIMIT 1");
    $stmt->bind_param("s", $courier_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return str_replace("{TRACKING_NO}", $tracking, $row['tracking_url']);
    }
    
    return "#";
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow border-0 rounded-4">
                <div class="card-body p-5">
                    <h2 class="text-center fw-bold mb-4">Track Your Order</h2>
                    
                    <form action="" method="GET" class="mb-5">
                        <div class="input-group input-group-lg">
                            <input type="text" name="tracking" class="form-control" placeholder="Enter Tracking Number" value="<?php echo htmlspecialchars($tracking_number); ?>" required>
                            <button class="btn btn-primary px-4" type="submit">Track</button>
                        </div>
                    </form>

                    <?php if ($error): ?>
                        <div class="alert alert-danger text-center">
                            <i class="bi bi-exclamation-circle me-2"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($order_info): ?>
                        <div class="text-center mb-4">
                            <div class="display-1 text-primary mb-3">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <h4 class="fw-bold">Order #<?php echo $order_info['id']; ?></h4>
                            <p class="text-muted">Current Status</p>
                            <span class="badge bg-<?php echo ($order_info['status'] == 'Delivered') ? 'success' : 'primary'; ?> fs-5 px-4 py-2 rounded-pill">
                                <?php echo htmlspecialchars($order_info['status']); ?>
                            </span>
                        </div>

                        <div class="bg-light p-4 rounded-3 mb-4">
                            <div class="row g-3">
                                <div class="col-6">
                                    <small class="text-muted d-block">Courier</small>
                                    <span class="fw-bold"><?php echo htmlspecialchars($order_info['courier_name']); ?></span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Tracking Number</small>
                                    <span class="fw-bold"><?php echo htmlspecialchars($order_info['tracking_number']); ?></span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Order Date</small>
                                    <span class="fw-bold"><?php echo date('M d, Y', strtotime($order_info['created_at'])); ?></span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Destination</small>
                                    <span class="fw-bold"><?php echo htmlspecialchars($order_info['city']); ?></span>
                                </div>
                            </div>
                        </div>

                        <?php 
                        $link = getCourierLink($conn, $order_info['courier_name'], $order_info['tracking_number']); 
                        if ($link !== "#"):
                        ?>
                        <div class="d-grid">
                            <a href="<?php echo $link; ?>" target="_blank" class="btn btn-outline-dark btn-lg">
                                <i class="bi bi-box-arrow-up-right me-2"></i> Track on <?php echo htmlspecialchars($order_info['courier_name']); ?> Website
                            </a>
                        </div>
                        <?php endif; ?>

                    <?php endif; ?>
                </div>
            </div>
            
            <div class="text-center mt-4 text-muted small">
                <p>Supported Couriers: TCS, Leopards, M&P, Call Courier</p>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
