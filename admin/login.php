<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../config/db.php';

if(isset($_SESSION['admin_id'])){
    header("Location: index.php");
    exit;
}

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $email = trim($_POST['email']);
    $pass  = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND (role = 'admin' OR role = 'moderator')");
    $stmt->execute(array($email));
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user && password_verify($pass, $user['password'])){
        $_SESSION['admin_id']   = $user['user_id'];
        $_SESSION['admin_name'] = $user['full_name'];
        $_SESSION['admin_role'] = $user['role'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid credentials or insufficient permissions.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login - ZithengeMart</title>
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #111111;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .login-box {
      background: #1a1a1a;
      border: 1px solid #2a2a2a;
      border-radius: 14px;
      padding: 45px;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.5);
    }
    .login-logo {
      text-align: center;
      margin-bottom: 8px;
    }
    .login-logo span {
      font-size: 1.6rem;
      font-weight: 900;
      color: #FF6B00;
      letter-spacing: 2px;
      font-style: italic;
    }
    .login-subtitle {
      text-align: center;
      color: #666;
      font-size: 0.85rem;
      margin-bottom: 30px;
    }
    .form-group {
      margin-bottom: 18px;
    }
    .form-group label {
      display: block;
      color: #aaa;
      font-size: 0.85rem;
      font-weight: 600;
      margin-bottom: 7px;
    }
    .form-group input {
      width: 100%;
      padding: 12px 14px;
      background: #222;
      border: 1.5px solid #333;
      border-radius: 8px;
      color: white;
      font-size: 0.95rem;
      outline: none;
      transition: border 0.2s;
    }
    .form-group input:focus {
      border-color: #FF6B00;
    }
    .btn-login {
      width: 100%;
      padding: 13px;
      background: #FF6B00;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      transition: background 0.2s;
      margin-top: 5px;
    }
    .btn-login:hover { background: #e05e00; }
    .alert-error {
      background: rgba(255,0,0,0.1);
      color: #ff6666;
      padding: 11px 14px;
      border-radius: 8px;
      margin-bottom: 18px;
      font-size: 0.88rem;
      border-left: 4px solid #cc0000;
    }
    .back-link {
      text-align: center;
      margin-top: 20px;
    }
    .back-link a {
      color: #555;
      font-size: 0.85rem;
      text-decoration: none;
    }
    .back-link a:hover { color: #FF6B00; }
  </style>
</head>
<body>
<div class="login-box">
  <div class="login-logo"><span>ZITHENGEMART</span></div>
  <p class="login-subtitle">Admin Panel — Authorized Access Only</p>

  <?php if($error != ''){ ?>
    <div class="alert-error"><?php echo $error; ?></div>
  <?php } ?>

  <form method="POST" action="login.php">
    <div class="form-group">
      <label>Email Address</label>
      <input type="email" name="email" placeholder="admin@zithengemart.com" required>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" placeholder="Enter password" required>
    </div>
    <button type="submit" class="btn-login">Login to Admin Panel</button>
  </form>
  <div class="back-link"><a href="../index.php">&larr; Back to ZithengeMart</a></div>
</div>
</body>
</html>
		 