<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

requireAuth();








$database = new Database();
$db = $database->getConnection();
$user_id = getUserId();

$success = '';
$error = '';


$stmt = $db->prepare("SELECT name, email, role, registration_date FROM user WHERE userID = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_details'])) {
        $name = trim($_POST['name']);
        if (empty($name)) {
            $error = 'Name cannot be empty.';
        } else {
            $update_stmt = $db->prepare("UPDATE user SET name = ? WHERE userID = ?");
            if ($update_stmt->execute([$name, $user_id])) {
                $_SESSION['user_name'] = $name; // Update session variable
                $user['name'] = $name; // Update displayed name on page
                $success = 'Profile details updated successfully!';
            } else {
                $error = 'Failed to update profile details.';
            }
        }
    }


    if (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = 'Please fill in all password fields.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match.';
        } elseif (strlen($new_password) < 6) {
            $error = 'New password must be at least 6 characters long.';
        } else {
            // Verify current password
            $pass_stmt = $db->prepare("SELECT password FROM user WHERE userID = ?");
            $pass_stmt->execute([$user_id]);
            $hashed_password = $pass_stmt->fetchColumn();

            if (password_verify($current_password, $hashed_password)) {
                $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_pass_stmt = $db->prepare("UPDATE user SET password = ? WHERE userID = ?");
                if ($update_pass_stmt->execute([$new_hashed_password, $user_id])) {
                    $success = 'Password updated successfully!';
                } else {
                    $error = 'Failed to update password.';
                }
            } else {
                $error = 'Incorrect current password.';
            }
        }
    }
}
?>
































<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Second-Hand Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.php" class="logo">🛍️ SecondHand Shop</a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <?php if (hasRole('Admin')): ?>
    <li><a href="admin/products.php">Products</a></li>
    <li><a href="admin/orders.php">Orders</a></li>
    <li><a href="admin/tickets.php">Tickets</a></li>
        <li><a href="admin/users.php">Users</a></li>
    <?php endif; ?>
                <!-- The new dropdown menu will be added here in the next step -->
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
        <div class="card">
            <h1>👤 My Profile</h1>
        </div>

        <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <div class="card">
            <h2>Profile Details</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                    <small>Email address cannot be changed.</small>
                </div>
                 <div class="form-group">
                    <label>Role</label>
                    <input type="text" value="<?php echo htmlspecialchars($user['role']); ?>" disabled>
                </div>
                 <div class="form-group">
                    <label>Member Since</label>
                    <input type="text" value="<?php echo date('F j, Y', strtotime($user['registration_date'])); ?>" disabled>
                </div>
                <button type="submit" name="update_details" class="btn btn-primary">Update Details</button>
            </form>
        </div>

        <div class="card">
            <h2>Change Password</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                </div>
                <button type="submit" name="update_password" class="btn btn-primary">Update Password</button>
            </form>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>