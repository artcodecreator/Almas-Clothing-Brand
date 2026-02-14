<?php 
session_start();
include("../includes/db.php");

$page = isset($_GET['page']) ? trim($_GET['page']) : 'faq';
$title = "Page Not Found";
$content = "The page you are looking for does not exist.";

switch ($page) {
    case 'faq':
        $title = "Frequently Asked Questions";
        $content = '
            <div class="accordion" id="faqAccordion">
              <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                  <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                    What payment methods do you accept?
                  </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                  <div class="accordion-body">
                    We accept Visa, MasterCard, and Cash on Delivery (COD).
                  </div>
                </div>
              </div>
              <div class="accordion-item">
                <h2 class="accordion-header" id="headingTwo">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                    How long does shipping take?
                  </button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                  <div class="accordion-body">
                    Standard shipping takes 3-5 business days. Express shipping is available for select locations.
                  </div>
                </div>
              </div>
              <div class="accordion-item">
                <h2 class="accordion-header" id="headingThree">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                    What is your return policy?
                  </button>
                </h2>
                <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                  <div class="accordion-body">
                    You can return items within 30 days of receipt if they are unused and in original packaging.
                  </div>
                </div>
              </div>
            </div>
        ';
        break;

    case 'shipping':
        $title = "Shipping & Returns";
        $content = '
            <h4>Shipping Policy</h4>
            <p>We ship nationwide. Orders are processed within 24 hours.</p>
            <ul>
                <li>Standard Shipping: PKR 200 (Free for orders over PKR 5000)</li>
                <li>Express Shipping: PKR 500</li>
            </ul>
            
            <h4 class="mt-4">Return Policy</h4>
            <p>If you are not completely satisfied with your purchase, you can return the item to us in its original condition within 30 days of receipt.</p>
            <p>Please contact our support team to initiate a return.</p>
        ';
        break;

    case 'privacy':
        $title = "Privacy Policy";
        $content = '
            <p>Your privacy is important to us. This policy explains how we collect and use your personal information.</p>
            <h5>Information We Collect</h5>
            <p>We collect information you provide directly to us, such as when you create an account, place an order, or contact us.</p>
            <h5>How We Use Information</h5>
            <p>We use your information to process orders, provide customer support, and improve our services.</p>
            <h5>Data Security</h5>
            <p>We implement appropriate security measures to protect your personal information.</p>
        ';
        break;
}

include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h2 class="mb-4 fw-bold text-center"><?php echo $title; ?></h2>
            <div class="bg-white p-4 rounded shadow-sm">
                <?php echo $content; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
