<?php
session_start();
include 'db.php'; // this should set up $conn

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {
        die("Email and password are required.");
    }

    // Check if email exists
    $sql = "SELECT u.User_ID, u.Username, u.Email, u.Password
            FROM user AS u
            WHERE u.Email = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Check password
        if (password_verify($password, $user['Password'])) {
            // Login success
            $_SESSION["UserID"] = $user["UserID"];
            $_SESSION["Username"] = $user["Username"];

            // Redirect to home or dashboard
            header("Location: ../profile.html"); // Or dashboard.php
            exit();
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No user found with that email.";
    }
} else {
    echo "Invalid request.";
}
?>
