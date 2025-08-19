<?php
require_once __DIR__ . '/../models/User.php';

interface UserFactory {
    public static function create($db, $name, $email, $password);
}