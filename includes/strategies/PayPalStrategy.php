<?php
require_once 'PaymentStrategy.php';

class PayPalStrategy implements PaymentStrategy {
    public function pay(float $amount): array {

        echo "Processing PayPal payment of $ {$amount}... (simulated)\n";
        
        return [
            'status' => 'completed',
            'transaction_id' => 'pp_' . uniqid()
        ];
    }
}