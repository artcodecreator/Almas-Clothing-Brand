<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($product_id > 0) {
    if ($action === 'add') {
        // Check if exists
        $check = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $check->bind_param("ii", $user_id, $product_id);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows == 0) {
            $stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $user_id, $product_id);
            $stmt->execute();
        }
        $check->close();
    } elseif ($action === 'remove') {
        $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
    }
}

// Redirect back
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'shop.php';
header("Location: $referer");
exit();
?>
