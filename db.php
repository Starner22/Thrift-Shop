<?php
$servername = "localhost";    // or your database host
$username = "root";           // default for XAMPP
$password = "";               // default for XAMPP (no password)
$dbname = "my_shop"; // replace with your actual DB name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "✅ Database connected successfully!";
}
?>