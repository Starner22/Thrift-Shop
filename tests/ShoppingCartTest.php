<?php
use PHPUnit\Framework\TestCase;

require_once 'ShoppingCart.php'; // Include renamed class

class ShoppingCartTest extends TestCase {

    public function testAddItem() {
        $cart = new ShoppingCart();
        $cart->addItem(new CartItem("Item A", 100, 2));
        $cart->addItem(new CartItem("Item B", 50, 1));

        $items = $cart->getItems();
        $this->assertCount(2, $items);
        $this->assertEquals(2, $items[0]->quantity);
    }

    public function testRemoveItem() {
        $cart = new ShoppingCart();
        $cart->addItem(new CartItem("Item A", 100, 2));
        $cart->addItem(new CartItem("Item B", 50, 1));

        $cart->removeItem("Item A");
        $items = $cart->getItems();
        $this->assertCount(1, $items);
        $this->assertEquals("Item B", $items[0]->name);
    }

    public function testUpdateQuantity() {
        $cart = new ShoppingCart();
        $cart->addItem(new CartItem("Item A", 100, 2));

        $cart->updateQuantity("Item A", 5);
        $items = $cart->getItems();
        $this->assertEquals(5, $items[0]->quantity);
    }

    public function testCalculateTotal() {
        $cart = new ShoppingCart();
        $cart->addItem(new CartItem("Item A", 100, 2));
        $cart->addItem(new CartItem("Item B", 50, 1));

        $this->assertEquals(250, $cart->calculateTotal());
        $this->assertEquals(225, $cart->calculateTotal(10)); // 10% discount
    }
}
