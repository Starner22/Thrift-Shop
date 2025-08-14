<?php

$conn = new mysqli("localhost", "root", "", "thrift_shop");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$category_id = $_GET['category'];

$category_name = $conn->query("SELECT Name FROM Category WHERE Category_ID = $category_id");
if ($cat_row = $category_name->fetch_assoc()) {
    $category_name = ($cat_row['Name']);
}else{
    $category_name = "Unknown Category";
}

// 1. Get all other user inputs from the URL for filtering and sorting
$search_term = $_GET['search'] ?? '';
$sort_option = $_GET['sort'] ?? 'name_asc';
$min_price = $_GET['min_price'] ?? 0;
$max_price = $_GET['max_price'] ?? 999999;
$quality_filter = $_GET['quality'] ?? [];
$review_filter = $_GET['review'] ?? [];

// 2. Build the WHERE clause (Conceptual Decorator Pattern)
// In a proper OOP approach, you'd chain your Decorator classes here.
$where_clause = " WHERE p.Is_Product_Shown = 'Listed' AND p.Category_ID = " . intval($category_id);
// Search filter
if (!empty($search_term)) {
    $where_clause .= " AND (p.Product_name LIKE '%" . $conn->real_escape_string($search_term) . "%' OR p.Description LIKE '%" . $conn->real_escape_string($search_term) . "%')";
}
// Price filter
$where_clause .= " AND p.Selling_Price BETWEEN " . intval($min_price) . " AND " . intval($max_price);
// Quality filter
if (!empty($quality_filter)) {
    $quality_list = "'" . implode("','", array_map([$conn, 'real_escape_string'], $quality_filter)) . "'";
    $where_clause .= " AND p.Quality IN ($quality_list)";
}
// Review filter (Requires a new column in your Product table, e.g., Average_Review_Score)
if (!empty($review_filter)) {
    $review_list = implode(",", array_map('intval', $review_filter));
    $where_clause .= " AND p.Average_Review_Score IN ($review_list)";
}

// 3. Build the ORDER BY clause (Conceptual Strategy Pattern)
// In a proper OOP approach, you'd use your SortingStrategy classes here.
$order_by_clause = "";
if ($sort_option === 'name_asc') {
    $order_by_clause = " ORDER BY p.Product_name ASC";
} elseif ($sort_option === 'name_desc') {
    $order_by_clause = " ORDER BY p.Product_name DESC";
} elseif ($sort_option === 'price_asc') {
    $order_by_clause = " ORDER BY p.Selling_Price ASC";
} elseif ($sort_option === 'price_desc') {
    $order_by_clause = " ORDER BY p.Selling_Price DESC";
} elseif ($sort_option === 'popularity_desc') {
    // Requires a new column in your Product table, e.g., Purchase_Count or View_Count
    $order_by_clause = " ORDER BY p.Purchase_Count DESC";
} elseif ($sort_option === 'popularity_asc') {
    $order_by_clause = " ORDER BY p.Purchase_Count ASC";
}

// 4. Construct the final query and fetch products
$sql = "SELECT p.Product_ID AS ID, p.Product_name AS name, p.Selling_Price AS price, p.Image AS image
        FROM Product p"
        . $where_clause
        . $order_by_clause;

