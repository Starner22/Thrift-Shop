<?php
require_once 'UserFactory.php';
require_once __DIR__ . '/../models/buyer.php';


class BuyerFactory implements UserFactory {
    public static function create($db, $name, $email, $password) {
        return new Buyer($db, $name, $email, $password);
    }
}
