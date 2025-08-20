<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

requireAuth('Admin'); 

$database = Database::getInstance();
$db = $database->getConnection();

$success = '';
$error = '';
$allowed_roles = ['Admin', 'Moderator', 'Seller', 'Buyer'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $user_id_to_update = (int)$_POST['user_id'];
    $new_role = $_POST['role'];

    if ($user_id_to_update === getUserId()) {
        $error = "You cannot change your own role from this panel.";
    } elseif (!in_array($new_role, $allowed_roles)) {
        $error = "Invalid role selected.";
    } else {
        $update_stmt = $db->prepare("UPDATE user SET role = ? WHERE userID = ?");
        if ($update_stmt->execute([$new_role, $user_id_to_update])) {
            $success = "User role updated successfully.";
        } else {
            $error = "Failed to update user role.";
        }
    }
}

$query = "SELECT userID, name, email, role, registration_date FROM `user` ORDER BY userID ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>














<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="../index.php" class="logo">🛍️ Thrift Store</a>
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
            <h1>Manage All Users</h1>
        </div>

        <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Current Role</th>
                        <th style="width: 250px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['userID']; ?></td>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td>
                                <?php if ($user['userID'] === getUserId()): ?>
                                    <small><em>(This is you)</em></small>
                                <?php else: ?>
                                    <form method="POST" style="display: flex; gap: 0.5rem; align-items: center;">
                                        <input type="hidden" name="user_id" value="<?php echo $user['userID']; ?>">
                                        <select name="role" style="padding: 0.5rem;">
                                            <?php foreach ($allowed_roles as $role): ?>
                                                <option value="<?php echo $role; ?>" <?php echo ($user['role'] === $role) ? 'selected' : ''; ?>>
                                                    <?php echo $role; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" name="update_role" class="btn btn-primary" style="padding: 0.5rem 1rem;">Update</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="../assets/js/main.js"></script>
</body>
</html>