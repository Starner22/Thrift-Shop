<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

requireAuth('Buyer');












$database = Database::getInstance();
$db = $database->getConnection();
$user_id = getUserId();

$query = "SELECT ci.cartItemID, p.productID, p.name, p.price, ci.quantity, p.condition
          FROM cartitem ci
          JOIN product p ON ci.productID = p.productID
          JOIN cart c ON ci.cartID = c.cartID
          WHERE c.buyerID = ?";
$stmt = $db->prepare($query);
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
?>














<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - Thrift Store</title>
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
            <h1>🛒 Your Shopping Cart</h1>
        </div>
        
        <?php if (empty($cart_items)): ?>
            <div class="card">
                <p>Your cart is empty. <a href="index.php">Continue shopping!</a></p>
            </div>
        <?php else: ?>
            <div class="card">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th style="width: 120px;">Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($item['name']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($item['condition']); ?></small>
                                </td>
                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                <td>
                                    <input type="number" value="<?php echo $item['quantity']; ?>" min="1" 
                                           onchange="updateQuantity(<?php echo $item['cartItemID']; ?>, this.value)"
                                           style="width: 80px; text-align: center;">
                                </td>
                                <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                <td>
                                    <button class="btn btn-danger" onclick="removeFromCart(<?php echo $item['cartItemID']; ?>)">Remove</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card" style="text-align: right;">
                <h2>Cart Summary</h2>
                <h3>Subtotal: $<?php echo number_format($subtotal, 2); ?></h3>
                <p>Shipping & taxes calculated at checkout.</p>
                <button id="checkout-btn" class="btn btn-primary" onclick="checkout()">Proceed to Checkout</button>
            </div>
        <?php endif; ?>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>