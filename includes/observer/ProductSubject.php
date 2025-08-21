<?php
require_once __DIR__ . '/AbstractSubject.php';

class ProductSubject extends AbstractSubject {
    private PDO $db;
    private int $productID;

    public function __construct(PDO $db, int $productID) {
        $this->db = $db;
        $this->productID = $productID;
    }

    public function bought(int $buyerID, int $sellerID): void {
        $event = [
            'title' => "Product Purchased",
            'message' => "Product #{$this->productID} has been purchased.",
            'eventType' => 'product_bought',
            'refType' => 'product',
            'refID' => $this->productID
        ];
        $this->notify($event);
    }

    public function approve(int $sellerID): void {
        $this->db->prepare("UPDATE product SET status='approved' WHERE productID=:id")
                 ->execute([':id'=>$this->productID]);

        $event = [
            'title' => "Product Approved",
            'message' => "Your product #{$this->productID} has been approved.",
            'eventType' => 'product_approved',
            'refType' => 'product',
            'refID' => $this->productID
        ];
        $this->notify($event);
    }

    public function reject(int $sellerID): void {
        $this->db->prepare("UPDATE product SET status='rejected' WHERE productID=:id")
                 ->execute([':id'=>$this->productID]);

        $event = [
            'title' => "Product Rejected",
            'message' => "Your product #{$this->productID} was rejected.",
            'eventType' => 'product_rejected',
            'refType' => 'product',
            'refID' => $this->productID
        ];
        $this->notify($event);
    }

    public function stockUpdate(int $newQty): void {
        $this->db->prepare("UPDATE product SET quantity=:q WHERE productID=:id")
                 ->execute([':q'=>$newQty, ':id'=>$this->productID]);

        $event = [
            'title' => $newQty > 0 ? "Product Restocked" : "Product Out of Stock",
            'message' => $newQty > 0
                ? "Product #{$this->productID} has been restocked."
                : "Product #{$this->productID} is out of stock.",
            'eventType' => 'inventory_update',
            'refType' => 'product',
            'refID' => $this->productID
        ];
        $this->notify($event);
    }
}
