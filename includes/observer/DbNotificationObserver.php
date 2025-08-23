<?php
require_once __DIR__ . '/Observer.php';

class DbNotificationObserver implements Observer {
    private PDO $db;
    private int $userId;

    public function __construct(PDO $db, int $userId) {
        $this->db = $db;
        $this->userId = $userId;
    }

    public function update(array $event): void {
        $stmt = $this->db->prepare("
            INSERT INTO notifications (userID, title, message, eventType, refType, refID)
            VALUES (:userID, :title, :message, :eventType, :refType, :refID)
        ");
        $stmt->execute([
            ':userID'    => $this->userId,
            ':title'     => $event['title'],
            ':message'   => $event['message'],
            ':eventType' => $event['eventType'],
            ':refType'   => $event['refType'] ?? null,
            ':refID'     => $event['refID'] ?? null
        ]);
    }
}
?>