<?php

require_once 'config/database.php';
require_once 'includes/auth.php';

$database = Database::getInstance();
$db = $database->getConnection();

if (isset($_GET['id'])){
  $productid = intval($_GET['id']);
} else {
  $productid = 0;
}

$details_sql = $db->prepare("SELECT p.productID AS ID, p.name AS name, p.price AS price, p.image_path AS image, p.description AS description, c.name AS category_name, p.quantity AS quantity
                FROM Product p
                LEFT JOIN Categories c ON p.categoryID = c.categoryID
                WHERE ProductID = $productid");
$details_sql->execute();
$product = $details_sql->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Thrift Store - <?php echo ($product['name']); ?></title>
        <link rel='stylesheet' href='Styles/customer_product_details_styles.css'/>
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
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle">Hello, <?php echo ($_SESSION['user_name']); ?></a>
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

        <div class='container search-bar-container'>
            <form method='GET' action='customer_browse_all.php' class='search-form'>
                <input type='text' name='search' placeholder='Search products...' class='search-input'>
                <button type='submit' class='search-button'>Search</button>
            </form>
        </div>

        <div class='container product-details-container'>
            <div class='product-image-section'>
                <?php
                echo "<img src='{$product['image']}' alt='{$product['name']}'>";
                ?>
            </div>

            <div class='product-info-section'>
                <h2 class='product-name'><?php echo ($product['name']); ?></h2>
                <p class='product-price'> $ <?php echo number_format($product['price'], 2); ?></p>

                <div class='description-box'>
                    <h4>Description</h4>
                    <p><?= ($product['description']); ?></p>
                </div>

                <?php if (isLoggedIn() && hasRole('Buyer')): ?>
                    <div class="btn-group">
                        <button class="btn btn-primary" onclick="addToCart(<?php echo $product['ID']; ?>, this)" <?php echo ($product['quantity'] <= 0) ? 'disabled' : ''; ?>>
                            <?php echo ($product['quantity'] <= 0) ? 'Out of Stock' : 'Add to Cart'; ?>
                        </button>
                        <button class="btn btn-outline" onclick="addToWishlist(<?php echo $product['ID']; ?>, this)">❤️</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <script src="assets/js/main.js"></script>
    </body>
</html>