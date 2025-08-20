<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

$database = new Database();
$db = $database->getConnection();

// Strategy DP Implementation

    //Strategy Interface
    interface SortStrategy {
        public function Sort_Order(): string;
    }
    // Concrete Strategy A
    class Name_Asc implements SortStrategy {
        public function Sort_Order(): string {
            return " ORDER BY p.productID ASC";
        }
    }
    // Concrete Strategy B
    class Name_Desc implements SortStrategy {
        public function Sort_Order(): string {
            return " ORDER BY p.productID DESC";
        }
    }
    // Concrete Strategy C
    class Price_Asc implements SortStrategy {
        public function Sort_Order(): string {
            return " ORDER BY p.price ASC";
        }
    }
    // Concrete Strategy D
    class Price_Desc implements SortStrategy {
        public function Sort_Order(): string {
            return " ORDER BY p.price DESC";
        }
    }

    // Strategy Context 
    class SortContext {
        private SortStrategy $strat;

        public function __construct(SortStrategy $strat) {
            $this->strat = $strat;
        }
        public function Collect_Sort(): string {
            return $this->strat->Sort_Order();  // Targetting the Concrete Strategy based on the name of $strat
        }
    }

    // Sort Initialize

    // COllecting sort
    $sort_collect = $_GET['sort'] ?? 'name_asc'; 
    $sort_option = $sort_collect;

    //Compare the collected data and fill the $strat
    switch ($sort_collect) {
        case 'name_asc':
            $strat = new Name_Asc();
            break;
        case 'name_desc':
            $strat = new Name_Desc();
            break;
        case 'price_asc':
            $strat = new Price_Asc();
            break;
        case 'price_desc':
            $strat = new Price_Desc();
            break;
        default:
            $strat = new Name_Asc();
            break;
    }
    // Ship the $strat to Context
    $sortContext = new SortContext($strat);
    $Strategy_Sort = $sortContext->Collect_Sort(); 

// Sort should be ready to use in SQL

// Decorator DP Implementation

    //Component Interface
    interface Filter{
        public function build(): string;
    }
    // Concrete Component A
    class BaseFilter implements Filter {
        public function build(): string {
            return " WHERE 1=1";
        }
    }
    // Concrete Component B
    class CategorizedFilter implements Filter {
        private int $categoryId;

        public function __construct(int $categoryId) {
            $this->categoryId = $categoryId;
        }

        public function build(): string {
            return " WHERE p.categoryID = " . $this->categoryId;
        }
    }
    // Decorator Interface (or Abstract)
    abstract class FilterDecorator implements Filter {
        protected Filter $filter;

        public function __construct(Filter $filter) {
            $this->filter = $filter;
        }

        public function build(): string {
            return $this->filter->build();
        }
    }
    // Concrete Decorator A
    class SearchFilter extends FilterDecorator {
        private string $searchTerm;

        public function __construct(Filter $filter, string $searchTerm) {
            parent::__construct($filter);
            $this->searchTerm = $searchTerm;
        }

        public function build(): string {
            return parent::build() . " AND p.name LIKE '%{$this->searchTerm}%'";
        }
    }
    // Concrete Decorator B
    class PriceFilter extends FilterDecorator {
        private int $minPrice;
        private int $maxPrice;

        public function __construct(Filter $filter, int $minPrice, int $maxPrice) {
            parent::__construct($filter);
            $this->minPrice = $minPrice;
            $this->maxPrice = $maxPrice;
        }

        public function build(): string {
            return parent::build() . " AND p.price BETWEEN {$this->minPrice} AND {$this->maxPrice}";
        }
    }
    // Concrete Decorator C
    class QualityFilter extends FilterDecorator {
        private array $qualities;

        public function __construct(Filter $filter, array $qualities) {
            parent::__construct($filter);
            $this->qualities = $qualities;
        }

        public function build(): string {
            $list = "'" . implode("','", $this->qualities) . "'";
            return parent::build() . " AND p.condition IN ({$list})";
        }
    }

    // Filter Initialize

    // Collecting filters
    $search_term = $_GET['search'] ?? '';
    $min_price = $_GET['min_price'] ?? 0;
    $max_price = $_GET['max_price'] ?? 999999;
    $quality_filter = $_GET['quality'] ?? [];

    $filter = new BaseFilter();

    if (!empty($search_term)) {
        $filter = new SearchFilter($filter, $search_term);
    }

    $filter = new PriceFilter($filter, intval($min_price), intval($max_price));

    if (!empty($quality_filter)) {
        $filter = new QualityFilter($filter, $quality_filter);
    }

    $Decorator_Filter = $filter->build();
