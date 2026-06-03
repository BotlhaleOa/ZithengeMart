<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config/db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Send message
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])){
    $receiver_id  = intval($_POST['receiver_id']);
    $product_id   = intval($_POST['product_id']);
    $message_text = trim($_POST['message_text']);

    if(!empty($message_text)){
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, product_id, message_text) VALUES (?, ?, ?, ?)");
        $stmt->execute(array($user_id, $receiver_id, $product_id, $message_text));
        header("Location: messages.php?conversation=" . $receiver_id . "&product=" . $product_id . "&sent=1");
        exit;
    }
}

// Mark messages as read
if(isset($_GET['conversation']) && isset($_GET['product'])){
    $other_id   = intval($_GET['conversation']);
    $product_id = intval($_GET['product']);
    $pdo->prepare("UPDATE messages SET is_read = 1 WHERE receiver_id = ? AND sender_id = ? AND product_id = ?")->execute(array($user_id, $other_id, $product_id));
}

// Fetch all conversations
$conversations = $pdo->prepare("
    SELECT DISTINCT
        CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END as other_user_id,
        m.product_id,
        p.title as product_title,
        p.product_image,
        p.price,
        u.full_name as other_user_name,
        (SELECT message_text FROM messages m2 WHERE (m2.sender_id = ? AND m2.receiver_id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END) OR (m2.sender_id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END AND m2.receiver_id = ?) ORDER BY m2.sent_at DESC LIMIT 1) as last_message,
        (SELECT m3.sent_at FROM messages m3 WHERE (m3.sender_id = ? AND m3.receiver_id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END) OR (m3.sender_id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END AND m3.receiver_id = ?) ORDER BY m3.sent_at DESC LIMIT 1) as last_time,
        (SELECT COUNT(*) FROM messages m4 WHERE m4.sender_id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END AND m4.receiver_id = ? AND m4.is_read = 0 AND m4.product_id = m.product_id) as unread_count
    FROM messages m
    JOIN users u ON u.user_id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END
    JOIN products p ON p.product_id = m.product_id
    WHERE m.sender_id = ? OR m.receiver_id = ?
    GROUP BY other_user_id, m.product_id
    ORDER BY last_time DESC
");
$conversations->execute(array(
    $user_id,
    $user_id, $user_id, $user_id, $user_id,
    $user_id, $user_id, $user_id, $user_id,
    $user_id, $user_id,
    $user_id,
    $user_id, $user_id
));
$convos = $conversations->fetchAll(PDO::FETCH_ASSOC);

// Fetch messages for active conversation
$active_messages = array();
$active_user     = null;
$active_product  = null;

if(isset($_GET['conversation']) && isset($_GET['product'])){
    $other_id   = intval($_GET['conversation']);
    $product_id = intval($_GET['product']);

    $active_messages_stmt = $pdo->prepare("
        SELECT m.*, u.full_name as sender_name
        FROM messages m
        JOIN users u ON m.sender_id = u.user_id
        WHERE ((m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?))
        AND m.product_id = ?
        ORDER BY m.sent_at ASC
    ");
    $active_messages_stmt->execute(array($user_id, $other_id, $other_id, $user_id, $product_id));
    $active_messages = $active_messages_stmt->fetchAll(PDO::FETCH_ASSOC);

    $active_user_stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $active_user_stmt->execute(array($other_id));
    $active_user = $active_user_stmt->fetch(PDO::FETCH_ASSOC);

    $active_product_stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
    $active_product_stmt->execute(array($product_id));
    $active_product = $active_product_stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Messages - ZithengeMart</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .messages-page {
      padding: 40px 0 60px;
      background: #fafafa;
      min-height: 80vh;
    }
    .messages-layout {
      display: grid;
      grid-template-columns: 320px 1fr;
      gap: 20px;
      align-items: start;
      height: 75vh;
    }
    .conversations-panel {
      background: white;
      border: 1px solid #e8e8e8;
      border-radius: 14px;
      overflow: hidden;
      height: 100%;
      display: flex;
      flex-direction: column;
    }
    .conversations-header {
      padding: 18px 20px;
      border-bottom: 2px solid #f0f0f0;
      font-weight: 800;
      font-size: 1rem;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .conversations-header::before {
      content: '';
      display: block;
      width: 4px;
      height: 18px;
      background: #FF6B00;
      border-radius: 2px;
    }
    .conversations-list {
      flex: 1;
      overflow-y: auto;
    }
    .convo-item {
      display: flex;
      gap: 12px;
      padding: 14px 18px;
      border-bottom: 1px solid #f5f5f5;
      cursor: pointer;
      transition: background 0.2s;
      text-decoration: none !important;
      color: inherit;
    }
    .convo-item:hover { background: #fff5ee; }
    .convo-item.active {
      background: #fff5ee;
      border-left: 3px solid #FF6B00;
    }
    .convo-avatar {
      width: 44px;
      height: 44px;
      background: #FF6B00;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 800;
      font-size: 1rem;
      flex-shrink: 0;
    }
    .convo-info { flex: 1; overflow: hidden; }
    .convo-name { font-weight: 700; font-size: 0.9rem; margin-bottom: 2px; }
    .convo-product {
      font-size: 0.78rem;
      color: #FF6B00;
      margin-bottom: 3px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .convo-last {
      font-size: 0.78rem;
      color: #aaa;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .convo-meta {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      gap: 5px;
      flex-shrink: 0;
    }
    .convo-time { font-size: 0.72rem; color: #bbb; }
    .unread-badge {
      background: #FF6B00;
      color: white;
      font-size: 0.7rem;
      font-weight: 700;
      padding: 2px 7px;
      border-radius: 20px;
    }
    .no-convos {
      padding: 40px 20px;
      text-align: center;
      color: #aaa;
    }
    .no-convos a {
      color: #FF6B00;
      font-weight: 700;
      text-decoration: none !important;
    }
    .chat-panel {
      background: white;
      border: 1px solid #e8e8e8;
      border-radius: 14px;
      overflow: hidden;
      height: 100%;
      display: flex;
      flex-direction: column;
    }
    .chat-header {
      padding: 15px 20px;
      border-bottom: 2px solid #f0f0f0;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .chat-header-avatar {
      width: 40px;
      height: 40px;
      background: #FF6B00;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 800;
      flex-shrink: 0;
    }
    .chat-header-info h4 { font-size: 0.95rem; font-weight: 700; }
    .chat-header-info p { font-size: 0.78rem; color: #FF6B00; }
    .chat-product-bar {
      background: #fff5ee;
      border-bottom: 1px solid #ffe0cc;
      padding: 10px 20px;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .chat-product-img {
      width: 40px;
      height: 40px;
      border-radius: 6px;
      object-fit: cover;
      background: #f0f0f0;
    }
    .chat-product-info { flex: 1; }
    .chat-product-info p { font-size: 0.82rem; font-weight: 700; color: #111; }
    .chat-product-info span { font-size: 0.78rem; color: #FF6B00; font-weight: 700; }
    .chat-messages {
      flex: 1;
      overflow-y: auto;
      padding: 20px;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .message-bubble {
      max-width: 65%;
      padding: 10px 14px;
      border-radius: 12px;
      font-size: 0.88rem;
      line-height: 1.5;
    }
    .message-bubble.sent {
      background: #FF6B00;
      color: white;
      align-self: flex-end;
      border-bottom-right-radius: 3px;
    }
    .message-bubble.received {
      background: #f0f0f0;
      color: #111;
      align-self: flex-start;
      border-bottom-left-radius: 3px;
    }
    .message-time {
      font-size: 0.7rem;
      opacity: 0.7;
      margin-top: 4px;
      text-align: right;
    }
    .message-bubble.received .message-time { text-align: left; }
    .chat-input-area {
      padding: 15px 20px;
      border-top: 2px solid #f0f0f0;
      display: flex;
      gap: 10px;
    }
    .chat-input-area input {
      flex: 1;
      padding: 11px 15px;
      border: 1.5px solid #ddd;
      border-radius: 25px;
      font-size: 0.9rem;
      outline: none;
      font-family: inherit;
      transition: border 0.2s;
    }
    .chat-input-area input:focus { border-color: #FF6B00; }
    .chat-send-btn {
      background: #FF6B00;
      color: white;
      border: none;
      padding: 11px 22px;
      border-radius: 25px;
      font-weight: 700;
      cursor: pointer;
      font-size: 0.9rem;
      transition: background 0.2s;
      font-family: inherit;
    }
    .chat-send-btn:hover { background: #e05e00; }
    .empty-chat {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      color: #aaa;
      gap: 10px;
    }
    .empty-chat .chat-icon { font-size: 3rem; }
    @media(max-width: 768px){
      .messages-layout { grid-template-columns: 1fr; height: auto; }
      .conversations-panel { height: 300px; }
      .chat-panel { height: 500px; }
    }
  </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="messages-page">
  <div class="container">
    <h2 class="section-title" style="margin-bottom:20px;">Messages</h2>

    <?php if(isset($_GET['sent'])){ ?>
      <div class="alert-success" style="margin-bottom:15px;">Message sent!</div>
    <?php } ?>

    <div class="messages-layout">

      <div class="conversations-panel">
        <div class="conversations-header">Conversations (<?php echo count($convos); ?>)</div>
        <div class="conversations-list">
          <?php if(count($convos) > 0){ ?>
            <?php foreach($convos as $c){ ?>
            <a href="messages.php?conversation=<?php echo $c['other_user_id']; ?>&product=<?php echo $c['product_id']; ?>"
               class="convo-item <?php echo (isset($_GET['conversation']) && $_GET['conversation'] == $c['other_user_id'] && isset($_GET['product']) && $_GET['product'] == $c['product_id']) ? 'active' : ''; ?>">
              <div class="convo-avatar">
                <?php echo strtoupper(substr($c['other_user_name'], 0, 1)); ?>
              </div>
              <div class="convo-info">
                <div class="convo-name"><?php echo htmlspecialchars($c['other_user_name']); ?></div>
                <div class="convo-product"><?php echo htmlspecialchars($c['product_title']); ?></div>
                <div class="convo-last"><?php echo htmlspecialchars($c['last_message'] ?? ''); ?></div>
              </div>
              <div class="convo-meta">
                <span class="convo-time"><?php echo $c['last_time'] ? date('d M', strtotime($c['last_time'])) : ''; ?></span>
                <?php if($c['unread_count'] > 0){ ?>
                  <span class="unread-badge"><?php echo $c['unread_count']; ?></span>
                <?php } ?>
              </div>
            </a>
            <?php } ?>
          <?php } else { ?>
            <div class="no-convos">
              <p>No messages yet.</p>
              <br>
              <a href="index.php">Browse listings to contact sellers</a>
            </div>
          <?php } ?>
        </div>
      </div>

      <div class="chat-panel">
        <?php if($active_user && $active_product){ ?>
          <div class="chat-header">
            <div class="chat-header-avatar">
              <?php echo strtoupper(substr($active_user['full_name'], 0, 1)); ?>
            </div>
            <div class="chat-header-info">
              <h4><?php echo htmlspecialchars($active_user['full_name']); ?></h4>
              <p><?php echo htmlspecialchars($active_user['email']); ?></p>
            </div>
          </div>

          <div class="chat-product-bar">
            <?php if($active_product['product_image']){ ?>
              <img src="uploads/products/<?php echo htmlspecialchars($active_product['product_image']); ?>"
                   class="chat-product-img" alt="">
            <?php } ?>
            <div class="chat-product-info">
              <p><?php echo htmlspecialchars($active_product['title']); ?></p>
              <span>R<?php echo number_format($active_product['price'], 2); ?></span>
            </div>
            <a href="product.php?id=<?php echo $active_product['product_id']; ?>"
               style="font-size:0.78rem; color:#FF6B00; font-weight:600;">View Listing</a>
          </div>

          <div class="chat-messages" id="chatMessages">
            <?php foreach($active_messages as $msg){ ?>
              <div class="message-bubble <?php echo $msg['sender_id'] == $user_id ? 'sent' : 'received'; ?>">
                <?php echo nl2br(htmlspecialchars($msg['message_text'])); ?>
                <div class="message-time">
                  <?php echo date('d M, H:i', strtotime($msg['sent_at'])); ?>
                </div>
              </div>
            <?php } ?>
          </div>

          <div class="chat-input-area">
            <form method="POST" action="messages.php" style="display:flex; gap:10px; width:100%;">
              <input type="hidden" name="receiver_id" value="<?php echo $active_user['user_id']; ?>">
              <input type="hidden" name="product_id" value="<?php echo $active_product['product_id']; ?>">
              <input type="text" name="message_text" placeholder="Type a message..." required autocomplete="off">
              <button type="submit" name="send_message" class="chat-send-btn">Send</button>
            </form>
          </div>

        <?php } else { ?>
          <div class="empty-chat">
            <div class="chat-icon">💬</div>
            <p>Select a conversation to start chatting</p>
          </div>
        <?php } ?>
      </div>

    </div>
  </div>
</div>

<footer class="footer">
  <div class="container">
    <p>&copy; <?php echo date('2026'); ?> <span>ZithengeMart</span>. All rights reserved.</p>
  </div>
</footer>

<script>
var chatMessages = document.getElementById('chatMessages');
if(chatMessages){
    chatMessages.scrollTop = chatMessages.scrollHeight;
}
</script>

</body>
</html>