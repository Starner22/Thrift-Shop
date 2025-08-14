<?php

$conn = new mysqli("localhost", "root", "", "thrift_shop");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])){
  $product_id = intval($_GET['id']);
} else {
  $product_id = 0;
}

$details_sql = "SELECT p.Product_ID as ID, p.Product_name as Name, Description, p.Selling_Price as Price, p.Image as Image, p.Quality, p.Category_ID
                FROM Product p
                WHERE Product_ID = $product_id AND Is_Product_Shown = 'Listed'";
$result = $conn->query($details_sql);
$product = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
    <head>
        <title><?php echo ($product['Name']); ?> – Thrift Store</title>
        <link rel='stylesheet' href='http://localhost/Thrift/Styles/customer_product_details_styles.css'/>
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

        <div class='container search-bar-container'>
            <form method='GET' action='http://localhost/Thrift/Frontend_html/customer_browse_all.php' class='search-form'>
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
