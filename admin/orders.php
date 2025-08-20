<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

requireAuth(['Admin', 'Moderator']);

$database = Database::getInstance();
$db = $database->getConnection();

$success = '';
$error = '';
$allowed_statuses = ['Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];

    if (!in_array($new_status, $allowed_statuses)) {
        $error = "Invalid status selected.";
    } else {
        try {
            $db->beginTransaction();

            $current_status_stmt = $db->prepare("SELECT orderStatus FROM `order` WHERE orderID = ?");
            $current_status_stmt->execute([$order_id]);
            $current_status = $current_status_stmt->fetchColumn();

            if ($new_status === 'Cancelled' && $current_status !== 'Cancelled') {
                $items_stmt = $db->prepare("SELECT productID, quantity FROM orderitem WHERE orderID = ?");
                $items_stmt->execute([$order_id]);
                $order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

                $stock_update_stmt = $db->prepare("UPDATE product SET quantity = quantity + ? WHERE productID = ?");

                foreach ($order_items as $item) {
                    $stock_update_stmt->execute([$item['quantity'], $item['productID']]);
                }
                 $success .= "Stock for Order #{$order_id} has been restocked. ";
            }

            $update_stmt = $db->prepare("UPDATE `order` SET orderStatus = ? WHERE orderID = ?");
            if ($update_stmt->execute([$new_status, $order_id])) {
                $success .= "Order #{$order_id} status updated to '{$new_status}'.";
            } else {
                throw new Exception("Failed to update order status.");
            }

            $db->commit();

        } catch (Exception $e) {
            $db->rollBack();
            $error = "An error occurred: " . $e->getMessage();
        }
    }
}

$query = "SELECT o.orderID, o.totalPrice, o.orderStatus, o.orderDate, u.name as buyer_name
          FROM `order` o
          JOIN `user` u ON o.buyerID = u.userID
          ORDER BY o.orderDate DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>














<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="../index.php" class="logo">🛍️ Thrift Store</a>
            <ul class="nav-links">
                <li><a href="../dashboard.php">Dashboard</a></li>
                <li><a href="products.php">Products</a></li>
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
            <h1>Manage All Orders</h1>
        </div>
        
        <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <div class="card">
            <?php if(empty($orders)): ?>
                <p>No orders have been placed yet.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Buyer</th>
                            <th>Total Price</th>
                            <th>Order Date</th>
                            <th style="width: 200px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['orderID']; ?></td>
                                <td><?php echo htmlspecialchars($order['buyer_name']); ?></td>
                                <td>$<?php echo number_format($order['totalPrice'], 2); ?></td>
                                <td><?php echo date('F j, Y', strtotime($order['orderDate'])); ?></td>
                                <td>
                                    <form method="POST" class="status-form">
                                        <input type="hidden" name="order_id" value="<?php echo $order['orderID']; ?>">
                                        <select name="status" class="status-select status-<?php echo strtolower($order['orderStatus']); ?>" onchange="this.form.submit()">
                                            <?php foreach ($allowed_statuses as $status): ?>
                                                <option value="<?php echo $status; ?>" <?php echo ($order['orderStatus'] === $status) ? 'selected' : ''; ?>>
                                                    <?php echo $status; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" name="update_status" class="hidden">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <style>
        .status-select {
            border-radius: 20px;
            border: none;
            padding: 0.5rem 1rem;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
        }
        .hidden {
            display: none;
        }
    </style>

    <script src="../assets/js/main.js"></script>
</body>
</html>