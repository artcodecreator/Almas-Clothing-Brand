<!-- Footer -->
<footer class="pt-5 pb-3">
  <div class="container">
    <div class="row g-4 mb-5">
      <div class="col-lg-4">
        <h5 class="fw-bold text-white mb-4 d-flex align-items-center gap-2">
          <span class="bg-white text-black rounded-circle d-flex align-items-center justify-content-center" style="width:30px; height:30px; font-size: 14px;">A</span>
          Almas Clothing
        </h5>
        <p class="text-muted mb-4">Elevating style with curated fashion for men, women, and kids. Quality meets comfort in every piece.</p>
        <div class="d-flex gap-3">
          <a href="#" class="btn btn-outline-light rounded-circle btn-sm d-flex align-items-center justify-content-center" style="width:35px; height:35px;"><i class="bi bi-facebook"></i></a>
          <a href="#" class="btn btn-outline-light rounded-circle btn-sm d-flex align-items-center justify-content-center" style="width:35px; height:35px;"><i class="bi bi-instagram"></i></a>
          <a href="#" class="btn btn-outline-light rounded-circle btn-sm d-flex align-items-center justify-content-center" style="width:35px; height:35px;"><i class="bi bi-twitter-x"></i></a>
        </div>
      </div>
      
      <div class="col-6 col-md-3 col-lg-2">
        <h6 class="fw-bold text-white mb-3">Shop</h6>
        <ul class="list-unstyled d-flex flex-column gap-2">
          <li><a href="<?php echo base_url('user/shop.php?category=Men'); ?>">Men</a></li>
          <li><a href="<?php echo base_url('user/shop.php?category=Women'); ?>">Women</a></li>
          <li><a href="<?php echo base_url('user/shop.php?category=Kids'); ?>">Kids</a></li>
          <li><a href="<?php echo base_url('user/shop.php?category=Accessories'); ?>">Accessories</a></li>
        </ul>
      </div>
      
      <div class="col-6 col-md-3 col-lg-2">
        <h6 class="fw-bold text-white mb-3">Support</h6>
        <ul class="list-unstyled d-flex flex-column gap-2">
          <li><a href="<?php echo base_url('user/track_order.php'); ?>">Track Order</a></li>
          <li><a href="<?php echo base_url('user/contact.php'); ?>">Contact Us</a></li>
          <li><a href="<?php echo base_url('user/pages.php?page=faq'); ?>">FAQs</a></li>
          <li><a href="<?php echo base_url('user/pages.php?page=shipping'); ?>">Shipping & Returns</a></li>
          <li><a href="<?php echo base_url('user/pages.php?page=privacy'); ?>">Privacy Policy</a></li>
        </ul>
      </div>
      
      <div class="col-md-6 col-lg-4">
        <h6 class="fw-bold text-white mb-3">Stay Updated</h6>
        <p class="text-muted small">Subscribe to our newsletter for the latest drops and exclusive offers.</p>
        <form class="d-flex gap-2" action="<?php echo base_url('subscribe.php'); ?>" method="POST">
          <input type="email" name="email" class="form-control bg-dark border-secondary text-light" placeholder="Enter your email" required>
          <button class="btn btn-primary" type="submit">Subscribe</button>
        </form>
      </div>
    </div>
    
    <div class="border-top border-secondary pt-4">
      <div class="row align-items-center">
        <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
          <p class="text-muted small mb-0">&copy; <?php echo date('Y'); ?> Almas Clothing Brand. All rights reserved.</p>
        </div>
        <div class="col-md-6 text-center text-md-end">
          <p class="text-muted small mb-0">Designed for fashion lovers. <a href="<?php echo isset($_SESSION['admin_id']) ? base_url('admin/dashboard.php') : base_url('admin/login.php'); ?>" class="text-secondary text-decoration-none ms-2">Admin</a></p>
        </div>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Chat Widget -->
<?php include_once __DIR__ . '/chat_widget.php'; ?>

</body>
</html>
