<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

requireAuth('Buyer');

$database = Database::getInstance();
$db = $database->getConnection();
$user_ID = getUserId();

$stmt = $db->prepare("SELECT * FROM notifications WHERE userID = ? ORDER BY created_at DESC");
$stmt->execute([$user_ID]);   // pass values directly

$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Notifications</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header>
  <nav>
    <a href="index.php" class="logo">Thrift Store</a>
    <ul class="nav-links">
      <li><a href="index.php">Home</a></li>
      <li><a href="profile.php">Profile</a></li>
      <li><a href="orders.php">Orders</a></li>
      <li>
        <a href="notifications.php">
          Notifications
          <?php
            // show unread count badge
            $countStmt = $db->prepare("SELECT COUNT(*) as unreadCount FROM notifications WHERE userID=? AND isRead=0");
            $countStmt->execute([$user_ID]);
            $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);            
                if ($countResult && $countResult['unreadCount'] > 0) {
                  echo '<span class="badge">'.$countResult['unreadCount'].'</span>';
            }
          ?>
        </a>
      </li>
      <li><a href="logout.php" class="btn btn-outline">Logout</a></li>
    </ul>
  </nav>
</header>

<div class="container">
  <h1 style="margin-bottom: 1rem;">Your Notifications</h1>

  <?php if (empty($notifications)): ?>
    <div class="card">
      <p class="alert alert-error">No notifications yet.</p>
    </div>
  <?php else: ?>
    <?php foreach ($notifications as $n): ?>
      <div class="card <?= $n['isRead'] ? '' : 'status-pending' ?>">
        <div class="meta" style="font-size:0.9rem; color:#777; margin-bottom:8px;">
          <?= htmlspecialchars($n['eventType']) ?> • <?= htmlspecialchars($n['created_at']) ?>
        </div>
        <h3 class="product-title"><?= htmlspecialchars($n['title']) ?></h3>
        <p class="product-description"><?= nl2br(htmlspecialchars($n['message'])) ?></p>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

</body>
</html>