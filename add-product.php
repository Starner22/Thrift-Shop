<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

requireAuth('Seller');


$database = Database::getInstance();
$db = $database->getConnection();
$user_id = getUserId();

$error = '';
$success = '';

$category_stmt = $db->prepare("SELECT * FROM categories ORDER BY name ASC");
$category_stmt->execute();
$categories = $category_stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? '';
    $condition = $_POST['condition'] ?? '';
    $quantity = (int)($_POST['quantity'] ?? 1);
    $category_id = (int)($_POST['category_id'] ?? 0);
    $image_path = null;

    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $image = $_FILES['product_image'];
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        
        if (in_array($image['type'], $allowed_types) && $image['size'] < 5000000) { 
            $image_name = uniqid('prod_') . '_' . basename($image['name']);
            $image_path = $upload_dir . $image_name;
            
            if (!move_uploaded_file($image['tmp_name'], $image_path)) {
                $error = "Failed to move uploaded file.";
                $image_path = null;
            }
        } else {
            $error = "Invalid file type or size is too large (Max 5MB).";
        }
    }

    if (empty($error)) {
        if (empty($name) || empty($description) || empty($price) || empty($category_id)) {
            $error = 'Please fill in all required fields.';
        } else {
            try {
                $query = "INSERT INTO product (name, description, price, `condition`, quantity, categoryID, image_path, sellerID, status) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$name, $description, $price, $condition, $quantity, $category_id, $image_path, $user_id])) {
                    $success = 'Product submitted successfully! It will be reviewed by our moderators.';
                    $_POST = [];
                } else {
                    $error = 'Failed to submit product.';
                }
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}
$common_conditions = ['New', 'Like New', 'Very Good', 'Good', 'Fair', 'Poor'];
?>






































<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Thrift Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.php" class="logo">🛍️ Thrift Store</a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
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
        <div class="card" style="max-width: 600px; margin: 2rem auto;">
            <h1>📦 Add New Product</h1>
            
            <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

            <form method="POST" id="productForm" enctype="multipart/form-data">
                
                <div class="form-group">
                    <label for="name">Product Name *</label>
                    <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="description">Product Description *</label>
                    <textarea id="description" name="description" required rows="4"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="product_image">Product Image</label>
                    <input type="file" id="product_image" name="product_image" accept="image/jpeg, image/png, image/gif">
                    <small>Upload a clear image of your product. Max 5MB.</small>
                </div>
                
                <div class="form-group">
                    <label for="category_id">Category *</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Select a category...</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['categoryID']; ?>" <?php echo (($_POST['category_id'] ?? '') == $category['categoryID']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="price">Price ($) *</label>
                        <input type="number" id="price" name="price" required step="0.01" min="0.01" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity *</label>
                        <input type="number" id="quantity" name="quantity" required min="1" value="<?php echo htmlspecialchars($_POST['quantity'] ?? '1'); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="condition">Condition *</label>
                    <select id="condition" name="condition" required>
                        <option value="">Select condition...</option>
                        <?php foreach ($common_conditions as $cond): ?>
                            <option value="<?php echo $cond; ?>" <?php echo (($_POST['condition'] ?? '') === $cond) ? 'selected' : ''; ?>><?php echo $cond; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Submit Product for Review</button>
            </form>
        </div>
    </div>
</body>
</html>