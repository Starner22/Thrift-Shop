<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../includes/auth.php';

$db = (new Database())->getConnection();

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $data['action'] ?? $_REQUEST['action'] ?? '';

if (in_array($action, ['add', 'remove'])) {
    requireAuth('Buyer');
}
$user_id = getUserId();

function getOrCreateUserWishlistId($db, $user_id) {
    $stmt = $db->prepare("SELECT wishlistID FROM wishlist WHERE buyerID = ?");
    $stmt->execute([$user_id]);
    if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
        return $result['wishlistID'];
    } else {
        $db->prepare("INSERT INTO wishlist (buyerID) VALUES (?)")->execute([$user_id]);
        return $db->lastInsertId();
    }
}

try {
    if (empty($action)) {
        throw new Exception('Invalid action.');
    }

    switch ($action) {
        case 'add':
            $product_id = (int)($data['product_id'] ?? 0);
            if ($product_id <= 0) {
                throw new Exception('Invalid product ID.');
            }
            
            $wishlist_id = getOrCreateUserWishlistId($db, $user_id);

            $stmt = $db->prepare("SELECT wishlistItemID FROM wishlistitem WHERE wishlistID = ? AND productID = ?");
            $stmt->execute([$wishlist_id, $product_id]);

            if (!$stmt->fetch()) {
                 $db->prepare("INSERT INTO wishlistitem (wishlistID, productID) VALUES (?, ?)")
                    ->execute([$wishlist_id, $product_id]);
            }
            echo json_encode(['success' => true, 'message' => 'Added to wishlist.']);
            break;

        case 'remove':
            $product_id = (int)($_POST['productId'] ?? 0);
            if ($product_id <= 0) {
                throw new Exception('Invalid product ID.');
            }

            $wishlist_id = getOrCreateUserWishlistId($db, $user_id);
            $db->prepare("DELETE FROM wishlistitem WHERE wishlistID = ? AND productID = ?")
               ->execute([$wishlist_id, $product_id]);
            echo json_encode(['success' => true, 'message' => 'Removed from wishlist.']);
            break;
            
        default:
            throw new Exception('Invalid action.');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}