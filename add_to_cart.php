<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

requireAuth('Buyer');

$database = Database::getInstance();
$db = $database->getConnection();
$user_id = getUserId();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['productID'])) {
    $product_id = (int) $_POST['productID'];

    // Check if cart exists for this user
    $stmt = $db->prepare("SELECT cartID FROM cart WHERE buyerID = ?");
    $stmt->execute([$user_id]);
    $cart = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cart) {
        // Create a new cart
        $stmt = $db->prepare("INSERT INTO cart (buyerID) VALUES (?)");
        $stmt->execute([$user_id]);
        $cart_id = $db->lastInsertId();
    } else {
        $cart_id = $cart['cartID'];
    }

    // Check if item is already in cart
    $stmt = $db->prepare("SELECT * FROM cartitem WHERE cartID = ? AND productID = ?");
    $stmt->execute([$cart_id, $product_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        // Update quantity
        $stmt = $db->prepare("UPDATE cartitem SET quantity = quantity + 1 WHERE cartID = ? AND productID = ?");
        $stmt->execute([$cart_id, $product_id]);
    } else {
        // Insert new item
        $stmt = $db->prepare("INSERT INTO cartitem (cartID, productID, quantity) VALUES (?, ?, 1)");
        $stmt->execute([$cart_id, $product_id]);
    }

    echo json_encode(["success" => true, "message" => "Added to cart"]);
    exit;
}

echo json_encode(["success" => false, "message" => "Invalid request"]);
exit;
