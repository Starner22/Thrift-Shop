<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

$database = Database::getInstance();;

$db = $database->getConnection();

$error = '';
$success = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_ticket'])) {
    if (!isLoggedIn()) {
        $error = 'You must be logged in to create a support ticket.';
    } else {
        $issue_description = trim($_POST['issue_description'] ?? '');
        
        if (empty($issue_description)) {
            $error = 'Please describe your issue.';
        } elseif (strlen($issue_description) < 10) {
            $error = 'Please provide more details (minimum 10 characters).';
        } else {
            $user_id = getUserId();
            try {
                $query = "INSERT INTO supportticket (userID, issueDescription, status) VALUES (?, ?, 'Open')";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$user_id, $issue_description])) {
                    $success = 'Support ticket created successfully! We will get back to you soon.';
                    $_POST = [];
                } else {
                    $error = 'Failed to create support ticket. Please try again.';
                }
            } catch (PDOException $e) {
                $error = 'Database error occurred. Please try again.';
            }
        }
    }
}




























$user_tickets = [];
if (isLoggedIn()) {
    $user_id = getUserId();
    $tickets_query = "SELECT ticketID, issueDescription, status, created_at FROM supportticket WHERE userID = ? ORDER BY created_at DESC";
    $tickets_stmt = $db->prepare($tickets_query);
    $tickets_stmt->execute([$user_id]);
    $user_tickets = $tickets_stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - Thrift Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.php" class="logo">🛍️ Thrift Store</a>
            <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <?php if (isLoggedIn()): ?>
        <li><a href="dashboard.php">Dashboard</a></li>
        <?php if (hasRole('Buyer')): ?>
            <li><a href="cart.php">Cart <span id="cart-count" class="badge"></span></a></li>
            <li><a href="wishlist.php">Wishlist</a></li>
        <?php endif; ?>
        <?php if (hasRole('Admin')): ?>
    <li><a href="admin/products.php">Products</a></li>
    <li><a href="admin/orders.php">Orders</a></li>
    <li><a href="admin/tickets.php">Tickets</a></li>
        <li><a href="admin/users.php">Users</a></li>
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
        <div class="card" style="max-width: 600px; margin: 2rem auto;">
            <h1>🛠️ Customer Support</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if (isLoggedIn()): ?>
                <form method="POST" id="supportForm">
                    <div class="form-group">
                        <label for="issue_description">Describe your issue *</label>
                        <textarea id="issue_description" name="issue_description" required rows="5"
                                  placeholder="Please describe your issue in detail..."><?php echo htmlspecialchars($_POST['issue_description'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" name="create_ticket" class="btn btn-primary" style="width: 100%;">
                        Submit Support Ticket
                    </button>
                </form>
            <?php else: ?>
                <p>Please <a href="login.php">log in</a> to create a support ticket.</p>
            <?php endif; ?>
        </div>

        <?php if (isLoggedIn() && !empty($user_tickets)): ?>
            <div class="card">
                <h2>📋 My Support Tickets</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ticket ID</th>
                            <th>Issue</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($user_tickets as $ticket): ?>
                            <tr>
                                <td>#<?php echo $ticket['ticketID']; ?></td>
                                <td><?php echo htmlspecialchars(substr($ticket['issueDescription'], 0, 100)); ?>...</td>
                                <td>
                                    <span class="status status-<?php echo strtolower(str_replace(' ', '', $ticket['status'])); ?>">
                                        <?php echo $ticket['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('F j, Y', strtotime($ticket['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>