// Filters should be ready to use in sql

$sql = $db->prepare("SELECT p.productID AS ID, p.name AS name, p.price AS price, p.image_path AS image, c.name AS category_name
                FROM Product p
                LEFT JOIN Categories c ON p.categoryID = c.categoryID"
                . $Decorator_Filter ." ". $Strategy_Sort);


$sql->execute();
$result = $sql->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html>
    <head>
        <title>Second-Hand Shop - All Products</title>
        <link rel="stylesheet" href="Styles/customer_browse_all_styles.css"/>
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

        <div class="container search-bar-container">
            <form method="GET" action="customer_browse_all.php" class="search-form">
                <?php
                echo "<input type='text' name='search' placeholder='Search products...' class='search-input' value='" . ($search_term) . "'>";
                ?>
                <button type="submit" class="btn-primary">
                    Search
                </button>
            </form>
        </div>

        <div class="browse-main container">
            <aside class="sidebar">
                <form method="GET" action="customer_browse_all.php">
                    <?php 
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
                        $qualities = ['Excellent', 'Good', 'Normal', 'Subpar'];
                        foreach ($qualities as $quality){
                            $checked = in_array($quality, $quality_filter) ? 'checked' : '';
                            echo "<label class='checkbox-label'>";
                            echo "<input type='checkbox' name='quality[]' value='" . ($quality) . "' " . $checked . ">";
                            echo ($quality);
                            echo "</label>";
                        } 
                        ?>
                    </div>

                    <button type="submit" class="btn-primary">Apply Filters</button>
                </form>
            </aside>

            <main class="main-product-list">
                <div class="sort-bar">
                    <span class="info-text">Showing all products</span>
                    <form method="GET" action="customer_browse_all.php" class="sort-form">
                        <label for="sort-dropdown">Sort by:</label>
                        <?php
                        echo "<select name='sort' onchange='this.form.submit()'>";
                            echo "<option value='name_asc' " . ($sort_option === 'name_asc' ? 'selected' : '') . ">Name A-Z</option>";
                            echo "<option value='name_desc' " . ($sort_option === 'name_desc' ? 'selected' : '') . ">Name Z-A</option>";
                            echo "<option value='price_asc' " . ($sort_option === 'price_asc' ? 'selected' : '') . ">Price Low to High</option>";
                            echo "<option value='price_desc' " . ($sort_option === 'price_desc' ? 'selected' : '') . ">Price High to Low</option>";
                        echo "</select>";
                        echo "<input type='hidden' name='search' value='" . ($search_term) . "'>";
                        echo "<input type='hidden' name='min_price' value='" . ($min_price) . "'>";
                        echo "<input type='hidden' name='max_price' value='" . ($max_price) . "'>";
                        foreach ($quality_filter as $quality) {
                            echo "<input type='hidden' name='quality[]' value='" . ($quality) . "'>";
                        }
                        ?>
                    </form>
                </div>

                <div class="product-grid">
                    <?php
                    if (!empty($result)) {    
                        foreach ($result as $product) {
                            echo "<div class='card'>";
                            echo "<img src='{$product['image']}' alt='" . ($product['name']) . "' class='product-img'>";
                            echo "<div class='card-content'>";
                            echo "<h3>" . ($product['name']) . "</h3>";
                            echo "<p class='product-category'>" . ($product['category_name'] ?? 'Uncategorized') . "</p>";
                            echo "<p>$" . number_format($product['price'], 2) . "</p>";
                            echo "<a href='Customer_Product_Details.php?id={$product['ID']}' class='btn-primary'>View Details</a>";
                            echo "</div></div>";
                        }
                    } else {
                        echo "<p>No products found matching your criteria.</p>";
                    }
                    ?>
                </div>
            </main>
        </div>
    </body>
</html>