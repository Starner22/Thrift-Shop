<?php
require_once 'config/database.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $database = Database::getInstance();
        $db = $database->getConnection();

        $stmt = $db->prepare("SELECT userID FROM user WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
    $token = bin2hex(random_bytes(50));

    // Let MySQL generate expiry (1 hour from NOW)
    $insert_stmt = $db->prepare("
        INSERT INTO password_resets (email, token, expires_at)
        VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))
    ");
    $insert_stmt->execute([$email, $token]);

    $reset_link = "http://localhost/shop/reset-password.php?token=" . $token;
    $message = "A password reset link has been generated. In a real application, this would be emailed to you. For now, please use this link: <br><a href='{$reset_link}'>{$reset_link}</a>";
} else {
    $message = "If an account with that email exists, a password reset link has been generated.";
}

    }
}
?>









<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - Thrift Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.php" class="logo">🛍️ Thirft Store</a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="card" style="max-width: 500px; margin: 2rem auto;">
            <h1>Forgot Your Password?</h1>
            <p>Enter your email address and we will generate a link to reset your password.</p>

            <?php if ($message): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

            <?php if (!$message): ?>
            <form method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Send Reset Link</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>