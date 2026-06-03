<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit;
}
require_once '../config/db.php';

// Approve listing
if(isset($_GET['approve']) && is_numeric($_GET['approve'])){
    $pdo->prepare("UPDATE products SET status = 'active' WHERE product_id = ?")->execute(array($_GET['approve']));
    header("Location: listings.php?msg=approved");
    exit;
}

// Remove listing
if(isset($_GET['remove']) && is_numeric($_GET['remove'])){
    $pdo->prepare("UPDATE products SET status = 'removed' WHERE product_id = ?")->execute(array($_GET['remove']));
    header("Location: listings.php?msg=removed");
    exit;
}

// Filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$sql = "SELECT p.*, u.full_name, c.category_name FROM products p JOIN users u ON p.seller_id = u.user_id JOIN categories c ON p.category_id = c.category_id";
if($filter != 'all'){
    $sql .= " WHERE p.status = ?";
    $listings = $pdo->prepare($sql . " ORDER BY p.created_at DESC");
    $listings->execute(array($filter));
    $listings = $listings->fetchAll(PDO::FETCH_ASSOC);
} else {
    $listings = $pdo->query($sql . " ORDER BY p.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Listings - ZithengeMart Admin</title>
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
  
  <div class="main-content">
    <div class="page-header">
      <h2>Manage Listings</h2>
      <p>Approve, remove or monitor all product listings</p>
    </div>

    <?php if(isset($_GET['msg'])){ ?>
      <div class="alert-success">
        <?php echo $_GET['msg'] == 'approved' ? 'Listing approved successfully!' : 'Listing removed.'; ?>
      </div>
    <?php } ?>

    <!-- FILTER TABS -->
    <div class="content-card">
      <div class="card-header">
        <h3>All Listings (<?php echo count($listings); ?>)</h3>
        <div style="display:flex; gap:8px;">
          <a href="listings.php?filter=all" class="btn-sm <?php echo $filter=='all'?'':'btn-sm-outline'; ?>">All</a>
          <a href="listings.php?filter=pending" class="btn-sm <?php echo $filter=='pending'?'':'btn-sm-outline'; ?>">Pending</a>
          <a href="listings.php?filter=active" class="btn-sm <?php echo $filter=='active'?'':'btn-sm-outline'; ?>">Active</a>
          <a href="listings.php?filter=removed" class="btn-sm <?php echo $filter=='removed'?'':'btn-sm-outline'; ?>">Removed</a>
        </div>
      </div>

      <table class="admin-table">
        <thead>
          <tr>
            <th>Image</th>
            <th>Title</th>
            <th>Seller</th>
            <th>Category</th>
            <th>Price</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($listings as $l){ ?>
          <tr>
            <td>
              <?php if($l['product_image']){ ?>
                <img src="../uploads/products/<?php echo htmlspecialchars($l['product_image']); ?>"
                     style="width:50px; height:50px; object-fit:cover; border-radius:6px;">
              <?php } else { ?>
                <div style="width:50px; height:50px; background:#f0f0f0; border-radius:6px; display:flex; align-items:center; justify-content:center; color:#bbb; font-size:0.7rem;">No img</div>
              <?php } ?>
            </td>
            <td><?php echo htmlspecialchars($l['title']); ?></td>
            <td><?php echo htmlspecialchars($l['full_name']); ?></td>
            <td><?php echo htmlspecialchars($l['category_name']); ?></td>
            <td>R<?php echo number_format($l['price'], 2); ?></td>
            <td><span class="status-badge status-<?php echo $l['status']; ?>"><?php echo ucfirst($l['status']); ?></span></td>
            <td><?php echo date('d M Y', strtotime($l['created_at'])); ?></td>
            <td>
              <div style="display:flex; gap:6px; flex-wrap:wrap;">
                <?php if($l['status'] == 'pending'){ ?>
                  <a href="listings.php?approve=<?php echo $l['product_id']; ?>" class="btn-sm">Approve</a>
                <?php } ?>
                <?php if($l['status'] != 'removed'){ ?>
                  <a href="listings.php?remove=<?php echo $l['product_id']; ?>"
                     class="btn-danger"
                     onclick="return confirm('Remove this listing?')">Remove</a>
                <?php } ?>
              </div>
            </td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>