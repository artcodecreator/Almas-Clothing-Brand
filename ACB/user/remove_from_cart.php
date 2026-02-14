<?php 
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$id = intval($_GET['id'] ?? 0);
if ($id > 0 && isset($_SESSION['cart'][$id])) {
    unset($_SESSION['cart'][$id]);
}

header("Location: cart.php");
exit;
?>
