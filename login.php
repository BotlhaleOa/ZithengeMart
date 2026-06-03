<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config/db.php';

$error = '';

if($_SERVER['REQUEST_METHOD'] =='POST'){

    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    if(empty($email) || empty($pass)){
        $error = "Please fill in all fields.";
	
    } else {
	
	    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
		$stmt->execute(array($email));
		$user = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if($user && password_verify($pass, $user['password'])){
		    $_SESSION['user_id'] = $user['user_id'];
			$_SESSION['user_name'] = $user['full_name'];
			$_SESSION['user_role'] = $user['role'];
			header("Location: index.php");
			exit;
		} else {
            $error = "Invalid email or password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - ZithengeMart</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="form-page">
  <div class="form-boc">
    <h2>Welcome Back</h2>
	<p class="subtitle">Login to your ZithengeMart account</p>
	
	<?php if($error != ''){ ?>
	  <div class="alert-error"><?php echo $error; ?></div>
	  <?php } ?>
	  
	  <form method="POST" action="login.php">
	    <div class="form-group">
		 <label>Email Address *</label>
		 <input type="email" name="email" placeholder="e.g. botlhale@gmail.com" required>
		</div>
        <div class="form-group">
		  <label>Password *</label>
		  <input type="password" name="password" placeholder="Enter your password" required>
		 </div>
		 <button type="submit" class="btn-primary">Login</button>
		</form>

        <p class="form-link">No account yet? <a href="register.php">Register account here</a></p>
		 </div>
</div>

<footer class="footer">
  <div class="container">
    <p>&copy; <?php echo date('2026'); ?> ZithengeMart. All rights reserved.</p>
  </div>
</footer>

</body>
</html>


	
