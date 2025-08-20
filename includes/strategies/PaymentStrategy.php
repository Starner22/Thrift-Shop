<?php
interface PaymentStrategy {
    /**
     * Processes the payment for a given amount.
     * @param float $amount The total amount to be paid.
     * @return array An array containing payment details like status and transaction ID.
     */
    public function pay(float $amount): array;
}