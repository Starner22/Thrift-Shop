<?php
session_start();
session_destroy();

// Use the function from auth.php if available, otherwise define a fallback.
if (function_exists('getBaseUrl')) {
    require_once 'includes/auth.php';
    $baseUrl = getBaseUrl();
} else {
    $baseUrl = '/Thrift-Shop-main'; // Fallback
}

header("Location: " . $baseUrl . "/index.php?message=logged_out");
exit();
?>