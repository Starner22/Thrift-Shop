<?php
class CartItem {
    public $name;
    public $price;
    public $quantity;

    public function __construct($name, $price, $quantity) {
        $this->name = $name;
        $this->price = $price;
        $this->quantity = $quantity;
    }
}

class ShoppingCart {
    private $items = [];

    public function addItem(CartItem $item) {
        // If item exists, increase quantity
        foreach ($this->items as $i) {
            if ($i->name === $item->name) {
                $i->quantity += $item->quantity;
                return;
            }
        }
        $this->items[] = $item;
    }

    public function removeItem($itemName) {
        foreach ($this->items as $index => $i) {
            if ($i->name === $itemName) {
                array_splice($this->items, $index, 1);
                return true;
            }
        }
        return false;
    }

    public function updateQuantity($itemName, $quantity) {
        foreach ($this->items as $i) {
            if ($i->name === $itemName) {
                $i->quantity = $quantity;
                return true;
            }
        }
        return false;
    }

    public function calculateTotal($discount = 0.0) {
        $total = 0;
        foreach ($this->items as $i) {
            $total += $i->price * $i->quantity;
        }
        $discountAmount = $total * $discount / 100;
        return $total - $discountAmount;
    }

    public function getItems() {
        return $this->items;
    }
}
