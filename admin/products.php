<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

requireAuth(['Admin', 'Moderator']);

$database = Database::getInstance();
$db = $database->getConnection();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];
    $action = $_POST['action'];
    
    if ($product_id && in_array($action, ['approve', 'reject'])) {
        $new_status = ($action === 'approve') ? 'approved' : 'rejected';
        
        $query = "UPDATE product SET status = ?, updated_at = NOW() WHERE productID = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$new_status, $product_id])) {
            $success = "Product has been successfully {$new_status}.";
        } else {
            $error = "Failed to update product status.";
        }
    }
}

$pending_query = "SELECT p.*, u.name as seller_name, u.email as seller_email
                  FROM product p
                  JOIN user u ON p.sellerID = u.userID
                  WHERE p.status = 'pending'
                  ORDER BY p.created_at ASC";
$pending_stmt = $db->prepare($pending_query);
$pending_stmt->execute();
$pending_products = $pending_stmt->fetchAll(PDO::FETCH_ASSOC);

$recent_query = "SELECT p.*, u.name as seller_name
                 FROM product p
                 JOIN user u ON p.sellerID = u.userID
                 WHERE p.status IN ('approved', 'rejected')
                 ORDER BY p.updated_at DESC
                 LIMIT 10";
$recent_stmt = $db->prepare($recent_query);
$recent_stmt->execute();
$recent_products = $recent_stmt->fetchAll(PDO::FETCH_ASSOC);
?>























<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Moderation - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="../index.php" class="logo">🛍️ SecondHand Shop</a>
            <ul class="nav-links">
                <li><a href="../dashboard.php">Dashboard</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="tickets.php">Tickets</a></li>
                <?php if (hasRole('Admin')): ?>
                    <li><a href="users.php">Users</a></li>
                <?php endif; ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle">Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?></a>
                    <ul class="dropdown-menu">
                        <li><a href="../profile.php">My Profile</a></li>
                        <li><a href="../logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="card">
            <h1>🔍 Product Moderation</h1>
            <p>Review and moderate products submitted by sellers.</p>
        </div>

        <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <div class="card">
            <h2>⏳ Pending Products (<?php echo count($pending_products); ?>)</h2>
            <?php if (empty($pending_products)): ?>
                <p>No products are pending review. Great job!</p>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($pending_products as $product): ?>
                        <div class="product-card" style="border: 2px solid #ffc107;">
                            <div class="product-title"><?php echo htmlspecialchars($product['name']); ?></div>
                            <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                            <div class="product-condition"><?php echo htmlspecialchars($product['condition']); ?></div>
                            <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                            <p><strong>Quantity:</strong> <?php echo $product['quantity']; ?></p>
                            <div style="margin: 1rem 0; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                                <p><strong>Seller:</strong> <?php echo htmlspecialchars($product['seller_name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($product['seller_email']); ?></p>
                            </div>
                            <div class="btn-group">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="product_id" value="<?php echo $product['productID']; ?>">
                                    <button type="submit" name="action" value="approve" class="btn btn-success">✅ Approve</button>
                                </form>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="product_id" value="<?php echo $product['productID']; ?>">
                                    <button type="submit" name="action" value="reject" class="btn btn-danger">❌ Reject</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>📋 Recently Reviewed Products</h2>
            <?php if (empty($recent_products)): ?>
                <p>No products have been reviewed recently.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Seller</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['seller_name']); ?></td>
                                <td>$<?php echo number_format($product['price'], 2); ?></td>
                                <td><span class="status status-<?php echo $product['status']; ?>"><?php echo ucfirst($product['status']); ?></span></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($product['updated_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    <script src="../assets/js/main.js"></script>
</body>
</html>