<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit;
}
require_once '../config/db.php';

// Ban user
if(isset($_GET['ban']) && is_numeric($_GET['ban'])){
    $pdo->prepare("UPDATE users SET is_active = 0 WHERE user_id = ?")->execute(array($_GET['ban']));
    header("Location: users.php?msg=banned");
    exit;
}

// Unban user
if(isset($_GET['unban']) && is_numeric($_GET['unban'])){
    $pdo->prepare("UPDATE users SET is_active = 1 WHERE user_id = ?")->execute(array($_GET['unban']));
    header("Location: users.php?msg=unbanned");
    exit;
}

// Change role
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_role'])){
    $uid  = intval($_POST['user_id']);
    $role = $_POST['role'];
    $allowed = array('buyer','seller','admin','moderator');
    if(in_array($role, $allowed)){
        $pdo->prepare("UPDATE users SET role = ? WHERE user_id = ?")->execute(array($role, $uid));
    }
    header("Location: users.php?msg=role_updated");
    exit;
}

$users = $pdo->query("SELECT u.*, (SELECT COUNT(*) FROM products WHERE seller_id = u.user_id) as listing_count FROM users u ORDER BY u.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Users - ZithengeMart Admin</title>
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
      <h2>Manage Users</h2>
      <p>View, ban, unban and manage user roles</p>
    </div>

    <?php if(isset($_GET['msg'])){ ?>
      <div class="alert-success">
        <?php
        $msgs = array('banned'=>'User banned.','unbanned'=>'User unbanned.','role_updated'=>'Role updated successfully!');
        echo isset($msgs[$_GET['msg']]) ? $msgs[$_GET['msg']] : '';
        ?>
      </div>
    <?php } ?>

    <div class="content-card">
      <div class="card-header">
        <h3>All Users (<?php echo count($users); ?>)</h3>
      </div>
      <table class="admin-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Role</th>
            <th>Listings</th>
            <th>Status</th>
            <th>Joined</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($users as $u){ ?>
          <tr>
            <td><strong><?php echo htmlspecialchars($u['full_name']); ?></strong></td>
            <td><?php echo htmlspecialchars($u['email']); ?></td>
            <td><?php echo htmlspecialchars($u['phone'] ?? '-'); ?></td>
            <td>
              <!-- Change role form -->
              <form method="POST" action="users.php" style="display:flex; gap:5px; align-items:center;">
                <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">
                <select name="role" class="admin-select" style="padding:4px 8px; font-size:0.8rem;">
                  <?php foreach(array('buyer','seller','moderator','admin') as $r){ ?>
                    <option value="<?php echo $r; ?>" <?php echo $u['role']==$r?'selected':''; ?>>
                      <?php echo ucfirst($r); ?>
                    </option>
                  <?php } ?>
                </select>
                <button type="submit" name="change_role" class="btn-sm" style="padding:4px 10px; font-size:0.78rem;">Save</button>
              </form>
            </td>
            <td><?php echo $u['listing_count']; ?></td>
            <td>
              <span class="status-badge <?php echo $u['is_active'] ? 'status-active' : 'status-removed'; ?>">
                <?php echo $u['is_active'] ? 'Active' : 'Banned'; ?>
              </span>
            </td>
            <td><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
            <td>
              <?php if($u['is_active']){ ?>
                <a href="users.php?ban=<?php echo $u['user_id']; ?>"
                   class="btn-danger"
                   onclick="return confirm('Ban this user?')">Ban</a>
              <?php } else { ?>
                <a href="users.php?unban=<?php echo $u['user_id']; ?>" class="btn-sm">Unban</a>
              <?php } ?>
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