<div class="sidebar">
  <div class="sidebar-brand">
    <span>ZITHENGEMART</span>
    <small>Admin Panel</small>
  </div>
  <nav class="sidebar-nav">
    <a href="/admin/index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
      📊 Dashboard
    </a>
    <a href="/admin/listings.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'listings.php' ? 'active' : ''; ?>">
      📦 Listings
    </a>
    <a href="/admin/users.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
      👥 Users
    </a>
    <a href="/admin/categories.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
      🏷️ Categories
    </a>
    <div class="sidebar-divider"></div>
    <a href="/index.php" target="_blank">
      🌐 View Site
    </a>
    <a href="/admin/logout.php" class="sidebar-logout">
      🚪 Logout
    </a>
  </nav>
</div>