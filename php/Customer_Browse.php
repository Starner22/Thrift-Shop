<?php
$conn = new mysqli("localhost", "root", "", "thrift_shop");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$all_category = $conn->query("SELECT Category_ID, Name, Image FROM Category");

$new_sql = "SELECT p.Product_ID AS ID, p.Product_name AS name, p.Selling_Price AS price, p.Image AS image
            FROM Product p
            ORDER BY p.Created_at DESC
            LIMIT 12";
$new_arrivals = $conn->query($new_sql);

$popular_sql = "SELECT p.Product_ID AS ID, p.Product_name AS name, p.Selling_Price AS price, p.Image AS image
                FROM Product p
                ORDER BY p.Selling_Price DESC
                LIMIT 12";
$popular_products = $conn->query($popular_sql);
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Browse Items – Thrift Store</title>
        <link rel="stylesheet" href="http://localhost/Thrift/Styles/product_browse_style.css"/>
    </head>
    <body>
        <header>
            <div class="container">
                <h1><a href="http://localhost/Thrift/Frontend_html/index.php">Thrift Store</a></h1>
                <nav>
                    <a href="http://localhost/Thrift/Frontend_html/Cart.php">Cart</a>
                    <a href="http://localhost/Thrift/Frontend_html/Wishlist.php">Wishlist</a>
                    <a href="http://localhost/Thrift/Frontend_html/Profile.php">Profile</a>
                </nav>
            </div>
        </header>

        <div class="container search-bar-container">
            <form method="GET" action="http://localhost/Thrift/Frontend_html/customer_browse_all.php" class="search-form">
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

                    <a href="http://localhost/Thrift/Frontend_html/customer_browse_all.php" class="category-box">
                        <span>All</span>
                    </a>
                    <?php
                    if ($all_category->num_rows > 0) {    
                        while ($category = $all_category->fetch_assoc()) {
                            echo "<a href='http://localhost/Thrift/Frontend_html/customer_browse_categorized.php?category=" . $category['Category_ID'] . "' class='category-box'>";
                            echo "<span>" . $category['Name'] . "</span>";
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
                    if ($new_arrivals->num_rows > 0) {
                        while ($product = $new_arrivals->fetch_assoc()) {
                            echo "<div class='card'>";
                            // echo "<img src='{$product['image']}' alt='{$product['name']}'>";
                            echo "<div class='card-content'>";
                            echo "<h3>{$product['name']}</h3>";
                            echo "<p>$" . number_format($product['price'], 2) . "</p>";
                            echo "<a href='http://localhost/Thrift/Frontend_html/Customer_Product_Details.php?id={$product['ID']}' class='btn-primary'>View Details</a>";
                            echo "</div></div>";
                        }
                    } else {
                        echo "<p>No new products found.</p>";
                    }
                    ?>
                </div>

                <h2 class="section-heading">Popular Products</h2>
                <div class="product-grid">
                    <?php
                    if ($popular_products->num_rows > 0) {
                        while ($product = $popular_products->fetch_assoc()) {
                            echo "<div class='card'>";
                            // echo "<img src='{$product['image']}' alt='{$product['name']}'>";
                            echo "<div class='card-content'>";
                            echo "<h3>{$product['name']}</h3>";
                            echo "<p>$" . number_format($product['price'], 2) . "</p>";
                            echo "<a href='http://localhost/Thrift/Frontend_html/Customer_Product_Details.php?id={$product['ID']}' class='btn-primary'>View Details</a>";
                            echo "</div></div>";
                        }
                    } else {
                        echo "<p>No popular products found.</p>";
                    }
                    ?>
                </div>
            </section>
        </div>
    </body>
</html>

<?php $conn->close(); ?>