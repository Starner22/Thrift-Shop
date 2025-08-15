<?php
class Product {
    protected $db;
    public $productID;
    public $name;
    public $description;
    public $price;
    public $condition;
    public $quantity;
    public $categoryID;
    public $image_path;
    public $sellerID;
    public $status;

    public function __construct($db) {
        $this->db = $db;
    }

    public static function findById($db, $productID) {
        $stmt = $db->prepare("SELECT * FROM product WHERE productID = ?");
        $stmt->execute([$productID]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            $product = new self($db);
            foreach ($data as $key => $value) {
                if (property_exists($product, $key)) {
                    $product->$key = $value;
                }
            }
            return $product;
        }
        return null;
    }

    public function save() {
        $query = "UPDATE product SET 
            name = :name, description = :description, price = :price, `condition` = :condition, 
            quantity = :quantity, categoryID = :categoryID, image_path = :image_path, status = :status
            WHERE productID = :productID AND sellerID = :sellerID";
            
        $stmt = $this->db->prepare($query);
        
        return $stmt->execute([
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'condition' => $this->condition,
            'quantity' => $this->quantity,
            'categoryID' => $this->categoryID,
            'image_path' => $this->image_path,
            'status' => 'pending', 
            'productID' => $this->productID,
            'sellerID' => $this->sellerID
        ]);
    }
}