<?php
class CheckoutFacade {
    protected $db;
    protected $user_id;

    public function __construct($db, $user_id) {
        $this->db = $db;
        $this->user_id = $user_id;
    }


    public function placeOrder(array $shipping_details, PaymentStrategy $payment_strategy) {
        try {
            $this->db->beginTransaction();

            $cart_query = "SELECT p.productID, p.name, p.price, ci.quantity, p.quantity as stock_available
                           FROM cartitem ci
                           JOIN product p ON ci.productID = p.productID
                           JOIN cart c ON ci.cartID = c.cartID
                           WHERE c.buyerID = ?";
            $cart_stmt = $this->db->prepare($cart_query);
            $cart_stmt->execute([$this->user_id]);
            $cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($cart_items)) {
                throw new Exception("Your cart is empty.");
            }

            $subtotal = 0;
            foreach ($cart_items as $item) {
                if ($item['quantity'] > $item['stock_available']) {
                    throw new Exception("Not enough stock for '".htmlspecialchars($item['name'])."'.");
                }
                $subtotal += $item['price'] * $item['quantity'];
            }
            
            $payment_result = $payment_strategy->pay($subtotal);
            if ($payment_result['status'] !== 'completed') {
                throw new Exception("Payment failed or was cancelled.");
            }

            $order_query = "INSERT INTO `order` (buyerID, totalPrice, orderStatus, shipping_name, shipping_address, shipping_city, shipping_postal_code) 
                            VALUES (?, ?, 'Processing', ?, ?, ?, ?)";
            $order_stmt = $this->db->prepare($order_query);
            $order_stmt->execute([
                $this->user_id, 
                $subtotal, 
                $shipping_details['name'], 
                $shipping_details['address'], 
                $shipping_details['city'], 
                $shipping_details['postal_code']
            ]);
            $order_id = $this->db->lastInsertId();

            $payment_method_name = get_class($payment_strategy); 
            $payment_query = "INSERT INTO payments (orderID, amount, payment_method, transaction_id, payment_status) VALUES (?, ?, ?, ?, ?)";
            $payment_stmt = $this->db->prepare($payment_query);
            $payment_stmt->execute([$order_id, $subtotal, $payment_method_name, $payment_result['transaction_id'], $payment_result['status']]);
            $order_item_query = "INSERT INTO orderitem (orderID, productID, quantity, price_at_purchase) VALUES (?, ?, ?, ?)";
            $order_item_stmt = $this->db->prepare($order_item_query);
            $decrement_stock_query = "UPDATE product SET quantity = quantity - ? WHERE productID = ?";
            $decrement_stock_stmt = $this->db->prepare($decrement_stock_query);

            foreach ($cart_items as $item) {
                $order_item_stmt->execute([$order_id, $item['productID'], $item['quantity'], $item['price']]);
                $decrement_stock_stmt->execute([$item['quantity'], $item['productID']]);
            }

     
            
            $cart_id_query = "SELECT cartID FROM cart WHERE buyerID = ?";
            $cart_id_stmt = $this->db->prepare($cart_id_query);
            $cart_id_stmt->execute([$this->user_id]);
            if ($cart_id = $cart_id_stmt->fetchColumn()) {
                $this->db->prepare("DELETE FROM cartitem WHERE cartID = ?")->execute([$cart_id]);
            }

            $this->db->commit();
            
            return $order_id;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}