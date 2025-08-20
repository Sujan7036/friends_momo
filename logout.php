<?php
/**
 * Logout Page
 * Friends and Momos Restaurant Management System
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destroy session
session_destroy();

// Redirect to login page
header('Location: login.php?message=logout_success');
exit();
?>
