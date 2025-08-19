<?php
class User {
    protected $db;
    public $name;
    public $email;
    public $password;
    public $role;

    public function __construct($db) {
        $this->db = $db;
    }

    public function save() {
        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);
        $query = "INSERT INTO user (name, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$this->name, $this->email, $hashed_password, $this->role]);
    }

    public static function findByEmail($db, $email) {
        $stmt = $db->prepare("SELECT userID FROM user WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
}