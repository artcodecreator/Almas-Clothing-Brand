<?php 
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['quantities'])) {
    foreach ($_POST['quantities'] as $product_id => $quantity) {
        $product_id = intval($product_id);
        $quantity = intval($quantity);

        if ($quantity > 0) {
            $_SESSION['cart'][$product_id] = $quantity;
        } else {
            unset($_SESSION['cart'][$product_id]);
        }
    }
}

header("Location: cart.php");
exit;
?>
