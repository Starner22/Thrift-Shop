<?php
require_once 'PaymentStrategy.php';

class PayPalStrategy implements PaymentStrategy {
    public function pay(float $amount): array {
        // In a real application, this would redirect to PayPal or use their API.
        // For now, we'll simulate a successful payment.
        echo "Processing PayPal payment of $ {$amount}... (simulated)\n";
        
        return [
            'status' => 'completed',
            'transaction_id' => 'pp_' . uniqid()
        ];
    }
}