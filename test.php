
<?php


require_once 'config/database.php';

// First call
$db1 = Database::getInstance();
$conn1 = $db1->getConnection();
if($db1!=NULL){
    echo" Success";
}
else{
$db2 = Database::getInstance();
$conn2 = $db2->getConnection();
echo"instance from db2";
}

echo "<h2>Singleton Test</h2>";

// Check object identity
if (spl_object_id($db1) === spl_object_id($db2)) {
    echo "✅ Database::getInstance() returned the SAME instance.<br>";
} else {
    echo "❌ Different instances created!<br>";
}

// Check PDO connection identity
if (spl_object_id($conn1) === spl_object_id($conn2)) {
    echo "✅ getConnection() returned the SAME PDO connection.<br>";
} else {
    echo "❌ Different PDO connections created!<br>";
}

// Debug output
echo "<pre>";
var_dump($db1);
var_dump($db2);
echo "</pre>";
