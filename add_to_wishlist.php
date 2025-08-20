<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

requireAuth('Buyer');

$database = Database::getInstance();
$db = $database->getConnection();
$user_id = getUserId();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['productID'])) {
    $product_id = (int) $_POST['productID'];

    // Check if user already has a wishlist
    $stmt = $db->prepare("SELECT wishlistID FROM wishlist WHERE buyerID = ?");
    $stmt->execute([$user_id]);
    $wishlist = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$wishlist) {
        // Create a new wishlist for this buyer
        $stmt = $db->prepare("INSERT INTO wishlist (buyerID) VALUES (?)");
        $stmt->execute([$user_id]);
        $wishlist_id = $db->lastInsertId();
    } else {
        $wishlist_id = $wishlist['wishlistID'];
    }

    // Prevent duplicates
    $stmt = $db->prepare("SELECT * FROM wishlistitem WHERE wishlistID = ? AND productID = ?");
    $stmt->execute([$wishlist_id, $product_id]);

    if (!$stmt->fetch()) {
        $stmt = $db->prepare("INSERT INTO wishlistitem (wishlistID, productID) VALUES (?, ?)");
        $stmt->execute([$wishlist_id, $product_id]);
    }

    echo json_encode(["success" => true, "message" => "Added to wishlist"]);
    exit;
}

echo json_encode(["success" => false, "message" => "Invalid request"]);
exit;
