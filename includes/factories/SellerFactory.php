<?php
require_once 'UserFactory.php';
require_once __DIR__ . '/../models/Seller.php';

class SellerFactory implements UserFactory {
    public static function create($db, $name, $email, $password) {
        return new Seller($db, $name, $email, $password);
    }
}
