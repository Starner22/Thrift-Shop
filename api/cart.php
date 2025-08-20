<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../includes/auth.php';

$db = (new Database())->getConnection();
$data = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $data['action'] ?? $_REQUEST['action'] ?? '';

if (in_array($action, ['add', 'update', 'remove', 'clear'])) {
    requireAuth('Buyer');
}
$user_id = getUserId();

function getOrCreateUserCartId($db, $user_id) {
    $stmt = $db->prepare("SELECT cartID FROM cart WHERE buyerID = ?");
    $stmt->execute([$user_id]);
    if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
        return $result['cartID'];
    } else {
        $db->prepare("INSERT INTO cart (buyerID) VALUES (?)")->execute([$user_id]);
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
            $quantity_to_add = (int)($data['quantity'] ?? 1);
            
            if ($product_id <= 0 || $quantity_to_add <= 0) {
                throw new Exception('Invalid product or quantity.');
            }

            $stock_stmt = $db->prepare("SELECT quantity FROM product WHERE productID = ?");
            $stock_stmt->execute([$product_id]);
            $available_stock = (int)$stock_stmt->fetchColumn();

            if ($available_stock < $quantity_to_add) {
                throw new Exception('Not enough items in stock to add.');
            }
            
            $cart_id = getOrCreateUserCartId($db, $user_id);
            
            $existing_query = "SELECT cartItemID, quantity FROM cartitem WHERE cartID = ? AND productID = ?";
            $stmt = $db->prepare($existing_query);
            $stmt->execute([$cart_id, $product_id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            $current_cart_qty = $item ? $item['quantity'] : 0;
            if (($current_cart_qty + $quantity_to_add) > $available_stock) {
                 throw new Exception('Cannot add more items than available in stock.');
            }
            
            if ($item) {
                $new_quantity = $item['quantity'] + $quantity_to_add;
                $update_query = "UPDATE cartitem SET quantity = ? WHERE cartItemID = ?";
                $db->prepare($update_query)->execute([$new_quantity, $item['cartItemID']]);
            } else {
                $insert_query = "INSERT INTO cartitem (cartID, productID, quantity) VALUES (?, ?, ?)";
                $db->prepare($insert_query)->execute([$cart_id, $product_id, $quantity_to_add]);
            }
            echo json_encode(['success' => true, 'message' => 'Item added to cart.']);
            break;

        case 'update':
        case 'remove':
             $cart_item_id = (int)($_POST['cartItemId'] ?? 0);
             if ($cart_item_id <= 0) {
                 throw new Exception('Invalid cart item ID.');
             }

             $verify_query = "SELECT c.buyerID FROM cartitem ci JOIN cart c ON ci.cartID = c.cartID WHERE ci.cartItemID = ?";
             $stmt = $db->prepare($verify_query);
             $stmt->execute([$cart_item_id]);
             $owner = $stmt->fetch();
             if (!$owner || $owner['buyerID'] != $user_id) {
                 throw new Exception('Permission denied.');
             }

             if ($action === 'update') {
                 $quantity = (int)($_POST['quantity'] ?? 1);
                 $query = "UPDATE cartitem SET quantity = ? WHERE cartItemID = ?";
                 $db->prepare($query)->execute([$quantity, $cart_item_id]);
             } else {
                 $query = "DELETE FROM cartitem WHERE cartItemID = ?";
                 $db->prepare($query)->execute([$cart_item_id]);
             }
             echo json_encode(['success' => true, 'message' => 'Cart updated.']);
             break;

        case 'count':
            $count = 0;
            if ($user_id) {
                 $cart_id = getOrCreateUserCartId($db, $user_id);
                 $stmt = $db->prepare("SELECT SUM(quantity) as total FROM cartitem WHERE cartID = ?");
                 $stmt->execute([$cart_id]);
                 $count = (int)($stmt->fetchColumn() ?? 0);
            }
            echo json_encode(['count' => $count]);
            break;
            
        default:
            throw new Exception('Invalid action.');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}