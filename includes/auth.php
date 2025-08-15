<?php

session_start();
require_once __DIR__ . '/../config/database.php';

function getBaseUrl() {
    return '/shop';
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserRole() {
    return $_SESSION['user_role'] ?? null;
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function hasRole($required_roles) {
    if (!isLoggedIn()) return false;
    $user_role = getUserRole();
    if (is_array($required_roles)) {
        return in_array($user_role, $required_roles);
    }
    return $user_role === $required_roles;
}

function requireAuth($required_roles = null) {
    if (!isLoggedIn()) {
        header("Location: " . getBaseUrl() . "/login.php");
        exit();
    }
    if ($required_roles && !hasRole($required_roles)) {
        header("Location: " . getBaseUrl() . "/dashboard.php?error=unauthorized");
        exit();
    }
}

function loginUser($email, $password, $db) {
    $query = "SELECT userID, name, email, password, role FROM user WHERE email = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$email]);
    if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['userID'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            if ($user['role'] === 'Buyer') {
                createCartAndWishlist($user['userID'], $db);
            }
            return true;
        }
    }
    return false;
}

function registerUser($name, $email, $password, $role, $db) {
    $check_query = "SELECT userID FROM user WHERE email = ?";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->execute([$email]);
    if ($check_stmt->fetch()) {
        return false; 
    }
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $query = "INSERT INTO user (name, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    if ($stmt->execute([$name, $email, $hashed_password, $role])) {
        if ($role === 'Buyer') {
            $user_id = $db->lastInsertId();
            createCartAndWishlist($user_id, $db);
        }
        return true;
    }
    return false;
}

function createCartAndWishlist($buyerID, $db) {
    try {
        $stmt = $db->prepare("SELECT cartID FROM cart WHERE buyerID = ?");
        $stmt->execute([$buyerID]);
        if (!$stmt->fetch()) {
            $stmt = $db->prepare("INSERT INTO cart (buyerID) VALUES (?)");
            $stmt->execute([$buyerID]);
        }
        $stmt = $db->prepare("SELECT wishlistID FROM wishlist WHERE buyerID = ?");
        $stmt->execute([$buyerID]);
        if (!$stmt->fetch()) {
            $stmt = $db->prepare("INSERT INTO wishlist (buyerID) VALUES (?)");
            $stmt->execute([$buyerID]);
        }
    } catch (PDOException $e) {
        error_log("Cart/Wishlist creation error: " . $e->getMessage());
    }
}
?>