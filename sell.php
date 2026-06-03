<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config/db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

$cats = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $title    = trim($_POST['title']);
    $desc     = isset($_POST['description']) ? trim($_POST['description']) : '';
    $price    = floatval($_POST['price']);
    $location = isset($_POST['location']) ? trim($_POST['location']) : '';
    $cat_id   = intval($_POST['category_id']);
    $img_name = null;

    if(empty($title)){
        $error = "Product title is required.";
    } elseif($price <= 0){
        $error = "Please enter a valid price.";
    } elseif($cat_id == 0){
        $error = "Please select a category.";
    } else {

        if(!empty($_FILES['product_image']['name'])){
            $allowed = array('jpg','jpeg','png','gif','webp');
            $ext = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));

            if(in_array($ext, $allowed)){
                $img_name = uniqid('prod_') . '.' . $ext;
                move_uploaded_file($_FILES['product_image']['tmp_name'], "uploads/products/" . $img_name);
            } else {
                $error = "Only JPG, PNG, GIF and WEBP images are allowed.";
            }
        }

        if(empty($error)){
            $stmt = $pdo->prepare("INSERT INTO products (seller_id, category_id, title, description, price, location, product_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute(array($_SESSION['user_id'], $cat_id, $title, $desc, $price, $location, $img_name));
            $success = "Your listing has been submitted and is pending approval!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sell - ZithengeMart</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container" style="max-width:600px; padding:40px 20px;">

  <h2 style="border-left:4px solid #FF6B00; padding-left:12px; margin-bottom:25px;">Post a Listing</h2>

  <?php if($error != ''){ ?>
    <div class="alert-error"><?php echo $error; ?></div>
  <?php } ?>

  <?php if($success != ''){ ?>
    <div class="alert-success"><?php echo $success; ?></div>
  <?php } ?>

  <form method="POST" action="sell.php" enctype="multipart/form-data">

    <div class="form-group">
      <label>Product Title *</label>
      <input type="text" name="title" placeholder="e.g. iPhone 11 256GB - Good Condition" required>
    </div>

    <div class="form-group">
      <label>Category *</label>
      <select name="category_id" required>
        <option value="">-- Select a Category --</option>
        <?php
        foreach($cats as $c){
            echo '<option value="' . $c['category_id'] . '">' . htmlspecialchars($c['category_name']) . '</option>';
        }
        ?>
      </select>
    </div>

    <div class="form-group">
      <label>Price (ZAR) *</label>
      <input type="number" name="price" min="1" step="0.01" placeholder="e.g. R200" required>
    </div>

    <div class="form-group">
      <label>Location</label>
      <input type="text" name="location" placeholder="e.g. Soweto, Johannesburg">
    </div>

    <div class="form-group">
      <label>Description</label>
      <textarea name="description" rows="5" placeholder="Describe your item in detail. Including the age, reason for selling and condition of item etc."></textarea>
    </div>

    <div class="form-group">
      <label>Product Image</label>
      <input type="file" name="product_image" accept="image/*">
      <small style="color:#777; display:block; margin-top:5px; font-size:0.7rem;">Accepted formats: JPG, JPEG, GIF, WEBP, PNG</small>
    </div>

    <button type="submit" class="btn-primary">Post Listing</button>

  </form>
</div>

<footer class="footer">
  <div class="container">
    <p>&copy; <?php echo date('2026'); ?> ZithengeMart. All rights reserved.</p>
  </div>
</footer>

</body>
</html>