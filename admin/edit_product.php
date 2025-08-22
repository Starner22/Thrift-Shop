<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

requireAuth(['Admin', 'Moderator']);

$database = Database::getInstance();
$db = $database->getConnection();

$success = '';
$error = '';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid product ID.");
}

$product_id = (int)$_GET['id'];

// ============================
// Fetch product info
// ============================
$stmt = $db->prepare("SELECT * FROM product WHERE productID = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Product not found.");
}

// Fetch categories for dropdown
$cat_stmt = $db->prepare("SELECT categoryID, name FROM categories ORDER BY name ASC");
$cat_stmt->execute();
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

// ============================
// Handle Update / Delist
// ============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delist') {
        $delist_stmt = $db->prepare("UPDATE product SET status = 'delisted', updated_at = NOW() WHERE productID = ?");
        if ($delist_stmt->execute([$product_id])) {
            $success = "Product has been delisted.";
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = "Failed to delist product.";
        }
    } else {
        $name = trim($_POST['name']);
        $price = (float)$_POST['price'];
        $quantity = (int)$_POST['quantity'];
        $condition = $_POST['condition'];
        $description = trim($_POST['description']);
        $categoryID = !empty($_POST['categoryID']) ? (int)$_POST['categoryID'] : null;

        if ($name && $price > 0 && $quantity >= 0) {
            $update_stmt = $db->prepare("UPDATE product 
                SET name = ?, price = ?, quantity = ?, `condition` = ?, description = ?, categoryID = ?, updated_at = NOW()
                WHERE productID = ?");
            $updated = $update_stmt->execute([
                $name, $price, $quantity, $condition, $description, $categoryID, $product_id
            ]);

            if ($updated) {
                $success = "Product updated successfully!";
                $stmt->execute([$product_id]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = "Failed to update product.";
            }
        } else {
            $error = "Please fill out all required fields correctly.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="../index.php" class="logo">🛍️ Thrift Shop</a>
            <ul class="nav-links">
                <li><a href="../dashboard.php">Dashboard</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="tickets.php">Tickets</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="card">
            <h1>✏️ Edit Product</h1>
        </div>

        <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <div class="card">
            <form method="POST">
                <label>Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>

                <label>Price ($)</label>
                <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>

                <label>Quantity</label>
                <input type="number" name="quantity" value="<?php echo htmlspecialchars($product['quantity']); ?>" required>

                <label>Condition</label>
                <select name="condition" required>
                    <?php
                    $conditionOptions = ['new', 'used', 'refurbished']; // match your ENUM
                    foreach ($conditionOptions as $opt): ?>
                        <option value="<?php echo $opt; ?>" <?php echo ($product['condition'] === $opt) ? 'selected' : ''; ?>>
                            <?php echo ucfirst($opt); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Description</label>
                <textarea name="description" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>

                <label>Category</label>
                <select name="categoryID">
                    <option value="">-- Select Category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['categoryID']; ?>" <?php echo ($product['categoryID'] == $cat['categoryID']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn btn-primary">💾 Save Changes</button>
                <a href="products.php" class="btn btn-secondary">⬅ Back</a>
                    </form>
           
        </div>
    </div>
</body>
</html>
