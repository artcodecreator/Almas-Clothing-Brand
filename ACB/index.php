<?php
session_start();
include("includes/header.php");
include("includes/db.php");

// Fetch Featured Products
$featured = $conn->query("SELECT * FROM products WHERE is_featured = 1 LIMIT 4");
if ($featured->num_rows == 0) {
    // Fallback if no featured products
    $featured = $conn->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 4");
}

// Fetch New Arrivals
$new_arrivals = $conn->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 8");
?>

<!-- Hero Section -->
<section class="position-relative overflow-hidden" style="background: #f0f2f5;">
  <div class="container py-5">
    <div class="row align-items-center min-vh-75">
      <div class="col-lg-6 mb-5 mb-lg-0">
        <span class="d-inline-block py-1 px-3 rounded-pill bg-white text-primary fw-bold small mb-3 shadow-sm">New Collection 2026</span>
        <h1 class="display-3 fw-bold mb-4" style="font-family: 'Playfair Display', serif;">Elevate Your Everyday Style</h1>
        <p class="lead text-muted mb-5">Discover the latest trends in fashion. Curated pieces for men, women, and kids that blend comfort with elegance.</p>
        <div class="d-flex gap-3">
          <a href="<?php echo base_url('user/shop.php'); ?>" class="btn btn-dark btn-lg px-4 rounded-pill">Shop Now</a>
          <a href="<?php echo base_url('user/shop.php?category=Women'); ?>" class="btn btn-outline-dark btn-lg px-4 rounded-pill">View Collection</a>
        </div>
      </div>
      <div class="col-lg-6 position-relative">
        <div class="position-relative z-index-1">
          <img src="<?php echo asset_url('images/hero-fashion.svg'); ?>" class="img-fluid rounded-4 shadow-lg" alt="Hero Image">
        </div>
        <!-- Decorative elements -->
        <div class="position-absolute top-0 end-0 translate-middle-y bg-warning rounded-circle" style="width: 100px; height: 100px; opacity: 0.2; filter: blur(20px);"></div>
        <div class="position-absolute bottom-0 start-0 translate-middle-y bg-info rounded-circle" style="width: 150px; height: 150px; opacity: 0.2; filter: blur(30px);"></div>
      </div>
    </div>
  </div>
</section>

<!-- Features Strip -->
<section class="py-5 bg-white border-bottom">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-3 d-flex align-items-center gap-3">
        <div class="bg-light p-3 rounded-circle"><i class="bi bi-truck fs-4"></i></div>
        <div>
          <h6 class="fw-bold mb-0">Free Shipping</h6>
          <small class="text-muted">On all orders over PKR 5000</small>
        </div>
      </div>
      <div class="col-md-3 d-flex align-items-center gap-3">
        <div class="bg-light p-3 rounded-circle"><i class="bi bi-arrow-repeat fs-4"></i></div>
        <div>
          <h6 class="fw-bold mb-0">Easy Returns</h6>
          <small class="text-muted">30-day money back guarantee</small>
        </div>
      </div>
      <div class="col-md-3 d-flex align-items-center gap-3">
        <div class="bg-light p-3 rounded-circle"><i class="bi bi-shield-check fs-4"></i></div>
        <div>
          <h6 class="fw-bold mb-0">Secure Payment</h6>
          <small class="text-muted">100% secure checkout</small>
        </div>
      </div>
      <div class="col-md-3 d-flex align-items-center gap-3">
        <div class="bg-light p-3 rounded-circle"><i class="bi bi-headset fs-4"></i></div>
        <div>
          <h6 class="fw-bold mb-0">24/7 Support</h6>
          <small class="text-muted">Dedicated support team</small>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Featured Products -->
