<?php
require_once 'UserFactory.php';

class BuyerFactory implements UserFactory {
    public static function create($db, $name, $email, $password) {
        $user = new User($db);
        $user->name = $name;
        $user->email = $email;
        $user->password = $password;
        $user->role = 'Buyer';
        return $user;
    }
}