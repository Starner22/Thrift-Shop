<?php
include 'db.php'; // make sure this file has your DB connection ($conn)
echo "Register PHP reached!";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username   = trim($_POST["username"]);
    $firstName  = trim($_POST["first_name"]);
    $lastName   = trim($_POST["last_name"]);
    $email      = trim($_POST["email"]);
    $password   = $_POST["password"];

    // Basic validation
    if (empty($username) || empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        die("All fields are required.");
    }

    // Check if username or email already exists
    $checkQuery = "SELECT * FROM user WHERE Username = ? OR Email = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        die("Username or Email already taken.");
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert into database
    $insertQuery = "INSERT INTO user (Username, First_Name, Last_Name, Email, Password) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("sssss", $username, $firstName, $lastName, $email, $hashedPassword);

    if ($stmt->execute()) {
        // Registration successful
        header("Location: /Thrift-Shop-FrontEnd/login.html"); // Or wherever you want to go next
        exit();
    } else {
        die("Registration failed: " . $stmt->error);
    }
} else {
    die("Invalid request.");
}
?>
