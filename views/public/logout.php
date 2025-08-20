<?php
/**
 * Logout Handler - Simple Version
 * Friends and Momos Restaurant Management System
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include config for BASE_URL
require_once dirname(__DIR__, 2) . '/config/config.php';

// Destroy session
session_destroy();

// Clear session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Redirect to home page
header('Location: ' . BASE_URL . '/index.html');
exit;
?>