<section class="py-5">
  <div class="container py-5">
    <div class="text-center mb-5">
      <h2 class="fw-bold" style="font-family: 'Playfair Display', serif;">Featured Products</h2>
      <p class="text-muted">Handpicked favorites just for you</p>
    </div>
    
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
      <?php while($prod = $featured->fetch_assoc()): ?>
      <div class="col">
        <div class="card h-100 product-card shadow-sm">
          <a href="<?php echo base_url('user/product_details.php?id='.$prod['id']); ?>">
            <img src="<?php echo htmlspecialchars(image_or_placeholder($prod['image_url'])); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($prod['name']); ?>">
          </a>
          <div class="card-body text-center">
            <h5 class="card-title fw-bold mb-1"><a href="<?php echo base_url('user/product_details.php?id='.$prod['id']); ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($prod['name']); ?></a></h5>
            <div class="mb-2 text-primary fw-bold">PKR <?php echo number_format($prod['price']); ?></div>
            <form action="<?php echo base_url('user/add_to_cart.php'); ?>" method="post">
              <input type="hidden" name="product_id" value="<?php echo $prod['id']; ?>">
              <input type="hidden" name="quantity" value="1">
              <button class="btn btn-outline-dark btn-sm rounded-pill w-100">Add to Cart</button>
            </form>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
  </div>
</section>

<!-- Categories Grid -->
<section class="py-5 bg-light">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-6">
        <div class="position-relative rounded-4 overflow-hidden h-100 shadow-sm group">
          <img src="<?php echo asset_url('images/cat-women.svg'); ?>" class="w-100 h-100 object-fit-cover" style="min-height: 300px;">
          <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-25"></div>
          <div class="position-absolute bottom-0 start-0 p-4 text-white">
            <h3 class="fw-bold">Women's Collection</h3>
            <a href="<?php echo base_url('user/shop.php?category=Women'); ?>" class="btn btn-light rounded-pill btn-sm">Shop Now</a>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="row g-4">
          <div class="col-12">
            <div class="position-relative rounded-4 overflow-hidden shadow-sm">
              <img src="<?php echo asset_url('images/cat-men.svg'); ?>" class="w-100 object-fit-cover" style="height: 200px;">
              <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-25"></div>
              <div class="position-absolute bottom-0 start-0 p-4 text-white">
                <h4 class="fw-bold">Men's Fashion</h4>
                <a href="<?php echo base_url('user/shop.php?category=Men'); ?>" class="btn btn-light rounded-pill btn-sm">Shop Now</a>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="position-relative rounded-4 overflow-hidden shadow-sm">
              <img src="<?php echo asset_url('images/cat-kids.svg'); ?>" class="w-100 object-fit-cover" style="height: 180px;">
              <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-25"></div>
              <div class="position-absolute bottom-0 start-0 p-3 text-white">
                <h5 class="fw-bold">Kids</h5>
                <a href="<?php echo base_url('user/shop.php?category=Kids'); ?>" class="btn btn-light rounded-pill btn-sm stretched-link">View</a>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="position-relative rounded-4 overflow-hidden shadow-sm">
              <img src="<?php echo asset_url('images/cat-accessories.svg'); ?>" class="w-100 object-fit-cover" style="height: 180px;">
              <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-25"></div>
              <div class="position-absolute bottom-0 start-0 p-3 text-white">
                <h5 class="fw-bold">Accessories</h5>
                <a href="<?php echo base_url('user/shop.php?category=Accessories'); ?>" class="btn btn-light rounded-pill btn-sm stretched-link">View</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Newsletter -->
<section class="py-5" style="background: #1a1a1a;">
  <div class="container text-center py-5">
    <div class="row justify-content-center">
      <div class="col-lg-6">
        <h2 class="text-white fw-bold mb-3">Join Our Newsletter</h2>
        <p class="text-white-50 mb-4">Subscribe to get special offers, free giveaways, and once-in-a-lifetime deals.</p>
        <form class="d-flex gap-2">
          <input type="email" class="form-control form-control-lg rounded-pill" placeholder="Enter your email address">
          <button class="btn btn-primary btn-lg rounded-pill px-4">Subscribe</button>
        </form>
      </div>
    </div>
  </div>
</section>

<?php include("includes/footer.php"); ?>
