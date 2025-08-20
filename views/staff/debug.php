<?php
// Simple debug test for staff dashboard
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Dashboard Debug Test</h1>";

try {
    echo "<p>Testing file inclusions...</p>";
    
    // Test config
    require_once dirname(__DIR__, 2) . '/config/config.php';
    echo "<p>✓ Config loaded successfully</p>";
    echo "<p>ASSETS_URL: " . (defined('ASSETS_URL') ? ASSETS_URL : 'NOT DEFINED') . "</p>";
    
    // Test database
    require_once dirname(__DIR__, 2) . '/config/database.php';
    $db = Database::getInstance();
    echo "<p>✓ Database connection established</p>";
    
    // Test models
    require_once dirname(__DIR__, 2) . '/models/Order.php';
    require_once dirname(__DIR__, 2) . '/models/Reservation.php';
    echo "<p>✓ Models loaded successfully</p>";
    
    // Test functions
    require_once dirname(__DIR__, 2) . '/includes/functions.php';
    echo "<p>✓ Functions loaded successfully</p>";
    
    // Test model instantiation
    $orderModel = new Order();
    $reservationModel = new Reservation();
    echo "<p>✓ Models instantiated successfully</p>";
    
    // Test method calls
    $todayOrders = $orderModel->getTodayOrders();
    echo "<p>✓ getTodayOrders() works - Found " . count($todayOrders) . " orders</p>";
    
    $pendingOrders = $orderModel->getPendingOrders();
    echo "<p>✓ getPendingOrders() works - Found " . count($pendingOrders) . " orders</p>";
    
    $activeOrders = $orderModel->getActiveOrders();
    echo "<p>✓ getActiveOrders() works - Found " . count($activeOrders) . " orders</p>";
    
    $todayReservations = $reservationModel->getTodayReservations();
    echo "<p>✓ getTodayReservations() works - Found " . count($todayReservations) . " reservations</p>";
    
    echo "<h2>All tests passed! The dashboard should work.</h2>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
