<?php 
session_start();
include("../includes/header.php"); 
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8 text-center mb-5">
      <h2 class="fw-bold mb-3">Get in Touch</h2>
      <p class="text-muted">Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
    </div>
  </div>

  <div class="row justify-content-center g-5">
    <div class="col-lg-5">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body p-4 p-lg-5">
          <div class="d-flex align-items-center mb-4">
            <div class="bg-light p-3 rounded-circle me-3 text-primary"><i class="bi bi-geo-alt fs-4"></i></div>
            <div>
              <h6 class="fw-bold mb-0">Our Location</h6>
              <p class="text-muted mb-0 small">123 Fashion Ave, New York, NY 10001</p>
            </div>
          </div>
          
          <div class="d-flex align-items-center mb-4">
            <div class="bg-light p-3 rounded-circle me-3 text-primary"><i class="bi bi-envelope fs-4"></i></div>
            <div>
              <h6 class="fw-bold mb-0">Email Us</h6>
              <p class="text-muted mb-0 small">support@almasclothing.com</p>
            </div>
          </div>
          
          <div class="d-flex align-items-center mb-4">
            <div class="bg-light p-3 rounded-circle me-3 text-primary"><i class="bi bi-telephone fs-4"></i></div>
            <div>
              <h6 class="fw-bold mb-0">Call Us</h6>
              <p class="text-muted mb-0 small">+1 (555) 123-4567</p>
            </div>
          </div>

          <div class="d-flex align-items-center">
            <div class="bg-light p-3 rounded-circle me-3 text-primary"><i class="bi bi-clock fs-4"></i></div>
            <div>
              <h6 class="fw-bold mb-0">Working Hours</h6>
              <p class="text-muted mb-0 small">Mon - Fri: 9:00 AM - 6:00 PM</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-lg-5">
          <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
              <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          <?php endif; ?>

          <form action="contact_action.php" method="POST">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
              </div>
              <div class="col-12">
                <label class="form-label">Subject</label>
                <input type="text" name="subject" class="form-control" required>
              </div>
              <div class="col-12">
                <label class="form-label">Message</label>
                <textarea name="message" class="form-control" rows="5" required></textarea>
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-primary w-100 py-2">Send Message</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include("../includes/footer.php"); ?>
