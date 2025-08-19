<?php
require_once 'includes/auth.php';

require_once 'includes/factories/BuyerFactory.php';
require_once 'includes/factories/SellerFactory.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'Buyer';
    
    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (User::findByEmail($db, $email)) {
        $error = 'Email already exists.';
    } else {
        // --- REFACTORED: Use the Factory Method ---
        $factory = null;
        if ($role === 'Buyer') {
            $factory = new BuyerFactory();
        } elseif ($role === 'Seller') {
            $factory = new SellerFactory();
        }

        if ($factory) {
            $user = $factory::create($db, $name, $email, $password);
            if ($user->save()) {
                // Also create cart/wishlist for new buyers
                if ($user->role === 'Buyer') {
                    $user_id = $db->lastInsertId();
                    createCartAndWishlist($user_id, $db);
                }
                $success = 'Registration successful! You can now login.';
            } else {
                $error = 'Registration failed.';
            }
        } else {
            $error = 'Invalid account type selected.';
        }
    }
}
?>






































<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Second-Hand Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.php" class="logo">🛍️ SecondHand Shop</a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="card" style="max-width: 500px; margin: 2rem auto;">
            <h1>Create Your Account</h1>
            
            <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                    <br><a href="login.php">Click here to login</a>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
            <form method="POST" id="registerForm">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="role">Account Type</label>
                    <select id="role" name="role" required>
                        <option value="Buyer">Buyer - I want to purchase products</option>
                        <option value="Seller">Seller - I want to sell products</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="password">Password (minimum 6 characters)</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Create Account</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>