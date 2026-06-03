<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config/db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Add to cart
if(isset($_GET['add']) && is_numeric($_GET['add'])){
    $product_id = intval($_GET['add']);

    // Check if already in cart
    $check = $pdo->prepare("SELECT cart_id FROM cart WHERE user_id = ? AND product_id = ?");
    $check->execute(array($user_id, $product_id));

    if($check->rowCount() == 0){
        $add = $pdo->prepare("INSERT INTO cart (user_id, product_id) VALUES (?, ?)");
        $add->execute(array($user_id, $product_id));
    }
    header("Location: cart.php?added=1");
    exit;
}

// Remove from cart
if(isset($_GET['remove']) && is_numeric($_GET['remove'])){
    $remove = $pdo->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    $remove->execute(array($_GET['remove'], $user_id));
    header("Location: cart.php?removed=1");
    exit;
}

// Fetch cart items
$stmt = $pdo->prepare("SELECT c.cart_id, p.product_id, p.title, p.price, p.location, p.product_image, p.status, u.full_name, u.email, u.phone, cat.category_name
                        FROM cart c
                        JOIN products p ON c.product_id = p.product_id
                        JOIN users u ON p.seller_id = u.user_id
                        JOIN categories cat ON p.category_id = cat.category_id
                        WHERE c.user_id = ?
                        ORDER BY c.added_at DESC");
$stmt->execute(array($user_id));
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total
$total = 0;
foreach($cart_items as $item){
    $total += $item['price'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Cart - ZithengeMart</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .cart-page {
      padding: 40px 0 60px;
      background: #fafafa;
      min-height: 80vh;
    }
    .cart-grid {
      display: grid;
      grid-template-columns: 1fr 320px;
      gap: 25px;
      align-items: start;
    }
    .cart-card {
      background: white;
      border: 1px solid #e8e8e8;
      border-radius: 14px;
      padding: 25px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      margin-bottom: 20px;
    }
    .cart-card h3 {
      font-size: 1.1rem;
      font-weight: 800;
      margin-bottom: 20px;
      padding-bottom: 12px;
      border-bottom: 2px solid #f0f0f0;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .cart-card h3::before {
      content: '';
      display: block;
      width: 4px;
      height: 18px;
      background: #FF6B00;
      border-radius: 2px;
    }
    .cart-item {
      display: flex;
      gap: 15px;
      padding: 15px 0;
      border-bottom: 1px solid #f5f5f5;
      align-items: center;
    }
    .cart-item:last-child {
      border-bottom: none;
    }
    .cart-item-img {
      width: 80px;
      height: 80px;
      border-radius: 10px;
      overflow: hidden;
      background: #f0f0f0;
      flex-shrink: 0;
    }
    .cart-item-img img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .cart-item-placeholder {
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #bbb;
      font-size: 0.75rem;
    }
    .cart-item-info {
      flex: 1;
    }
    .cart-item-info h4 {
      font-size: 1rem;
      font-weight: 700;
      margin-bottom: 5px;
    }
    .cart-item-info h4 a {
      color: #111;
      text-decoration: none !important;
    }
    .cart-item-info h4 a:hover {
      color: #FF6B00;
    }
    .cart-item-info p {
      font-size: 0.82rem;
      color: #888;
      margin-bottom: 4px;
    }
    .cart-item-price {
      font-size: 1.1rem;
      font-weight: 800;
      color: #FF6B00;
      white-space: nowrap;
      margin-right: 15px;
    }
    .btn-remove {
      background: #fff0f0;
      color: #cc0000;
      padding: 7px 14px;
      border-radius: 6px;
      font-size: 0.8rem;
      font-weight: 600;
      text-decoration: none !important;
      white-space: nowrap;
      transition: background 0.2s;
    }
    .btn-remove:hover {
      background: #ffe0e0;
    }
    .seller-contact-box {
      background: #f9f9f9;
      border-radius: 8px;
      padding: 10px 12px;
      margin-top: 6px;
      font-size: 0.82rem;
      color: #555;
    }
    .seller-contact-box a {
      color: #FF6B00;
      font-weight: 600;
      text-decoration: none !important;
    }
    .summary-card {
      background: white;
      border: 1px solid #e8e8e8;
      border-radius: 14px;
      padding: 25px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      position: sticky;
      top: 100px;
    }
    .summary-card h3 {
      font-size: 1.1rem;
      font-weight: 800;
      margin-bottom: 20px;
      padding-bottom: 12px;
      border-bottom: 2px solid #f0f0f0;
    }
    .summary-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 12px;
      font-size: 0.9rem;
      color: #555;
    }
    .summary-total {
      display: flex;
      justify-content: space-between;
      font-size: 1.1rem;
      font-weight: 800;
      padding-top: 12px;
      border-top: 2px solid #f0f0f0;
      margin-top: 5px;
    }
    .summary-total span:last-child {
      color: #FF6B00;
    }
    .summary-note {
      font-size: 0.78rem;
      color: #aaa;
      text-align: center;
      margin-top: 15px;
      line-height: 1.5;
    }
    .empty-cart {
      text-align: center;
      padding: 60px 20px;
      color: #aaa;
    }
    .empty-cart h3 {
      font-size: 1.3rem;
      margin-bottom: 10px;
      color: #444;
    }
    .empty-cart a {
      color: #FF6B00;
      font-weight: 700;
      text-decoration: none !important;
    }
    .empty-cart .cart-icon {
      font-size: 4rem;
      margin-bottom: 15px;
    }
    @media(max-width: 768px){
      .cart-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="cart-page">
  <div class="container">

    <h2 class="section-title" style="margin-bottom:25px;">My Saved Items</h2>

    <?php if(isset($_GET['added'])){ ?>
      <div class="alert-success" style="margin-bottom:20px;">Item added to your saved list!</div>
    <?php } ?>
    <?php if(isset($_GET['removed'])){ ?>
      <div class="alert-error" style="margin-bottom:20px;">Item removed from your saved list.</div>
    <?php } ?>

    <?php if(count($cart_items) > 0){ ?>

    <div class="cart-grid">

      <!-- LEFT: CART ITEMS -->
      <div>
        <div class="cart-card">
          <h3>Saved Items (<?php echo count($cart_items); ?>)</h3>

          <?php foreach($cart_items as $item){ ?>
          <div class="cart-item">

            <div class="cart-item-img">
              <?php if($item['product_image']){ ?>
                <img src="uploads/products/<?php echo htmlspecialchars($item['product_image']); ?>" alt="">
              <?php } else { ?>
                <div class="cart-item-placeholder">No img</div>
              <?php } ?>
            </div>

            <div class="cart-item-info">
              <h4>
                <a href="product.php?id=<?php echo $item['product_id']; ?>">
                  <?php echo htmlspecialchars($item['title']); ?>
                </a>
              </h4>
              <p>📁 <?php echo htmlspecialchars($item['category_name']); ?> &bull; 📍 <?php echo htmlspecialchars($item['location']); ?></p>
              <p>Seller: <strong><?php echo htmlspecialchars($item['full_name']); ?></strong></p>
              <div class="seller-contact-box">
                <?php if(!empty($item['phone'])){ ?>
                  📞 <?php echo htmlspecialchars($item['phone']); ?> &nbsp;&bull;&nbsp;
                <?php } ?>
                ✉️ <a href="mailto:<?php echo htmlspecialchars($item['email']); ?>">
                  <?php echo htmlspecialchars($item['email']); ?>
                </a>
              </div>
            </div>

            <div class="cart-item-price">R<?php echo number_format($item['price'], 2); ?></div>

            <a href="cart.php?remove=<?php echo $item['cart_id']; ?>"
               class="btn-remove"
               onclick="return confirm('Remove this item?')">
              Remove
            </a>

          </div>
          <?php } ?>
        </div>
      </div>

      <!-- RIGHT: SUMMARY -->
      <div>
        <div class="summary-card">
          <h3>Summary</h3>
          <div class="summary-row">
            <span>Items</span>
            <span><?php echo count($cart_items); ?></span>
          </div>
          <div class="summary-total">
            <span>Total Value</span>
            <span>R<?php echo number_format($total, 2); ?></span>
          </div>
          <p class="summary-note">
            This is a C2C platform. Contact each seller directly to arrange payment and delivery.
          </p>
        </div>
      </div>

    </div>

    <?php } else { ?>
      <div class="cart-card">
        <div class="empty-cart">
          <div class="cart-icon">🛒</div>
          <h3>Your saved list is empty</h3>
          <p>Browse listings and save items you are interested in.</p>
          <br>
          <a href="index.php">Browse Listings</a>
        </div>
      </div>
    <?php } ?>

  </div>
</div>

<footer class="footer">
  <div class="container">
    <p>&copy; <?php echo date('2026'); ?> <span>ZithengeMart</span>. All rights reserved.</p>
  </div>
</footer>

</body>
</html>