<?php
class Database {
    private $host = "localhost";
    private $db_name = "second_hand_shop";
    private $username = "root";
    private $password = "";

    private static $connection = null;

    public function __construct() {}


    public function getConnection() {
        if (self::$connection === null) {
            try {
                self::$connection = new PDO(
                    "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                    $this->username,
                    $this->password
                );
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch(PDOException $exception) {
                error_log("Connection error: " . $exception->getMessage());
                die("Could not connect to the database. Please try again later.");
            }
        }

        return self::$connection;
    }
}
?>