<?php 
session_start();
include("../includes/db.php");
error_reporting(E_ALL);
ini_set('display_errors', 1);


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    if ($product_id > 0 && $quantity > 0) {
        // Fetch product details from DB
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();

        if ($product && $product['stock'] >= $quantity) {
            // Add to session cart
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }

            // If product is already in cart, update quantity
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id] += $quantity;
            } else {
                $_SESSION['cart'][$product_id] = $quantity;
            }
        }
    }
}

header("Location: cart.php");
exit;
?>
