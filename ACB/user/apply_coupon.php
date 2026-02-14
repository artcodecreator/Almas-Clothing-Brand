<?php
session_start();
include("../includes/db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = isset($_POST['coupon_code']) ? trim($_POST['coupon_code']) : '';
    $action = isset($_POST['action']) ? $_POST['action'] : 'apply';

    if ($action === 'remove') {
        unset($_SESSION['coupon']);
        $_SESSION['success'] = "Coupon removed.";
    } elseif ($code) {
        // Fetch cart total to validate min_spend
        $cart = $_SESSION['cart'] ?? [];
        $cart_total = 0;
        foreach ($cart as $pid => $qty) {
            $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
            $stmt->bind_param("i", $pid);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            if ($res) {
                $cart_total += $res['price'] * $qty;
            }
        }

        $stmt = $conn->prepare("SELECT * FROM coupons WHERE code = ? AND status = 1 AND valid_until >= CURDATE()");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $coupon = $result->fetch_assoc();
            
            // Check usage limit
            if ($coupon['used_count'] >= $coupon['usage_limit']) {
                $_SESSION['error'] = "This coupon has reached its usage limit.";
            } 
            // Check min spend
            elseif ($cart_total < $coupon['min_spend']) {
                $_SESSION['error'] = "Minimum spend of PKR " . number_format($coupon['min_spend']) . " required.";
            } else {
                $_SESSION['coupon'] = [
                    'code' => $coupon['code'],
                    'type' => $coupon['type'],
                    'value' => $coupon['discount_value']
                ];
                $_SESSION['success'] = "Coupon applied successfully!";
            }
        } else {
            $_SESSION['error'] = "Invalid or expired coupon code.";
        }
    }
}

header("Location: cart.php");
exit();
?>
