<?php
require_once 'PaymentStrategy.php';

class CreditCardStrategy implements PaymentStrategy {
    public function pay(float $amount): array {
        // In a real application, this would connect to a payment gateway like Stripe or Braintree.
        // For now, we'll just simulate a successful payment.
        echo "Processing credit card payment of $ {$amount}... (simulated)\n";
        
        return [
            'status' => 'completed',
            'transaction_id' => 'cc_' . uniqid()
        ];
    }
}