<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config/db.php';

if(!isset($_GET['id'])){
    header("Location: index.php");
    exit;
}

$product_id = intval($_GET['id']);

$stmt = $pdo->prepare("SELECT p.*, u.full_name, u.phone, u.email, c.category_name 
                        FROM products p 
                        JOIN users u ON p.seller_id = u.user_id 
                        JOIN categories c ON p.category_id = c.category_id 
                        WHERE p.product_id = ? AND p.status = 'active'");
$stmt->execute(array($product_id));
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$product){
    header("Location: index.php");
    exit;
}

// Fetch related products from same category
$related = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND product_id != ? AND status = 'active' LIMIT 4");
$related->execute(array($product['category_id'], $product_id));
$related_products = $related->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($product['title']); ?> - ZithengeMart</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .product-detail-page {
      padding: 40px 0 60px;
    }
    .product-detail-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 40px;
      align-items: start;
    }
    .product-detail-img {
      width: 100%;
      border-radius: 12px;
      overflow: hidden;
      background: #f0f0f0;
      aspect-ratio: 1/1;
    }
    .product-detail-img img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .no-img-large {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 400px;
      color: #aaa;
      font-size: 1rem;
    }
    .product-detail-info h1 {
      font-size: 1.6rem;
      font-weight: 800;
      margin-bottom: 10px;
    }
    .product-detail-price {
      font-size: 2rem;
      font-weight: 800;
      color: #FF6B00;
      margin-bottom: 15px;
    }
    .product-meta {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-bottom: 20px;
    }
    .product-meta span {
      background: #f5f5f5;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      color: #555;
    }
    .product-description {
      margin-bottom: 25px;
      line-height: 1.7;
      color: #444;
    }
    .seller-box {
      background: #f9f9f9;
      border: 1px solid #eee;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 20px;
    }
    .seller-box h3 {
      font-size: 1rem;
      color: #777;
      margin-bottom: 12px;
      text-transform: uppercase;
      letter-spacing: 1px;
      font-size: 0.8rem;
    }
    .seller-name {
      font-size: 1.1rem;
      font-weight: 700;
      margin-bottom: 8px;
    }
    .seller-contact {
      font-size: 0.9rem;
      color: #555;
      margin-bottom: 5px;
    }
    .btn-contact {
      display: block;
      background: #FF6B00;
      color: white;
      text-align: center;
      padding: 13px;
      border-radius: 6px;
      font-weight: 700;
      font-size: 1rem;
      margin-top: 15px;
    }
    .btn-contact:hover {
      background: #e05e00;
    }
    .btn-whatsapp {
      display: block;
      background: #25D366;
      color: white;
      text-align: center;
      padding: 13px;
      border-radius: 6px;
      font-weight: 700;
      font-size: 1rem;
      margin-top: 10px;
    }
    .btn-whatsapp:hover {
      background: #1ebe5c;
    }
    .related-section {
      padding: 40px 0 60px;
      border-top: 1px solid #eee;
    }
    .related-section h2 {
      font-size: 1.4rem;
      margin-bottom: 20px;
      border-left: 4px solid #FF6B00;
      padding-left: 12px;
    }
    @media(max-width: 768px){
      .product-detail-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="product-detail-page">
  <div class="container">

    <!-- BACK LINK -->
    <a href="index.php" style="color:#FF6B00; font-size:0.9rem; display:inline-block; margin-bottom:20px;">
      &larr; Back to Listings
    </a>

    <div class="product-detail-grid">

      <!-- LEFT: IMAGE -->
      <div class="product-detail-img">
        <?php if($product['product_image']){ ?>
          <img src="uploads/products/<?php echo htmlspecialchars($product['product_image']); ?>" 
               alt="<?php echo htmlspecialchars($product['title']); ?>">
        <?php } else { ?>
          <div class="no-img-large">No Image Available</div>
        <?php } ?>
      </div>

      <!-- RIGHT: INFO -->
      <div class="product-detail-info">

        <div class="product-meta">
          <span>📁 <?php echo htmlspecialchars($product['category_name']); ?></span>
          <span>📍 <?php echo htmlspecialchars($product['location']); ?></span>
          <span>🕒 <?php echo date('d M Y', strtotime($product['created_at'])); ?></span>
        </div>

        <h1><?php echo htmlspecialchars($product['title']); ?></h1>

        <div class="product-detail-price">
          R<?php echo number_format($product['price'], 2); ?>
        </div>

        <div class="product-description">
          <?php if(!empty($product['description'])){ ?>
            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
          <?php } else { ?>
            <p style="color:#aaa;">No description provided.</p>
          <?php } ?>
        </div>

        <!-- SELLER BOX -->
        <div class="seller-box">
          <h3>Seller Information</h3>
          <div class="seller-name">
            👤 <?php echo htmlspecialchars($product['full_name']); ?>
          </div>
          <?php if(!empty($product['phone'])){ ?>
            <div class="seller-contact">
              📞 <?php echo htmlspecialchars($product['phone']); ?>
            </div>
          <?php } ?>
          <div class="seller-contact">
            ✉️ <?php echo htmlspecialchars($product['email']); ?>
          </div>

          <?php if(!empty($product['phone'])){ ?>
            <a href="https://wa.me/27<?php echo ltrim(preg_replace('/\s+/', '', $product['phone']), '0'); ?>" 
               class="btn-whatsapp" target="_blank">
              💬 WhatsApp Seller
            </a>
          <?php } ?>
		  
          <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] != $product['seller_id']){ ?>
            <a href="cart.php?add=<?php echo $product['product_id']; ?>" class="btn-contact" style="background:#111; margin-bottom:5px;">
              🛒 Save Item
           </a>
          <?php } ?>
		  <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] != $product['seller_id']){ ?>
            <a href="messages.php?conversation=<?php echo $product['seller_id']; ?>&product=<?php echo $product['product_id']; ?>"
               class="btn-contact" style="background:#5b2d8e; margin-bottom:5px;">
              💬 Message Seller
            </a>
          <?php } ?>
          <a href="mailto:<?php echo htmlspecialchars($product['email']); ?>" class="btn-contact">
            ✉️ Email Seller
          </a>
        </div>

      </div>
    </div>

  </div>
</div>

<!-- RELATED PRODUCTS -->
<?php if(count($related_products) > 0){ ?>
<section class="related-section">
  <div class="container">
    <h2>Related Listings</h2>
    <div class="products-grid">
      <?php foreach($related_products as $r){ ?>
      <a href="product.php?id=<?php echo $r['product_id']; ?>" class="product-card">
        <div class="product-img">
          <?php if($r['product_image']){ ?>
            <img src="uploads/products/<?php echo htmlspecialchars($r['product_image']); ?>" 
                 alt="<?php echo htmlspecialchars($r['title']); ?>">
          <?php } else { ?>
            <div class="no-img">No Image</div>
          <?php } ?>
        </div>
        <div class="product-info">
          <h3><?php echo htmlspecialchars($r['title']); ?></h3>
          <p class="price">R<?php echo number_format($r['price'], 2); ?></p>
          <p class="location">📍 <?php echo htmlspecialchars($r['location']); ?></p>
        </div>
      </a>
      <?php } ?>
    </div>
  </div>
</section>
<?php } ?>

<footer class="footer">
  <div class="container">
    <p>&copy; <?php echo date('2026'); ?> ZithengeMart. All rights reserved.</p>
  </div>
</footer>

</body>
</html>
		  
  

   
     	 
	 
	 
	  
						
	