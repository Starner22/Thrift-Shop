<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/builders/ProductQueryBuilder.php';



$database = Database::getInstance();
$db = $database->getConnection();

$search = $_GET['search'] ?? '';
$category_filter = (int)($_GET['category'] ?? 0);
$price_range = $_GET['price_range'] ?? '';

$queryBuilder = new ProductQueryBuilder($db);
$products = $queryBuilder->search($search)
                         ->category($category_filter)
                         ->priceRange($price_range)
                         ->fetchAll();

$categories_stmt = $db->prepare("SELECT * FROM categories ORDER BY name ASC");
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);
?>






















<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Second-Hand Shop - Home</title>
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
                        <li><a href="notification.php">Notifications</a></li>
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
        <div class="card search-filter">
            <form action="index.php" method="GET" class="filter-row">
                <div class="form-group">
                    <input type="text" id="search" name="search" placeholder="Search by name or description..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="form-group">
                    <select id="category" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['categoryID']; ?>" <?php echo ($category_filter == $category['categoryID']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <select id="price_range" name="price_range">
                        <option value="">All Prices</option>
                        <option value="under_50" <?php echo ($price_range === 'under_50') ? 'selected' : ''; ?>>Under $50</option>
                        <option value="50_100" <?php echo ($price_range === '50_100') ? 'selected' : ''; ?>>$50 - $100</option>
                        <option value="100_500" <?php echo ($price_range === '100_500') ? 'selected' : ''; ?>>$100 - $500</option>
                        <option value="over_500" <?php echo ($price_range === 'over_500') ? 'selected' : ''; ?>>Over $500</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </form>
        </div>

        <div class="card">
            <h2>Available Products (<?php echo count($products); ?>)</h2>
            <?php if (empty($products)): ?>
                <p>No products found. Try adjusting your search criteria.</p>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <img src="<?php echo htmlspecialchars($product['image_path'] ?? 'assets/images/placeholder.png'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image" style="height: 200px; object-fit: cover;">
                            <div class="product-title"><?php echo htmlspecialchars($product['name']); ?></div>
                            <div class="product-condition" style="background-color: #667eea; color: white;"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></div>
                            <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                            <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                            <div class="product-stock" style="margin-top: auto; color: #6c757d; font-weight: bold;">
                                <?php echo ($product['quantity'] > 0) ? "{$product['quantity']} left in stock" : 'Out of stock'; ?>
                            </div>
                            <?php if (isLoggedIn() && hasRole('Buyer')): ?>
                                <div class="btn-group">
                                    <button class="btn btn-primary" onclick="addToCart(<?php echo $product['productID']; ?>, this)" <?php echo ($product['quantity'] <= 0) ? 'disabled' : ''; ?>>
                                        <?php echo ($product['quantity'] <= 0) ? 'Out of Stock' : 'Add to Cart'; ?>
                                    </button>
                                        <button class="btn btn-outline" onclick="addToWishlist(<?php echo $product['productID']; ?>, this)">❤️</button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
</body>
</html>