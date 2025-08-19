<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/facades/CheckoutFacade.php';
require_once 'includes/strategies/CreditCardStrategy.php';
require_once 'includes/strategies/PayPalStrategy.php';

requireAuth('Buyer');








$database = new Database();
$db = $database->getConnection();
$user_id = getUserId();
$error = '';

$cart_query = "SELECT p.name, p.price, ci.quantity FROM cartitem ci JOIN product p ON ci.productID = p.productID JOIN cart c ON ci.cartID = c.cartID WHERE c.buyerID = ?";
$cart_stmt = $db->prepare($cart_query);
$cart_stmt->execute([$user_id]);
$cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($cart_items)) {
    header("Location: cart.php");
    exit();
}

$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_details = [
        'name' => trim($_POST['shipping_name'] ?? ''),
        'address' => trim($_POST['shipping_address'] ?? ''),
        'city' => trim($_POST['shipping_city'] ?? ''),
        'postal_code' => trim($_POST['shipping_postal_code'] ?? '')
    ];
    $payment_method_string = trim($_POST['payment_method'] ?? '');

    if (in_array('', $shipping_details) || empty($payment_method_string)) {
        $error = "Please fill in all required shipping and payment fields.";
    } else {
        $payment_strategy = null;
        if ($payment_method_string === 'Credit Card') {
            $payment_strategy = new CreditCardStrategy();
        } elseif ($payment_method_string === 'PayPal') {
            $payment_strategy = new PayPalStrategy();
        }

        if ($payment_strategy) {
            try {
                $checkout = new CheckoutFacade($db, $user_id);
                $order_id = $checkout->placeOrder($shipping_details, $payment_strategy);
                
                header("Location: my-orders.php?order_success=true&order_id=" . $order_id);
                exit();
            } catch (Exception $e) {
                $error = "An error occurred: " . $e->getMessage();
            }
        } else {
            $error = "Invalid payment method selected.";
        }
    }
}
?>























<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Second-Hand Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.php" class="logo">🛍️ SecondHand Shop</a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="cart.php">Back to Cart</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="card">
            <h1>Checkout</h1>
        </div>
        
        <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            <div class="card">
                <form method="POST">
                    <h2>Shipping Details</h2>
                    <div class="form-group"><label for="shipping_name">Full Name *</label><input type="text" id="shipping_name" name="shipping_name" required></div>
                    <div class="form-group"><label for="shipping_address">Address *</label><textarea id="shipping_address" name="shipping_address" rows="3" required></textarea></div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group"><label for="shipping_city">City *</label><input type="text" id="shipping_city" name="shipping_city" required></div>
                        <div class="form-group"><label for="shipping_postal_code">Postal Code *</label><input type="text" id="shipping_postal_code" name="shipping_postal_code" required></div>
                    </div>

                    <h2 style="margin-top: 2rem;">Payment Method</h2>
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 1rem; border: 1px solid #eee; border-radius: 5px;"><input type="radio" name="payment_method" value="Credit Card" required>Credit Card (mock)</label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 1rem; border: 1px solid #eee; border-radius: 5px;"><input type="radio" name="payment_method" value="PayPal" required>PayPal (mock)</label>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Place Order</button>
                </form>
            </div>

            <div class="card">
                <h2>Order Summary</h2>
                <table class="table">
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?> (x<?php echo $item['quantity']; ?>)</td>
                                <td style="text-align: right;">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr style="font-weight: bold; border-top: 2px solid #333;">
                            <td>Total</td>
                            <td style="text-align: right;">$<?php echo number_format($subtotal, 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>