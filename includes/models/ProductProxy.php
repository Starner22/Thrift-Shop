<?php
require_once 'Product.php';

class ProductProxy {
    private $product; 
    private $user_id;   

    public function __construct(Product $product, $user_id) {
        $this->product = $product;
        $this->user_id = $user_id;
    }
    
    public static function createFromId($db, $productID, $user_id) {
        $product = Product::findById($db, $productID);
        if ($product) {
            return new self($product, $user_id);
        }
        return null;
    }

    public function save() {
        if ($this->product->sellerID !== $this->user_id) {
            throw new Exception("You do not have permission to edit this product.");
        }
        
        return $this->product->save();
    }

    public function __get($name) {
        return $this->product->$name;
    }

    public function __set($name, $value) {
        $this->product->$name = $value;
    }
}