<?php
require_once __DIR__ . '/AbstractSubject.php';

class OrderSubject extends AbstractSubject {
    private PDO $db;
    private int $orderID;

    public function __construct(PDO $db, int $orderID) {
        $this->db = $db;
        $this->orderID = $orderID;
    }

    public function updateStatus(string $newStatus): void {
        $this->db->prepare("UPDATE `order` SET orderStatus=:s WHERE orderID=:id")
                 ->execute([':s'=>$newStatus, ':id'=>$this->orderID]);

        $event = [
            'title' => "Order Status Updated",
            'message' => "Order #{$this->orderID} status is now {$newStatus}.",
            'eventType' => 'order_status',
            'refType' => 'order',
            'refID' => $this->orderID
        ];
        $this->notify($event);
    }
}