$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html>
    <head>
        <title><?php echo $category_name; ?> – Thrift Store</title>
        <link rel="stylesheet" href="http://localhost/Thrift/Styles/customer_browse_all_styles.css"/>
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
                <?php
                echo "<input type='text' name='search' placeholder='Search products...' class='search-input' value='" . ($search_term) . "'>";
                ?>
                <button type="submit" class="search-button">
                    Search
                </button>
            </form>
        </div>

        <div class="browse-main container">
            <aside class="sidebar">
                <form method="GET" action="http://localhost/Thrift/Frontend_html/customer_browse_categorized.php">
                    <?php 
                    echo "<input type='hidden' name='category' value='" . $category_id . "'>"; 
                    echo "<input type='hidden' name='search' value='" . $search_term . "'>"; 
                    ?>
                    
                    <h3 class="filter-heading">Filters</h3>

                    <h4>Price Range</h4>
                    <div class="filter-group">
                        <?php
                        echo "<input type='number' name='min_price' placeholder='Min' class='price-input' value='" . ($min_price) . "'>";
                        echo "<span>-</span>";
                        echo "<input type='number' name='max_price' placeholder='Max' class='price-input' value='" . ($max_price) . "'>";
                        ?>
                    </div>

                    <h4>Quality</h4>
                    <div class="filter-group">
                        <?php
                        $qualities = ['Excellent', 'Good', 'Normal', 'Poor'];
                        foreach ($qualities as $quality){
                            $checked = in_array($quality, $quality_filter) ? 'checked' : '';
                            echo "<label class='checkbox-label'>";
                            echo "<input type='checkbox' name='quality[]' value='" . ($quality) . "' " . $checked . ">";
                            echo ($quality);
                            echo "</label>";
                        } 
                        ?>
                    </div>

                    <h4>Reviews (stars)</h4>
                    <div class="filter-group">
                        <?php
                        $reviews = [5, 4, 3, 2, 1];
                        foreach ($reviews as $review){
                            $checked = in_array($review, $review_filter) ? 'checked' : '';
                            echo "<label class='checkbox-label'>";
                            echo "<input type='checkbox' name='review[]' value='" . ($review) . "' " . $checked . ">";
                            echo ($review) . " stars";
                            echo "</label>";
                        }
                        ?>
                    </div>

                    <button type="submit" class="apply-filters-btn">Apply Filters</button>
                </form>
            </aside>

            <main class="main-product-list">
                <div class="sort-bar">
                    <span class="info-text">Showing: <?php echo $category_name; ?></span>
                    <form method="GET" action="customer_browse_categorized.php" class="sort-form">
                        <?php
                        echo "<input type='hidden' name='category' value='" . $category_id . "'>";
                        echo "<label for='sort-dropdown'>Sort by:</label>";
                        echo "<select name='sort' onchange='this.form.submit()'>";
                            echo "<option value='name_asc' " . ($sort_option === 'name_asc' ? 'selected' : '') . ">Name A-Z</option>";
                            echo "<option value='name_desc' " . ($sort_option === 'name_desc' ? 'selected' : '') . ">Name Z-A</option>";
                            echo "<option value='price_asc' " . ($sort_option === 'price_asc' ? 'selected' : '') . ">Price Low to High</option>";
                            echo "<option value='price_desc' " . ($sort_option === 'price_desc' ? 'selected' : '') . ">Price High to Low</option>";
                            echo "<option value='price_asc' " . ($sort_option === 'popularity_asc' ? 'selected' : '') . ">Popularity Low to High</option>";
                            echo "<option value='price_desc' " . ($sort_option === 'popularity_desc' ? 'selected' : '') . ">Popularity High to Low</option>";
                        echo "</select>";
                        echo "<input type='hidden' name='search' value='" . ($search_term) . "'>";
                        echo "<input type='hidden' name='min_price' value='" . ($min_price) . "'>";
                        echo "<input type='hidden' name='max_price' value='" . ($max_price) . "'>";
                        foreach ($quality_filter as $quality) {
                            echo "<input type='hidden' name='quality[]' value='" . ($quality) . "'>";
                        }
                        foreach ($review_filter as $review) {
                            echo "<input type='hidden' name='review[]' value='" . ($review) . "'>";
                        }
                        ?>
                    </form>
                </div>

                <div class="product-grid">
                    <?php
                    if ($result->num_rows > 0) {
                        while ($product = $result->fetch_assoc()) {
                            echo "<div class='card'>";
                            // echo "<img src='{$product['image']}' alt='{$product['name']}'>";
                            echo "<div class='card-content'>";
                            echo "<h3>{$product['name']}</h3>";
                            echo "<p>$" . number_format($product['price'], 2) . "</p>";
                            echo "<a href='http://localhost/Thrift/Frontend_html/Customer_Product_Details.php?id={$product['ID']}' class='btn-primary'>View Details</a>";
                            echo "</div></div>";
                        }
                    } else {
                        echo "<p>No products found in this category matching your criteria.</p>";
                    }
                    ?>
                </div>
            </main>
        </div>
    </body>
</html>

<?php $conn->close(); ?>