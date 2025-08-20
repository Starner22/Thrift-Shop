<?php

require_once 'config/database.php';
require_once 'includes/auth.php';

$database = new Database();
$db = $database->getConnection();

if (isset($_GET['id'])){
  $product_id = intval($_GET['id']);
} else {
  $product_id = 0;
}

$details_sql = $db->prepare("SELECT p.productID AS ID, p.name AS name, p.price AS price, p.image_path AS image, p.condition, p.CategoryID, c.name AS category_name
                FROM Product p
                LEFT JOIN Categories c ON p.categoryID = c.categoryID
                WHERE Product_ID = $product_id AND Is_Product_Shown = 'Listed'");
$details_sql->execute();
$result = $details_sql->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Second-Hand Shop - <?php echo ($product['Name']); ?></title>
        <link rel='stylesheet' href='Styles/customer_product_details_styles.css'/>
    </head>
    <body>
        <header>
            <nav>
                <a href="index.php" class="logo">🛍️ SecondHand Shop</a>
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
                echo "<img src='{$product['Image']}' alt='{$product['Name']}'>";
                ?>
            </div>

            <div class='product-info-section'>
                <h2 class='product-name'><?php echo ($product['Name']); ?></h2>
                <p class='product-price'> $ <?php echo number_format($product['Price'], 2); ?></p>

                <div class='description-box'>
                    <h4>Description</h4>
                    <p><?= ($product['Description']); ?></p>
                </div>

                <div class='product-actions'>
                    <form action='http://localhost/Thrift/Frontend_html/Cart.php' method='POST' class='add-to-cart-form'>
                        <input type='hidden' name='product_id' value='<?= $product['ID']; ?>'>
                        <button type='submit' class='btn-purchase'>Add to Cart</button>
                    </form>

                    <a href='http://localhost/Thrift/Frontend_html/Wishlist.php?add=<?= $product['ID']; ?>' class='btn-wishlist'>
                        Add to Wishlist
                        <span class='wishlist-notification'>&hearts;</span>
                    </a>
                </div>
            </div>
        </div>
    </body>
</html>

<?php $conn->close(); ?>
