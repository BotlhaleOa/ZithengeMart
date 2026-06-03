<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit;
}
require_once '../config/db.php';

// Add category
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])){
    $name = trim($_POST['category_name']);
    if(!empty($name)){
        $pdo->prepare("INSERT INTO categories (category_name) VALUES (?)")->execute(array($name));
    }
    header("Location: categories.php?msg=added");
    exit;
}

// Delete category
if(isset($_GET['delete']) && is_numeric($_GET['delete'])){
    $pdo->prepare("DELETE FROM categories WHERE category_id = ?")->execute(array($_GET['delete']));
    header("Location: categories.php?msg=deleted");
    exit;
}

$cats = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.category_id) as product_count FROM categories c ORDER BY c.category_name")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Categories - ZithengeMart Admin</title>
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
      <h2>Manage Categories</h2>
      <p>Add or remove product categories</p>
    </div>

    <?php if(isset($_GET['msg'])){ ?>
      <div class="alert-success">
        <?php echo $_GET['msg'] == 'added' ? 'Category added!' : 'Category deleted.'; ?>
      </div>
    <?php } ?>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:25px; align-items:start;">

      <!-- ADD CATEGORY -->
      <div class="content-card">
        <div class="card-header"><h3>Add New Category</h3></div>
        <form method="POST" action="categories.php">
          <div style="display:flex; gap:10px;">
            <input type="text" name="category_name" class="admin-input" placeholder="e.g. Appliances" style="flex:1;" required>
            <button type="submit" name="add_category" class="btn-sm">Add</button>
          </div>
        </form>
      </div>

      <!-- CATEGORIES LIST -->
      <div class="content-card">
        <div class="card-header"><h3>All Categories (<?php echo count($cats); ?>)</h3></div>
        <table class="admin-table">
          <thead>
            <tr>
              <th>Category Name</th>
              <th>Listings</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($cats as $c){ ?>
            <tr>
              <td><strong><?php echo htmlspecialchars($c['category_name']); ?></strong></td>
              <td><?php echo $c['product_count']; ?></td>
              <td>
                <?php if($c['product_count'] == 0){ ?>
                  <a href="categories.php?delete=<?php echo $c['category_id']; ?>"
                     class="btn-danger"
                     onclick="return confirm('Delete this category?')">Delete</a>
                <?php } else { ?>
                  <span style="color:#aaa; font-size:0.8rem;">Has listings</span>
                <?php } ?>
              </td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</div>
</body>
</html>