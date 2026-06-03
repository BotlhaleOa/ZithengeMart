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
$error = '';
$success = '';

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute(array($user_id));
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch user's listings
$listings = $pdo->prepare("SELECT p.*, c.category_name FROM products p JOIN categories c ON p.category_id = c.category_id WHERE p.seller_id = ? ORDER BY p.created_at DESC");
$listings->execute(array($user_id));
$my_listings = $listings->fetchAll(PDO::FETCH_ASSOC);

// Handle profile update
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])){
    $name  = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);

    if(empty($name)){
        $error = "Full name is required.";
    } else {
        $update = $pdo->prepare("UPDATE users SET full_name = ?, phone = ? WHERE user_id = ?");
        $update->execute(array($name, $phone, $user_id));
        $_SESSION['user_name'] = $name;
        $success = "Profile updated successfully!";
        // Refresh user data
        $stmt->execute(array($user_id));
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Handle listing delete
if(isset($_GET['delete']) && is_numeric($_GET['delete'])){
    $del = $pdo->prepare("DELETE FROM products WHERE product_id = ? AND seller_id = ?");
    $del->execute(array($_GET['delete'], $user_id));
    header("Location: profile.php?deleted=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile - ZithengeMart</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .profile-page {
      padding: 40px 0 60px;
      background: #fafafa;
      min-height: 80vh;
    }
    .profile-grid {
      display: grid;
      grid-template-columns: 300px 1fr;
      gap: 30px;
      align-items: start;
    }
    .profile-card {
      background: white;
      border: 1px solid #e8e8e8;
      border-radius: 14px;
      padding: 30px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .profile-avatar {
      width: 80px;
      height: 80px;
      background: #FF6B00;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2rem;
      color: white;
      font-weight: 800;
      margin: 0 auto 15px;
    }
    .profile-name {
      text-align: center;
      font-size: 1.2rem;
      font-weight: 700;
      margin-bottom: 5px;
    }
    .profile-email {
      text-align: center;
      color: #888;
      font-size: 0.85rem;
      margin-bottom: 20px;
    }
    .profile-stat {
      display: flex;
      justify-content: space-between;
      padding: 10px 0;
      border-top: 1px solid #f0f0f0;
      font-size: 0.9rem;
    }
    .profile-stat span:last-child {
      font-weight: 700;
      color: #FF6B00;
    }
    .section-card {
      background: white;
      border: 1px solid #e8e8e8;
      border-radius: 14px;
      padding: 30px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      margin-bottom: 25px;
    }
    .section-card h3 {
      font-size: 1.1rem;
      font-weight: 800;
      margin-bottom: 20px;
      padding-bottom: 12px;
      border-bottom: 2px solid #f0f0f0;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .section-card h3::before {
      content: '';
      display: block;
      width: 4px;
      height: 18px;
      background: #FF6B00;
      border-radius: 2px;
    }
    .listing-row {
      display: flex;
      align-items: center;
      gap: 15px;
      padding: 12px 0;
      border-bottom: 1px solid #f5f5f5;
    }
    .listing-row:last-child {
      border-bottom: none;
    }
    .listing-thumb {
      width: 65px;
      height: 65px;
      border-radius: 8px;
      overflow: hidden;
      background: #f0f0f0;
      flex-shrink: 0;
    }
    .listing-thumb img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .listing-thumb-placeholder {
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #bbb;
      font-size: 0.7rem;
    }
    .listing-info {
      flex: 1;
    }
    .listing-info h4 {
      font-size: 0.95rem;
      font-weight: 700;
      margin-bottom: 4px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 300px;
    }
    .listing-info p {
      font-size: 0.85rem;
      color: #888;
    }
    .listing-price {
      font-weight: 800;
      color: #FF6B00;
      font-size: 1rem;
      white-space: nowrap;
    }
    .listing-actions {
      display: flex;
      gap: 8px;
    }
    .btn-edit {
      background: #f0f0f0;
      color: #333;
      padding: 6px 14px;
      border-radius: 6px;
      font-size: 0.8rem;
      font-weight: 600;
      text-decoration: none !important;
      transition: background 0.2s;
    }
    .btn-edit:hover {
      background: #e0e0e0;
    }
    .btn-delete {
      background: #fff0f0;
      color: #cc0000;
      padding: 6px 14px;
      border-radius: 6px;
      font-size: 0.8rem;
      font-weight: 600;
      text-decoration: none !important;
      transition: background 0.2s;
    }
    .btn-delete:hover {
      background: #ffe0e0;
    }
    .status-badge {
      display: inline-block;
      padding: 2px 10px;
      border-radius: 20px;
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
    }
    .status-active { background: #e0fff0; color: #006600; }
    .status-pending { background: #fff5e0; color: #cc6600; }
    .status-sold { background: #e0e0ff; color: #0000cc; }
    .status-removed { background: #ffe0e0; color: #cc0000; }
    .no-listings {
      text-align: center;
      padding: 30px;
      color: #aaa;
    }
    .no-listings a {
      color: #FF6B00;
      font-weight: 700;
    }
    @media(max-width: 768px){
      .profile-grid { grid-template-columns: 1fr; }
      .listing-info h4 { max-width: 150px; }
    }
  </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="profile-page">
  <div class="container">

    <?php if(isset($_GET['deleted'])){ ?>
      <div class="alert-error" style="margin-bottom:20px;">Listing deleted successfully.</div>
    <?php } ?>

    <?php if($error != ''){ ?>
      <div class="alert-error" style="margin-bottom:20px;"><?php echo $error; ?></div>
    <?php } ?>

    <?php if($success != ''){ ?>
      <div class="alert-success" style="margin-bottom:20px;"><?php echo $success; ?></div>
    <?php } ?>

    <div class="profile-grid">

      <!-- LEFT: PROFILE CARD -->
      <div>
        <div class="profile-card">
          <div class="profile-avatar">
            <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
          </div>
          <div class="profile-name"><?php echo htmlspecialchars($user['full_name']); ?></div>
          <div class="profile-email"><?php echo htmlspecialchars($user['email']); ?></div>

          <div class="profile-stat">
            <span>Total Listings</span>
            <span><?php echo count($my_listings); ?></span>
          </div>
          <div class="profile-stat">
            <span>Active</span>
            <span><?php echo count(array_filter($my_listings, function($l){ return $l['status'] == 'active'; })); ?></span>
          </div>
          <div class="profile-stat">
            <span>Pending</span>
            <span><?php echo count(array_filter($my_listings, function($l){ return $l['status'] == 'pending'; })); ?></span>
          </div>
          <div class="profile-stat">
            <span>Sold</span>
            <span><?php echo count(array_filter($my_listings, function($l){ return $l['status'] == 'sold'; })); ?></span>
          </div>
          <div class="profile-stat">
            <span>Member Since</span>
            <span><?php echo date('M Y', strtotime($user['created_at'])); ?></span>
          </div>
        </div>
      </div>

      <!-- RIGHT: DETAILS -->
      <div>

        <!-- EDIT PROFILE -->
        <div class="section-card">
          <h3>Edit Profile</h3>
          <form method="POST" action="profile.php">
            <div class="form-group">
              <label>Full Name *</label>
              <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
            </div>
            <div class="form-group">
              <label>Email Address</label>
              <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled style="background:#f5f5f5; color:#888;">
              <small style="color:#aaa; font-size:0.75rem;">Email cannot be changed</small>
            </div>
            <div class="form-group">
              <label>Phone Number</label>
              <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="e.g. 071 234 5678">
            </div>
            <button type="submit" name="update_profile" class="btn-primary" style="width:auto; padding:10px 25px;">
              Save Changes
            </button>
          </form>
        </div>

        <!-- MY LISTINGS -->
        <div class="section-card">
          <h3>My Listings (<?php echo count($my_listings); ?>)</h3>

          <?php if(count($my_listings) > 0){ ?>
            <?php foreach($my_listings as $l){ ?>
            <div class="listing-row">
              <div class="listing-thumb">
                <?php if($l['product_image']){ ?>
                  <img src="uploads/products/<?php echo htmlspecialchars($l['product_image']); ?>" alt="">
                <?php } else { ?>
                  <div class="listing-thumb-placeholder">No img</div>
                <?php } ?>
              </div>
              <div class="listing-info">
                <h4><?php echo htmlspecialchars($l['title']); ?></h4>
                <p>
                  <?php echo htmlspecialchars($l['category_name']); ?> &bull;
                  <?php echo date('d M Y', strtotime($l['created_at'])); ?>
                </p>
                <span class="status-badge status-<?php echo $l['status']; ?>">
                  <?php echo ucfirst($l['status']); ?>
                </span>
              </div>
              <div class="listing-price">R<?php echo number_format($l['price'], 2); ?></div>
              <div class="listing-actions">
                <a href="product.php?id=<?php echo $l['product_id']; ?>" class="btn-edit">View</a>
                <a href="profile.php?delete=<?php echo $l['product_id']; ?>"
                   class="btn-delete"
                   onclick="return confirm('Are you sure you want to delete this listing?')">
                  Delete
                </a>
              </div>
            </div>
            <?php } ?>
          <?php } else { ?>
            <div class="no-listings">
              <p>You have no listings yet.</p>
              <a href="sell.php">Post your first listing</a>
            </div>
          <?php } ?>

        </div>
      </div>
    </div>
  </div>
</div>

<footer class="footer">
  <div class="container">
    <p>&copy; <?php echo date('2026'); ?> <span>ZithengeMart</span>. All rights reserved.</p>
  </div>
</footer>

</body>
</html>