
<?php
interface Observer {
    public function update(array $event): void;
}
?>