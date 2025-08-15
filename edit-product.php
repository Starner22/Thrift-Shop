<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/models/ProductProxy.php';

requireAuth('Seller');





$database = new Database();
$db = $database->getConnection();
$user_id = getUserId();
$error = '';
$success = '';

$product_id = (int)($_GET['id'] ?? 0);
if ($product_id === 0) {
    header("Location: my-products.php");
    exit();
}

$product_proxy = ProductProxy::createFromId($db, $product_id, $user_id);

if (!$product_proxy) {
    header("Location: my-products.php?error=notfound");
    exit();
}

$category_stmt = $db->prepare("SELECT * FROM categories ORDER BY name ASC");
$category_stmt->execute();
$categories = $category_stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_proxy->name = trim($_POST['name'] ?? '');
    $product_proxy->description = trim($_POST['description'] ?? '');
    $product_proxy->price = $_POST['price'] ?? '';
    $product_proxy->condition = $_POST['condition'] ?? '';
    $product_proxy->quantity = (int)($_POST['quantity'] ?? 1);
    $product_proxy->categoryID = (int)($_POST['category_id'] ?? 0);
    
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $image = $_FILES['product_image'];
        $upload_dir = 'uploads/';
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        
        if (in_array($image['type'], $allowed_types) && $image['size'] < 5000000) { 
            if ($product_proxy->image_path && file_exists($product_proxy->image_path)) {
                unlink($product_proxy->image_path);
            }
            $image_name = uniqid('prod_') . '_' . basename($image['name']);
            $new_image_path = $upload_dir . $image_name;
            
            if (move_uploaded_file($image['tmp_name'], $new_image_path)) {
                $product_proxy->image_path = $new_image_path;
            } else {
                $error = "Failed to upload new image.";
            }
        } else {
            $error = "Invalid file type or size is too large (Max 5MB).";
        }
    }

    try {
        if (empty($error) && $product_proxy->save()) {
            $success = 'Product updated successfully! It has been re-submitted for review.';
        } else {
            $error = $error ?: 'Failed to update product.';
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
$common_conditions = ['New', 'Like New', 'Very Good', 'Good', 'Fair', 'Poor'];
?>






















<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product - <?php echo htmlspecialchars($product_proxy->name); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.php" class="logo">🛍️ SecondHand Shop</a>
            <ul class="nav-links">
                <li><a href="my-products.php">Back to My Products</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="card" style="max-width: 600px; margin: 2rem auto;">
            <h1>✏️ Edit Product</h1>
            <p>After saving, your product will be re-submitted for review.</p>

            <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Current Image</label><br>
                    <img src="<?php echo htmlspecialchars($product_proxy->image_path ?? 'assets/images/placeholder.png'); ?>" alt="Current product image" style="max-width: 150px; border-radius: 5px;">
                </div>
                <div class="form-group">
                    <label for="product_image">Upload New Image (Optional)</label>
                    <input type="file" id="product_image" name="product_image" accept="image/jpeg, image/png, image/gif">
                </div>
                <div class="form-group">
                    <label for="name">Product Name *</label>
                    <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($product_proxy->name); ?>">
                </div>
                <div class="form-group">
                    <label for="description">Product Description *</label>
                    <textarea id="description" name="description" required rows="4"><?php echo htmlspecialchars($product_proxy->description); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="category_id">Category *</label>
                    <select id="category_id" name="category_id" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['categoryID']; ?>" <?php echo ($product_proxy->categoryID == $category['categoryID']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="price">Price ($) *</label>
                        <input type="number" id="price" name="price" required step="0.01" min="0.01" value="<?php echo htmlspecialchars($product_proxy->price); ?>">
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity *</label>
                        <input type="number" id="quantity" name="quantity" required min="1" value="<?php echo htmlspecialchars($product_proxy->quantity); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="condition">Condition *</label>
                    <select id="condition" name="condition" required>
                        <?php foreach ($common_conditions as $cond): ?>
                            <option value="<?php echo $cond; ?>" <?php echo ($product_proxy->condition === $cond) ? 'selected' : ''; ?>><?php echo $cond; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Save Changes</button>
            </form>
        </div>
    </div>
</body>
</html>