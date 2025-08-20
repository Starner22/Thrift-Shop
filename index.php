<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

$database = Database::getInstance();
$db = $database->getConnection();


$new_sql = $db->prepare("SELECT p.productID AS ID, p.name AS name, p.price AS price, p.image_path AS image, p.quantity AS quantity, c.name AS category_name
            FROM Product p
            LEFT JOIN Categories c ON p.categoryID = c.categoryID
            WHERE p.status = 'approved'
            ORDER BY p.created_at DESC
            LIMIT 12");
$new_sql->execute();
$new_arrivals = $new_sql->fetchAll(PDO::FETCH_ASSOC);

$all_category_sql = $db->prepare("SELECT categoryID, Name , image_path FROM Categories");
$all_category_sql->execute();
$all_category = $all_category_sql->fetchAll(PDO::FETCH_ASSOC);
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thrift Store - Home</title>
    <link rel="stylesheet" href="Styles/product_browse_style.css">
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

    <div class="container search-bar-container">
            <form method="GET" action="customer_browse_all.php" class="search-form">
                <input type="text" name="search" placeholder="Search products..." class="search-input">
                <button type="submit" class="search-button">
                    Search
                </button>
            </form>
    </div>

    <div class="main-content container">
        <section class="content-area">
            <h2 class="section-heading">Categories</h2>
            <div class="category-grid">
                <a href="customer_browse_all.php" class="category-box">
                    <span>All</span>
                </a>
                <?php
                if (!empty($all_category)) {    
                    foreach ($all_category as $category) {
                        echo "<a href='customer_browse_categorized.php?category=" . $category['categoryID'] . "' class='category-box'>";
                        echo "<img src='{$category['image_path']}' alt='" . ($category['Name']) . "' class='product-img'>";
                        echo "<span>" . ($category['Name']) . "</span>";
                        echo "</a>";
                    } 
                } else {
                    echo "<p>No categories found.</p>";
                }
                ?>
            </div>   

            <h2 class="section-heading">New Arrivals</h2>
            <div class="product-grid">
                <?php
                if (!empty($new_arrivals)) {
                    foreach ($new_arrivals as $product) {
                        echo "<div class='card'>";
                        echo "<img src='{$product['image']}' alt='" . ($product['name']) . "' class='product-img'>";
                        echo "<div class='card-content'>";
                        echo "<h3>" . ($product['name']) . "</h3>";
                        echo "<p class='product-category'>" . ($product['category_name'] ?? 'Uncategorized') . "</p>";
                        echo "<p>$" . number_format($product['price'], 2) . "</p>";
                        echo "<p> Quantity: " . ($product['quantity']) . "</p>";
                        echo "<a href='Customer_Product_Details.php?id={$product['ID']}' class='btn-primary'>View Details</a>";
                        echo "</div></div>";
                    }
                } else {
                    echo "<p>No new products found.</p>";
                }
                ?>
            </div>

        </section>
    </div>
    
    <script src="assets/js/main.js"></script>
</body>
</html>