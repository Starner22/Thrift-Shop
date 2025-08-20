<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

requireAuth();

$database = Database::getInstance();
$db = $database->getConnection();
$user_id = getUserId();
$user_role = getUserRole();

$stats = [];
if ($user_role === 'Buyer') {
    // Order stats (order is reserved keyword → backticks)
    $orders_query = "SELECT COUNT(*) as total_orders, COALESCE(SUM(totalPrice), 0) as total_spent 
                     FROM `order` 
                     WHERE buyerID = ?";
    $orders_stmt = $db->prepare($orders_query);
    $orders_stmt->execute([$user_id]);
    $order_stats = $orders_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Cart stats
    $cart_query = "SELECT COUNT(ci.cartItemID) as cart_items 
                   FROM cart c 
                   JOIN cartitem ci ON c.cartID = ci.cartID 
                   WHERE c.buyerID = ?";
    $cart_stmt = $db->prepare($cart_query);
    $cart_stmt->execute([$user_id]);
    $cart_stats = $cart_stmt->fetch(PDO::FETCH_ASSOC);
    
    $stats = [
        'total_orders' => $order_stats['total_orders'],
        'total_spent' => $order_stats['total_spent'],
        'cart_items'  => $cart_stats['cart_items']
    ];
} elseif ($user_role === 'Seller') {
    // Product stats
    $products_query = "SELECT 
                           COUNT(*) as total_products, 
                           COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_products, 
                           COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_products 
                       FROM product 
                       WHERE sellerID = ?";
    $products_stmt = $db->prepare($products_query);
    $products_stmt->execute([$user_id]);
    $stats = $products_stmt->fetch(PDO::FETCH_ASSOC);
} elseif (in_array($user_role, ['Admin', 'Moderator'])) {
    // Admin/Moderator stats
    $admin_query = "SELECT 
                       (SELECT COUNT(*) FROM `user`) as total_users, 
                       (SELECT COUNT(*) FROM product WHERE status = 'pending') as pending_products, 
                       (SELECT COUNT(*) FROM `order`) as total_orders, 
                       (SELECT COUNT(*) FROM supportticket WHERE status = 'Open') as open_tickets";
    $admin_stmt = $db->prepare($admin_query);
    $admin_stmt->execute();
    $stats = $admin_stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Thrift Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.php" class="logo">🛍️Thrift Store</a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>

                    <?php if (hasRole('Admin')): ?>
                        <li><a href="admin/products.php">Products</a></li>
                        <li><a href="admin/orders.php">Orders</a></li>
                        <li><a href="admin/tickets.php">Tickets</a></li>
                        <li><a href="admin/users.php">Users</a></li>
                    <?php elseif (hasRole('Moderator')): ?>
                        <li><a href="admin/products.php">Products</a></li>
                        <li><a href="admin/tickets.php">Tickets</a></li>
                    <?php elseif (hasRole('Buyer')): ?>
                        <li><a href="cart.php">Cart <span id="cart-count" class="badge"></span></a></li>
                        <li><a href="wishlist.php">Wishlist</a></li>
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
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
            <p>Your role is: <strong><?php echo $user_role; ?></strong></p>
        </div>

        <?php if ($user_role === 'Buyer'): ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_orders']; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">$<?php echo number_format($stats['total_spent'], 2); ?></div>
                    <div class="stat-label">Total Spent</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['cart_items']; ?></div>
                    <div class="stat-label">Items in Cart</div>
                </div>
            </div>
            <div class="card">
                <h2>Quick Actions</h2>
                <div class="btn-group">
                    <a href="cart.php" class="btn btn-primary">View Cart</a>
                    <a href="wishlist.php" class="btn btn-outline">View Wishlist</a>
                    <a href="my-orders.php" class="btn btn-secondary">My Orders</a>
                </div>
            </div>
        <?php elseif ($user_role === 'Seller'): ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_products']; ?></div>
                    <div class="stat-label">Total Products</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['approved_products']; ?></div>
                    <div class="stat-label">Approved</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['pending_products']; ?></div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>
            <div class="card">
                <h2>Quick Actions</h2>
                <div class="btn-group">
                    <a href="add-product.php" class="btn btn-primary">Add New Product</a>
                    <a href="my-products.php" class="btn btn-outline">Manage My Products</a>
                </div>
            </div>

        <?php elseif ($user_role === 'Admin'): ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_users']; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['pending_products']; ?></div>
                    <div class="stat-label">Products to Review</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_orders']; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['open_tickets']; ?></div>
                    <div class="stat-label">Open Support Tickets</div>
                </div>
            </div>
            <div class="card">
                <h2>Quick Actions</h2>
                <div class="btn-group">
                    <a href="admin/products.php" class="btn btn-primary">Review Products</a>
                    <a href="admin/orders.php" class="btn btn-outline">Manage Orders</a>
                    <a href="admin/tickets.php" class="btn btn-secondary">Support Tickets</a>
                    <a href="admin/users.php" class="btn btn-warning">Manage Users</a>
                </div>
            </div>

        <?php elseif ($user_role === 'Moderator'): ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['pending_products']; ?></div>
                    <div class="stat-label">Products to Review</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_orders']; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['open_tickets']; ?></div>
                    <div class="stat-label">Open Support Tickets</div>
                </div>
            </div>
            <div class="card">
                <h2>Quick Actions</h2>
                <div class="btn-group">
                    <a href="admin/products.php" class="btn btn-primary">Review Products</a>
                    <a href="admin/tickets.php" class="btn btn-secondary">Support Tickets</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
