<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config/db.php';

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $name  = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $pass  = $_POST['password'];
    $pass2 = $_POST['confirm_password'];

    if(empty($name) || empty($email) || empty($pass)){
    $error = "Please fill in all required fields.";
} elseif($pass !== $pass2){
    $error = "Passwords do not match.";
} elseif(strlen($pass) < 6){
    $error = "Password must be at least 6 characters.";
} elseif(!empty($phone) && !preg_match('/^0[0-9]{9}$/', $phone)){
    $error = "Please enter a valid South African phone number starting with 0 and 10 digits long.";
} else {

        $check = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->execute(array($email));

        if($check->rowCount() > 0){
            $error = "An account with this email already exists.";

        } else {
            $hashed = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password) VALUES (?, ?, ?, ?)");
            $stmt->execute(array($name, $email, $phone, $hashed));
            $success = "Account created successfully!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - ZithengeMart</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="form-page">
  <div class="form-box">
    <h2>Create Account</h2>
    <p class="subtitle">Join thousands of buyers and sellers on ZithengeMart</p>

    <?php if($error != ''){ ?>
      <div class="alert-error"><?php echo $error; ?></div>
    <?php } ?>

    <?php if($success != ''){ ?>
      <div class="alert-success">
        <?php echo $success; ?> <a href="login.php" style="color:#006600; font-weight:700;">Login here</a>
      </div>
    <?php } ?>

    <form method="POST" action="register.php">
      <div class="form-group">
        <label>Full Name *</label>
        <input type="text" name="full_name" placeholder="e.g. Botlhale Nthite" required>
      </div>
      <div class="form-group">
        <label>Email Address *</label>
        <input type="email" name="email" placeholder="e.g. botlhale@gmail.com" required>
      </div>
      <div class="form-group">
        <label>Phone Number</label>
        <input type="text" name="phone" placeholder="e.g. 073 115 4222" maxlength="10">
		<small style="color:#aaa; font-size:0.75rem; display:block; margin-top:4px;">South African number starting with 0, 10 digits</small>
      </div>
      <div class="form-group">
        <label>Password *</label>
        <input type="password" name="password" placeholder="Minimum of 6 characters" required>
      </div>
      <div class="form-group">
        <label>Confirm Password *</label>
        <input type="password" name="confirm_password" placeholder="Re-enter your password" required>
      </div>
      <button type="submit" class="btn-primary">Create Account</button>
    </form>

    <p class="form-link">Already have an account? <a href="login.php">Login</a></p>
  </div>
</div>

<footer class="footer">
  <div class="container">
    <p>&copy; <?php echo date('2026'); ?> ZithengeMart. All rights reserved.</p>
  </div>
</footer>

</body>
</html>