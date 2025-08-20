<?php
require_once __DIR__ . '/User.php';

class Seller extends User {
    public function __construct($db, $name, $email, $password) {
        parent::__construct($db);
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->role = 'Seller';
    }
}
