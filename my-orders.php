<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

requireAuth('Buyer');














$database = Database::getInstance();
$db = $database->getConnection();
$user_id = getUserId();


$query = "SELECT orderID, totalPrice, orderStatus, orderDate 
          FROM `order` 
          WHERE buyerID = ? 
          ORDER BY orderDate DESC";
$stmt = $db->prepare($query);
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>




















<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Thrift Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.php" class="logo">🛍️ Thrift Store</a>
            <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <?php if (isLoggedIn()): ?>
        <li><a href="dashboard.php">Dashboard</a></li>
        <?php if (hasRole('Buyer')): ?>
            <li><a href="cart.php">Cart <span id="cart-count" class="badge"></span></a></li>
            <li><a href="wishlist.php">Wishlist</a></li>
        <?php endif; ?>
        <?php if (hasRole('Admin')): ?>
    <li><a href="admin/products.php">Products</a></li>
    <li><a href="admin/orders.php">Orders</a></li>
    <li><a href="admin/tickets.php">Tickets</a></li>
        <li><a href="admin/users.php">Users</a></li>
    <?php endif; ?>
        <li class="dropdown">
            <a href="#" class="dropdown-toggle">Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?></a>
            <ul class="dropdown-menu">
                <li><a href="profile.php">My Profile</a></li>
                <?php if (hasRole('Buyer')): ?>
                    <li><a href="my-orders.php">My Orders</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </li>
    <?php else: ?>
        <li><a href="login.php">Login</a></li>
        <li><a href="register.php">Register</a></li>
        <li><a href="support.php">Support</a></li>
    <?php endif; ?>
</ul>
        </nav>
    </header>

    <div class="container">
        <div class="card">
            <h1>📄 My Order History</h1>
        </div>

        <?php if (empty($orders)): ?>
            <div class="card">
                <p>You have not placed any orders yet. <a href="index.php">Start shopping!</a></p>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="card">
                    <div style="display: flex; justify-content: space-between; align-items: baseline;">
                        <h3>Order #<?php echo $order['orderID']; ?></h3>
                        <span class="status status-<?php echo strtolower($order['orderStatus']); ?>"><?php echo $order['orderStatus']; ?></span>
                    </div>
                    <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($order['orderDate'])); ?></p>
                    <p><strong>Total:</strong> $<?php echo number_format($order['totalPrice'], 2); ?></p>
                    
                    <h4 style="margin-top: 1.5rem;">Items in this order:</h4>
                    <?php
                    
                    $item_query = "SELECT p.name, oi.quantity, oi.price_at_purchase 
                                   FROM orderitem oi 
                                   JOIN product p ON oi.productID = p.productID 
                                   WHERE oi.orderID = ?";
                    $item_stmt = $db->prepare($item_query);
                    $item_stmt->execute([$order['orderID']]);
                    $items = $item_stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <table class="table" style="margin-top: 1rem;">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>$<?php echo number_format($item['price_at_purchase'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>