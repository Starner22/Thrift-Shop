<?php
session_start();
session_destroy();







if (function_exists('getBaseUrl')) {
    require_once 'includes/auth.php';
    $baseUrl = getBaseUrl();
} else {
    $baseUrl = '/shop';
}

header("Location: " . $baseUrl . "/index.php?message=logged_out");
exit();
?>