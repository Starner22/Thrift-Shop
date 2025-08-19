<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

requireAuth(['Admin', 'Moderator']);

$database = new Database();
$db = $database->getConnection();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ticket_id'])) {
    $ticket_id = $_POST['ticket_id'];
    $new_status = $_POST['status'];
    if (in_array($new_status, ['Open', 'In Progress', 'Closed'])) {
        $update_query = "UPDATE supportticket SET status = ? WHERE ticketID = ?";
        $update_stmt = $db->prepare($update_query);
        if ($update_stmt->execute([$new_status, $ticket_id])) {
            $message = "Ticket #$ticket_id status updated successfully.";
        }
    }
}

$query = "SELECT st.ticketID, st.issueDescription, st.status, st.created_at, u.name as user_name
          FROM supportticket st
          JOIN `user` u ON st.userID = u.userID
          ORDER BY st.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>



























<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Tickets - Admin</title>
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
            <h1>Manage Support Tickets</h1>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="card">
            <?php if(empty($tickets)): ?>
                <p>There are no support tickets.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ticket ID</th>
                            <th>User</th>
                            <th>Issue</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td>#<?php echo $ticket['ticketID']; ?></td>
                                <td><?php echo htmlspecialchars($ticket['user_name']); ?></td>
                                <td style="max-width: 400px;"><?php echo htmlspecialchars($ticket['issueDescription']); ?></td>
                                <td>
                                    <span class="status status-<?php echo strtolower(str_replace(' ', '', $ticket['status'])); ?>">
                                        <?php echo $ticket['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('F j, Y', strtotime($ticket['created_at'])); ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="ticket_id" value="<?php echo $ticket['ticketID']; ?>">
                                        <select name="status" onchange="this.form.submit()">
                                            <option value="Open" <?php if($ticket['status'] == 'Open') echo 'selected'; ?>>Open</option>
                                            <option value="In Progress" <?php if($ticket['status'] == 'In Progress') echo 'selected'; ?>>In Progress</option>
                                            <option value="Closed" <?php if($ticket['status'] == 'Closed') echo 'selected'; ?>>Closed</option>
                                        </select>
                                    </form>
                                </td>
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