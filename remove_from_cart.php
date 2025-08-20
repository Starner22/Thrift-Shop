<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

requireAuth('Buyer');

$database = Database::getInstance();
$db = $database->getConnection();
$user_id = getUserId();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cartItemID'])) {
    $cart_item_id = (int) $_POST['cartItemID'];

    // Delete only if it belongs to this user's cart
    $query = "DELETE ci FROM cartitem ci
              JOIN cart c ON ci.cartID = c.cartID
              WHERE ci.cartItemID = ? AND c.buyerID = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$cart_item_id, $user_id]);

    echo json_encode(["success" => true, "message" => "Removed from cart"]);
    exit;
}

echo json_encode(["success" => false, "message" => "Invalid request"]);
exit;
