<?php
require_once 'PaymentStrategy.php';

class CreditCardStrategy implements PaymentStrategy {
    public function pay(float $amount): array {

        echo "Processing credit card payment of $ {$amount}... (simulated)\n";
        
        return [
            'status' => 'completed',
            'transaction_id' => 'cc_' . uniqid()
        ];
    }
}