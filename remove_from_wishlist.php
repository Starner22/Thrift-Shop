<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

requireAuth('Buyer');

$database = Database::getInstance();
$db = $database->getConnection();
$user_id = getUserId();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['productID'])) {
    $product_id = (int) $_POST['productID'];

    // Get wishlist id for user
    $stmt = $db->prepare("SELECT wishlistID FROM wishlist WHERE buyerID = ?");
    $stmt->execute([$user_id]);
    $wishlist = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($wishlist) {
        $wishlist_id = $wishlist['wishlistID'];

        // Delete the product from wishlist
        $stmt = $db->prepare("DELETE FROM wishlistitem WHERE wishlistID = ? AND productID = ?");
        $stmt->execute([$wishlist_id, $product_id]);

        echo json_encode(["success" => true, "message" => "Removed from wishlist"]);
        exit;
    }
}

echo json_encode(["success" => false, "message" => "Invalid request"]);
exit;
