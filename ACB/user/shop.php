<?php 
session_start();
include("../includes/db.php");
include '../includes/header.php';

// Parameters
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 9;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$category = isset($_GET['category']) ? trim($_GET['category']) : "";
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : "newest";
$min_price = isset($_GET['min_price']) ? (int)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (int)$_GET['max_price'] : 100000;

// Build Query
$where_clauses = ["1=1"];
$params = [];
$types = "";

if ($search) {
    $where_clauses[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

if ($category) {
    $where_clauses[] = "c.name = ?";
    $params[] = $category;
    $types .= "s";
}

$where_clauses[] = "p.price BETWEEN ? AND ?";
$params[] = $min_price;
$params[] = $max_price;
$types .= "ii";

$where_sql = implode(" AND ", $where_clauses);

// Sorting
$order_by = "p.created_at DESC";
switch ($sort) {
    case 'price_low': $order_by = "p.price ASC"; break;
    case 'price_high': $order_by = "p.price DESC"; break;
    case 'oldest': $order_by = "p.created_at ASC"; break;
}

// Count Query
$count_sql = "SELECT COUNT(*) FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE $where_sql";
$stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$stmt->bind_result($total_products);
$stmt->fetch();
$stmt->close();

// Main Query
$sql = "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE $where_sql ORDER BY $order_by LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$params[] = $limit;
$params[] = $offset;
$types .= "ii";
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$total_pages = ceil($total_products / $limit);

// Get Categories for Sidebar
$cats = $conn->query("SELECT * FROM categories");
?>

<div class="bg-light py-5">
  <div class="container">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-lg-3 mb-4">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <h5 class="fw-bold mb-3">Filters</h5>
            <form action="shop.php" method="GET">
              <?php if($search): ?><input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>"><?php endif; ?>
              
              <!-- Categories -->
              <div class="mb-4">
                <h6 class="fw-semibold">Category</h6>
                <div class="d-flex flex-column gap-2">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="category" value="" id="cat_all" <?php echo $category == '' ? 'checked' : ''; ?> onchange="this.form.submit()">
                    <label class="form-check-label" for="cat_all">All Categories</label>
                  </div>
                  <?php while($c = $cats->fetch_assoc()): ?>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="category" value="<?php echo $c['name']; ?>" id="cat_<?php echo $c['id']; ?>" <?php echo $category == $c['name'] ? 'checked' : ''; ?> onchange="this.form.submit()">
                    <label class="form-check-label" for="cat_<?php echo $c['id']; ?>"><?php echo $c['name']; ?></label>
                  </div>
                  <?php endwhile; ?>
                </div>
              </div>

              <!-- Price Range -->
              <div class="mb-4">
                <h6 class="fw-semibold">Price Range</h6>
                <div class="d-flex align-items-center gap-2 mb-2">
                  <input type="number" name="min_price" class="form-control form-control-sm" placeholder="Min" value="<?php echo $min_price; ?>">
                  <span>-</span>
                  <input type="number" name="max_price" class="form-control form-control-sm" placeholder="Max" value="<?php echo $max_price; ?>">
                </div>
                <button type="submit" class="btn btn-dark btn-sm w-100">Apply</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Product Grid -->
      <div class="col-lg-9">
        <!-- Toolbar -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <p class="mb-0 text-muted">Showing <?php echo $result->num_rows; ?> of <?php echo $total_products; ?> results</p>
          <form method="GET" class="d-flex align-items-center gap-2">
            <?php foreach($_GET as $key => $val): if($key!='sort' && $key!='page'): ?>
              <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($val); ?>">
            <?php endif; endforeach; ?>
            <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
              <option value="newest" <?php echo $sort=='newest'?'selected':''; ?>>Newest First</option>
              <option value="price_low" <?php echo $sort=='price_low'?'selected':''; ?>>Price: Low to High</option>
              <option value="price_high" <?php echo $sort=='price_high'?'selected':''; ?>>Price: High to Low</option>
            </select>
          </form>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
          <?php if ($result->num_rows > 0): ?>
            <?php while ($product = $result->fetch_assoc()): ?>
              <div class="col">
                <div class="card h-100 product-card shadow-sm">
                  <!-- Wishlist Button -->
                  <form action="wishlist_action.php" method="POST" class="position-absolute" style="top:10px; right:10px; z-index:10;">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <input type="hidden" name="action" value="add">
                    <button type="submit" class="btn btn-light rounded-circle shadow-sm p-0 d-flex align-items-center justify-content-center" style="width:35px; height:35px;">
                      <i class="bi bi-heart"></i>
                    </button>
                  </form>

                  <a href="product_details.php?id=<?php echo $product['id']; ?>" class="text-decoration-none">
                    <img src="<?php echo htmlspecialchars(image_or_placeholder((string)($product['image_url'] ?? ''))); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                  </a>
                  
                  <div class="card-body d-flex flex-column">
                    <div class="text-muted small mb-1"><?php echo htmlspecialchars($product['category_name']); ?></div>
                    <h5 class="card-title text-dark fw-bold mb-1">
                      <a href="product_details.php?id=<?php echo $product['id']; ?>" class="text-decoration-none text-dark">
                        <?php echo htmlspecialchars($product['name']); ?>
                      </a>
                    </h5>
                    <div class="mb-3">
                      <span class="text-primary fw-bold">PKR <?php echo number_format($product['price']); ?></span>
                    </div>
                    
                    <div class="mt-auto">
                      <form action="add_to_cart.php" method="post" class="d-grid">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btn btn-outline-dark btn-sm">Add to Cart</button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="col-12 text-center py-5">
              <i class="bi bi-search display-1 text-muted"></i>
              <h4 class="mt-3">No products found</h4>
              <p class="text-muted">Try adjusting your filters or search query.</p>
              <a href="shop.php" class="btn btn-primary mt-2">Clear Filters</a>
            </div>
          <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav class="mt-5">
          <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
              <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                  <?php echo $i; ?>
                </a>
              </li>
            <?php endfor; ?>
          </ul>
        </nav>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include("../includes/footer.php"); ?>
