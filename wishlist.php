<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

requireAuth('Buyer');











$database = Database::getInstance();;

$db = $database->getConnection();
$user_id = getUserId();


$query = "SELECT p.productID, p.name, p.price, p.condition, p.quantity
          FROM wishlistitem wi
          JOIN product p ON wi.productID = p.productID
          JOIN wishlist w ON wi.wishlistID = w.wishlistID
          WHERE w.buyerID = ?";
$stmt = $db->prepare($query);
$stmt->execute([$user_id]);
$wishlist_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>



























<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Wishlist - Second-Hand Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.php" class="logo">🛍️Thrift Store</a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="cart.php">Cart</a></li>
                 <li class="dropdown">
                    <a href="#" class="dropdown-toggle">Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?></a>
                    <ul class="dropdown-menu">
                        <li><a href="profile.php">My Profile</a></li>
                        <li><a href="my-orders.php">My Orders</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="card">
            <h1>❤️ Your Wishlist</h1>
        </div>

        <?php if (empty($wishlist_items)): ?>
            <div class="card">
                <p>Your wishlist is empty. <a href="index.php">Find something you love!</a></p>
            </div>
        <?php else: ?>
             <div class="product-grid">
                <?php foreach ($wishlist_items as $product): ?>
                    <div class="product-card">
                        <div class="product-image">📦</div>
                        <div class="product-title"><?php echo htmlspecialchars($product['name']); ?></div>
                        <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                        <div class="product-condition"><?php echo htmlspecialchars($product['condition']); ?></div>
                        <div class="btn-group">
                            <button class="btn btn-primary" 
                                    onclick="addToCart(<?php echo $product['productID']; ?>, this)"
                                    <?php echo ($product['quantity'] <= 0) ? 'disabled' : ''; ?>>
                                <?php echo ($product['quantity'] <= 0) ? 'Out of Stock' : 'Add to Cart'; ?>
                            </button>
                            <button class="btn btn-danger" onclick="removeFromWishlist(<?php echo $product['productID']; ?>)">Remove</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>