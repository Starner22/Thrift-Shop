<?php
require_once __DIR__ . '/Subject.php';

abstract class AbstractSubject implements Subject {
    protected array $observers = [];

    public function attach(Observer $observer): void {
        $this->observers[] = $observer;
    }

    public function detach(Observer $observer): void {
        $this->observers = array_values(array_filter(
            $this->observers,
            fn($o) => $o !== $observer
        ));
    }

    public function notify(array $event): void {
        foreach ($this->observers as $observer) {
            $observer->update($event);
        }
    }
}
?>