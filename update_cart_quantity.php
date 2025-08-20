<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

requireAuth('Buyer');

$database = Database::getInstance();
$db = $database->getConnection();
$user_id = getUserId();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cartItemID'], $_POST['quantity'])) {
    $cart_item_id = (int) $_POST['cartItemID'];
    $quantity = max(1, (int) $_POST['quantity']); // prevent 0 or negative

    // Update only if belongs to this user
    $query = "UPDATE cartitem ci
              JOIN cart c ON ci.cartID = c.cartID
              SET ci.quantity = ?
              WHERE ci.cartItemID = ? AND c.buyerID = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$quantity, $cart_item_id, $user_id]);

    echo json_encode(["success" => true, "message" => "Quantity updated"]);
    exit;
}

echo json_encode(["success" => false, "message" => "Invalid request"]);
exit;
