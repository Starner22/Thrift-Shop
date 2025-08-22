<?php
interface UserFactory {
    public static function create($db, $name, $email, $password);
}
