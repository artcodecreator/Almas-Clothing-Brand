<?php 
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Almas Clothing | Elevate Your Style</title>
  
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
  
  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  
  <!-- Custom CSS -->
  <link href="<?php echo asset_url('css/style.css'); ?>" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg sticky-top shadow-sm">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo base_url(''); ?>">
      <span class="bg-black text-white rounded-circle d-flex align-items-center justify-content-center" style="width:40px; height:40px;">A</span>
      <span>Almas</span>
    </a>
    
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <!-- Search Form -->
      <form class="d-flex mx-lg-auto my-2 my-lg-0" action="<?php echo base_url('user/shop.php'); ?>" method="GET" style="max-width: 400px; width: 100%;">
        <div class="input-group">
          <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
          <input class="form-control bg-light border-start-0 ps-0" type="search" name="search" placeholder="Search for products..." aria-label="Search">
        </div>
      </form>

      <ul class="navbar-nav align-items-lg-center gap-lg-3">
        <li class="nav-item"><a class="nav-link" href="<?php echo base_url('user/shop.php'); ?>">Shop</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Categories</a>
          <ul class="dropdown-menu border-0 shadow">
            <li><a class="dropdown-item" href="<?php echo base_url('user/shop.php?category=Men'); ?>">Men</a></li>
            <li><a class="dropdown-item" href="<?php echo base_url('user/shop.php?category=Women'); ?>">Women</a></li>
            <li><a class="dropdown-item" href="<?php echo base_url('user/shop.php?category=Kids'); ?>">Kids</a></li>
            <li><a class="dropdown-item" href="<?php echo base_url('user/shop.php?category=Accessories'); ?>">Accessories</a></li>
          </ul>
        </li>
        
        <?php if (isset($_SESSION['admin_id'])) { ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Admin</a>
            <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
              <li><a class="dropdown-item" href="<?php echo base_url('admin/dashboard.php'); ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
              <li><a class="dropdown-item" href="<?php echo base_url('admin/financial_analytics.php'); ?>"><i class="bi bi-graph-up-arrow me-2"></i> Analytics</a></li>
              <li><a class="dropdown-item" href="<?php echo base_url('admin/manage_products.php'); ?>"><i class="bi bi-box-seam me-2"></i> Products</a></li>
              <li><a class="dropdown-item" href="<?php echo base_url('admin/manage_orders.php'); ?>"><i class="bi bi-receipt me-2"></i> Orders</a></li>
              <li><a class="dropdown-item" href="<?php echo base_url('admin/manage_users.php'); ?>"><i class="bi bi-people me-2"></i> Users</a></li>
              <li><a class="dropdown-item" href="<?php echo base_url('admin/moderate_reviews.php'); ?>"><i class="bi bi-star-half me-2"></i> Reviews</a></li>
              <li><a class="dropdown-item" href="<?php echo base_url('admin/manage_coupons.php'); ?>"><i class="bi bi-ticket-perforated me-2"></i> Coupons</a></li>
              <li><a class="dropdown-item" href="<?php echo base_url('admin/manage_couriers.php'); ?>"><i class="bi bi-truck me-2"></i> Couriers</a></li>
              <li><a class="dropdown-item" href="<?php echo base_url('admin/manage_messages.php'); ?>"><i class="bi bi-envelope me-2"></i> Messages</a></li>
              <li><a class="dropdown-item" href="<?php echo base_url('admin/chat.php'); ?>"><i class="bi bi-chat-dots me-2"></i> Live Chat</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="<?php echo base_url('admin/logout.php'); ?>"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
            </ul>
          </li>
        <?php } elseif (isset($_SESSION['user_id'])) { ?>
          <li class="nav-item"><a class="nav-link position-relative" href="<?php echo base_url('user/wishlist.php'); ?>">
            <i class="bi bi-heart fs-5"></i>
          </a></li>
          <li class="nav-item"><a class="nav-link position-relative" href="<?php echo base_url('user/cart.php'); ?>">
            <i class="bi bi-bag fs-5"></i>
          </a></li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
              <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border" style="width:35px; height:35px;">
                <i class="bi bi-person"></i>
              </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
              <li><a class="dropdown-item" href="<?php echo base_url('user/profile.php'); ?>">My Profile</a></li>
              <li><a class="dropdown-item" href="<?php echo base_url('user/orders.php'); ?>">My Orders</a></li>
              <li><a class="dropdown-item" href="<?php echo base_url('user/my_messages.php'); ?>">My Messages</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="<?php echo base_url('user/logout.php'); ?>">Logout</a></li>
            </ul>
          </li>
        <?php } else { ?>
          <li class="nav-item"><a class="nav-link" href="<?php echo base_url('user/cart.php'); ?>"><i class="bi bi-bag fs-5"></i></a></li>
          <li class="nav-item d-flex gap-2">
            <a class="btn btn-outline-dark btn-sm" href="<?php echo base_url('user/login.php'); ?>">Login</a>
            <a class="btn btn-dark btn-sm" href="<?php echo base_url('user/register.php'); ?>">Sign Up</a>
          </li>
        <?php } ?>
      </ul>
    </div>
  </div>
</nav>
