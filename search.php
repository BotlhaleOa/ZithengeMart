<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config/db.php';

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$cat_id = isset($_GET['category']) ? intval($_GET['category']) : 0;

$cats = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT p.*, u.full_name, c.category_name 
        FROM products p 
        JOIN users u ON p.seller_id = u.user_id 
        JOIN categories c ON p.category_id = c.category_id 
        WHERE p.status = 'active'";

$params = array();

if(!empty($search)){
    $sql .= " AND (p.title LIKE ? OR p.description LIKE ? OR p.location LIKE ?)";
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

if($cat_id > 0){
    $sql .= " AND p.category_id = ?";
    $params[] = $cat_id;
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Search - ZithengeMart</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .search-page {
      padding: 40px 0 60px;
    }
    .search-header {
      margin-bottom: 25px;
    }
    .search-header h2 {
      font-size: 1.5rem;
      border-left: 4px solid #FF6B00;
      padding-left: 12px;
      margin-bottom: 5px;
    }
    .search-header p {
      color: #777;
      font-size: 0.9rem;
      padding-left: 16px;
    }
    .search-filters {
      background: #f9f9f9;
      border: 1px solid #eee;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 30px;
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
      align-items: flex-end;
    }
    .filter-group {
      display: flex;
      flex-direction: column;
      gap: 6px;
      flex: 1;
      min-width: 180px;
    }
    .filter-group label {
      font-size: 0.85rem;
      font-weight: 600;
      color: #444;
    }
    .filter-group input,
    .filter-group select {
      padding: 9px 12px;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: 0.9rem;
    }
    .filter-group input:focus,
    .filter-group select:focus {
      border-color: #FF6B00;
      outline: none;
    }
    .btn-search {
      background: #FF6B00;
      color: white;
      border: none;
      padding: 10px 25px;
      border-radius: 6px;
      font-weight: 700;
      cursor: pointer;
      font-size: 0.95rem;
      height: 40px;
    }
    .btn-search:hover {
      background: #e05e00;
    }
    .no-results {
      text-align: center;
      padding: 60px 20px;
      color: #777;
    }
    .no-results h3 {
      font-size: 1.3rem;
      margin-bottom: 10px;
      color: #444;
    }
    .no-results a {
      color: #FF6B00;
      font-weight: 600;
    }
  </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="search-page">
  <div class="container">

    <div class="search-header">
      <h2>
        <?php if(!empty($search)){ ?>
          Search results for: "<?php echo htmlspecialchars($search); ?>"
        <?php } elseif($cat_id > 0){ ?>
          Browsing Category
        <?php } else { ?>
          All Listings
        <?php } ?>
      </h2>
      <p><?php echo count($results); ?> listing(s) found</p>
    </div>

    <!-- FILTERS -->
    <form method="GET" action="search.php">
      <div class="search-filters">
        <div class="filter-group">
          <label>Search</label>
          <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search listings...">
        </div>
        <div class="filter-group">
          <label>Category</label>
          <select name="category">
            <option value="">All Categories</option>
            <?php
            foreach($cats as $c){
                $selected = ($cat_id == $c['category_id']) ? 'selected' : '';
                echo '<option value="' . $c['category_id'] . '" ' . $selected . '>' . htmlspecialchars($c['category_name']) . '</option>';
            }
            ?>
          </select>
        </div>
        <div class="filter-group">
          <label>&nbsp;</label>
          <button type="submit" class="btn-search">Search</button>
        </div>
      </div>
    </form>

    <!-- RESULTS -->
    <?php if(count($results) > 0){ ?>
      <div class="products-grid">
        <?php foreach($results as $p){ ?>
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
      </div>

    <?php } else { ?>
      <div class="no-results">
        <h3>No listings found</h3>
        <?php if(!empty($search)){ ?>
          <p>No results for "<?php echo htmlspecialchars($search); ?>". Try a different keyword.</p>
        <?php } else { ?>
          <p>No listings available yet.</p>
        <?php } ?>
        <br>
        <a href="index.php">Back to Homepage</a>
      </div>
    <?php } ?>

  </div>
</div>

<footer class="footer">
  <div class="container">
    <p>&copy; <?php echo date('2026'); ?> ZithengeMart. All rights reserved.</p>
  </div>
</footer>

</body>
</html>