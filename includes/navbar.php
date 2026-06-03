<?php if(session_status() === PHP_SESSION_NONE) session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/db.php';
$cats = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- SIDE MENU OVERLAY -->
<div class="side-menu-overlay" id="sideOverlay" onclick="closeMenu()"></div>

<!-- SIDE MENU -->
<div class="side-menu" id="sideMenu">
  <div class="side-menu-header">
    <span class="menu-logo">ZITHENGEMART</span>
    <button class="side-menu-close" onclick="closeMenu()">&times;</button>
  </div>
  <?php if(isset($_SESSION['user_id'])){ ?>
  <div class="side-menu-user">
    <p>Logged in as</p>
    <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>
  </div>
  <?php } ?>
  <nav>
    <a href="index.php"><span class="menu-icon">🏠</span> Home</a>
    <a href="search.php"><span class="menu-icon">🔍</span> Browse Listings</a>
    <?php if(isset($_SESSION['user_id'])){ ?>
      <div class="side-menu-divider"></div>
      <a href="sell.php" class="menu-sell"><span class="menu-icon">➕</span> Post a Listing</a>
      <a href="profile.php"><span class="menu-icon">👤</span> My Profile</a>
      <a href="cart.php"><span class="menu-icon">🛒</span> Saved Items</a>
      <a href="messages.php"><span class="menu-icon">💬</span> Messages</a>
      <div class="side-menu-divider"></div>
      <a href="logout.php" class="menu-logout"><span class="menu-icon">🚪</span> Logout</a>
    <?php } else { ?>
      <div class="side-menu-divider"></div>
      <a href="login.php"><span class="menu-icon">🔑</span> Login</a>
      <a href="register.php"><span class="menu-icon">📝</span> Register</a>
    <?php } ?>
  </nav>
</div>

<!-- MAIN HEADER -->
<header class="main-header">
  <div class="header-container">

    <a href="index.php" class="logo">ZITHENGEMART</a>

    <form class="header-search" action="search.php" method="GET">
      <select name="category" class="search-category-select">
        <option value="">All Categories</option>
        <?php foreach($cats as $c){ ?>
          <option value="<?php echo $c['category_id']; ?>"><?php echo htmlspecialchars($c['category_name']); ?></option>
        <?php } ?>
      </select>
      <input type="text" name="q" placeholder="What are you looking for?">
      <button type="submit">&#128269;</button>
    </form>

    <div class="header-right">
      <?php if(isset($_SESSION['user_id'])){ ?>
        <a href="sell.php" class="btn-post-listing">+ Post Listing</a>
      <?php } else { ?>
        <a href="register.php" class="header-link">Sign up</a>
        <a href="login.php" class="header-link">Log in</a>
      <?php } ?>
      <button class="hamburger" onclick="openMenu()" aria-label="Menu">
        <span></span><span></span><span></span>
      </button>
    </div>

  </div>

  <div class="mobile-search-bar">
    <form action="search.php" method="GET">
      <input type="text" name="q" placeholder="Search listings...">
      <button type="submit">🔍</button>
    </form>
  </div>

  <div class="category-bar">
    <div class="category-bar-inner">
      <?php foreach($cats as $c){ ?>
        <a href="search.php?category=<?php echo $c['category_id']; ?>" class="cat-bar-link">
          <?php echo htmlspecialchars($c['category_name']); ?>
        </a>
      <?php } ?>
    </div>
  </div>
</header>

<script>
function openMenu(){
  document.getElementById('sideMenu').classList.add('active');
  document.getElementById('sideOverlay').classList.add('active');
  document.body.style.overflow = 'hidden';
}
function closeMenu(){
  document.getElementById('sideMenu').classList.remove('active');
  document.getElementById('sideOverlay').classList.remove('active');
  document.body.style.overflow = '';
}
</script>