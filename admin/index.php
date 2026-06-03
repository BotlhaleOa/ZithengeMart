<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit;
}
require_once '../config/db.php';

$total_users    = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_listings = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$pending        = $pdo->query("SELECT COUNT(*) FROM products WHERE LOWER(status) = 'pending'")->fetchColumn();
$active         = $pdo->query("SELECT COUNT(*) FROM products WHERE LOWER(status) = 'active'")->fetchColumn();

$recent = $pdo->query("SELECT p.*, u.full_name, c.category_name FROM products p JOIN users u ON p.seller_id = u.user_id JOIN categories c ON p.category_id = c.category_id ORDER BY p.created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - ZithengeMart Admin</title>
  <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<div class="admin-layout">

  <!-- MOBILE TOPBAR -->
  <div class="admin-topbar">
    <span class="admin-topbar-brand">ZITHENGEMART</span>
    <button class="admin-menu-btn" onclick="openAdminMenu()">
      <span></span><span></span><span></span>
    </button>
  </div>

  <!-- SIDEBAR OVERLAY -->
  <div class="admin-sidebar-overlay" id="adminOverlay" onclick="closeAdminMenu()"></div>

  <?php include 'includes/sidebar.php'; ?>

  <div class="main-content">
    <div class="page-header">
      <h2>Dashboard</h2>
      <p>Welcome back, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>
        <span class="role-badge"><?php echo ucfirst($_SESSION['admin_role']); ?></span>
      </p>
    </div>

    <!-- STATS -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-number"><?php echo $total_users; ?></div>
        <div class="stat-label">Total Users</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-number"><?php echo $total_listings; ?></div>
        <div class="stat-label">Total Listings</div>
      </div>
      <div class="stat-card orange">
        <div class="stat-icon">⏳</div>
        <div class="stat-number"><?php echo $pending; ?></div>
        <div class="stat-label">Pending Approval</div>
      </div>
      <div class="stat-card green">
        <div class="stat-icon">✅</div>
        <div class="stat-number"><?php echo $active; ?></div>
        <div class="stat-label">Active Listings</div>
      </div>
    </div>

    <!-- RECENT LISTINGS -->
    <div class="content-card">
      <div class="card-header">
        <h3>Recent Listings</h3>
        <a href="listings.php" class="btn-sm">View All</a>
      </div>
      <div style="overflow-x:auto;">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Title</th>
              <th>Seller</th>
              <th>Category</th>
              <th>Price</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($recent as $r){ ?>
            <tr>
              <td><?php echo htmlspecialchars($r['title']); ?></td>
              <td><?php echo htmlspecialchars($r['full_name']); ?></td>
              <td><?php echo htmlspecialchars($r['category_name']); ?></td>
              <td>R<?php echo number_format($r['price'], 2); ?></td>
              <td><span class="status-badge status-<?php echo strtolower($r['status']); ?>"><?php echo ucfirst($r['status']); ?></span></td>
              <td><?php echo date('d M Y', strtotime($r['created_at'])); ?></td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<script>
function openAdminMenu(){
  document.querySelector('.sidebar').classList.add('mobile-open');
  document.getElementById('adminOverlay').classList.add('active');
  document.body.style.overflow = 'hidden';
}
function closeAdminMenu(){
  document.querySelector('.sidebar').classList.remove('mobile-open');
  document.getElementById('adminOverlay').classList.remove('active');
  document.body.style.overflow = '';
}
</script>

</body>
</html>
	  
	  
	  