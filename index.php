<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config/db.php';

$stmt = $pdo->query("SELECT p.*, u.full_name, c.category_name FROM products p JOIN users u ON p.seller_id = u.user_id JOIN categories c ON p.category_id = c.category_id WHERE p.status = 'active' ORDER BY p.created_at DESC LIMIT 12");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cats = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ZithengeMart - Buy & Sell Anything</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="hero-banner">
  <div class="hero-text">
    <h1>Buy & Sell Anything</h1>
    <p>South Africa's Local C2C Marketplace</p>
    <?php if(isset($_SESSION['user_id'])){ ?>
      <a href="sell.php" class="btn-cta">+ Post a Listing</a>
    <?php } else { ?>
      <a href="register.php" class="btn-cta">Start Selling Today</a>
    <?php } ?>
  </div>
</div>

<section class="categories-section">
  <div class="container">
    <h2 class="section-title">Browse Categories</h2>
    <div class="categories-grid">
      <?php foreach($cats as $cat){ ?>
      <a href="search.php?category=<?php echo $cat['category_id']; ?>" class="category-card">
        <?php echo htmlspecialchars($cat['category_name']); ?>
      </a>
      <?php } ?>
    </div>
  </div>
</section>

<section class="products-section">
  <div class="container">
    <h2 class="section-title">Latest Listings</h2>
    <div class="products-grid">
      <?php if(count($products) > 0){ ?>
        <?php foreach($products as $p){ ?>
        <a href="product.php?id=<?php echo $p['product_id']; ?>" class="product-card">
          <div class="product-img">
            <?php if($p['product_image']){ ?>
              <img src="uploads/products/<?php echo htmlspecialchars($p['product_image']); ?>"
                   alt="<?php echo htmlspecialchars($p['title']); ?>">
            <?php } else { ?>
              <div class="no-img">No Image</div>
            <?php } ?>
          </div>
          <div class="product-info">
            <h3><?php echo htmlspecialchars($p['title']); ?></h3>
            <p class="price">R<?php echo number_format($p['price'], 2); ?></p>
            <p class="location">📍 <?php echo htmlspecialchars($p['location']); ?></p>
            <span class="category-tag"><?php echo htmlspecialchars($p['category_name']); ?></span>
          </div>
        </a>
        <?php } ?>
      <?php } else { ?>
        <p style="color:#999; grid-column:1/-1; padding:40px 0;">
          No listings yet. <a href="sell.php" style="color:#FF6B00; font-weight:700;">Be the first to sell!</a>
        </p>
      <?php } ?>
    </div>
  </div>
</section>

<footer class="footer">
  <div class="container">
    <p>&copy; <?php echo date('2026'); ?> <span>ZithengeMart</span>. All rights reserved.</p>
  </div>
</footer>

</body>
</html>