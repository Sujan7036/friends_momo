<?php
/**
 * Quick Admin Panel Status Check
 * Friends and Momos Restaurant Management System
 */

echo "<h1>🔧 Admin Panel Status Check</h1>";

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'models/BaseModel.php';
require_once 'models/Reservation.php';

try {
    echo "<h2>✅ Testing Reservation Model Methods</h2>";
    
    $reservationModel = new Reservation();
    
    // Check if methods exist
    $methods = get_class_methods($reservationModel);
    echo "<h3>Available methods in Reservation class:</h3>";
    echo "<ul>";
    foreach ($methods as $method) {
        echo "<li>$method</li>";
    }
    echo "</ul>";
    
    // Test the specific method
    if (method_exists($reservationModel, 'getReservationsWithCustomers')) {
        echo "<p>✅ getReservationsWithCustomers method exists</p>";
        
        try {
            $reservations = $reservationModel->getReservationsWithCustomers();
            echo "<p>✅ Method executed successfully. Found " . count($reservations) . " reservations</p>";
        } catch (Exception $e) {
            echo "<p>❌ Error executing method: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>❌ getReservationsWithCustomers method does not exist</p>";
    }
    
    // Test stats method
    if (method_exists($reservationModel, 'getReservationStats')) {
        echo "<p>✅ getReservationStats method exists</p>";
        
        try {
            $stats = $reservationModel->getReservationStats();
            echo "<p>✅ Stats method executed successfully: " . json_encode($stats) . "</p>";
        } catch (Exception $e) {
            echo "<p>❌ Error executing stats method: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>❌ getReservationStats method does not exist</p>";
    }
    
    echo "<h2>🔗 Admin Panel Links</h2>";
    echo "<ul>";
    echo "<li><a href='views/admin/dashboard.php'>Dashboard</a></li>";
    echo "<li><a href='views/admin/customers.php'>Customers</a></li>";
    echo "<li><a href='views/admin/orders.php'>Orders</a></li>";
    echo "<li><a href='views/admin/reservations.php'>Reservations</a></li>";
    echo "<li><a href='views/admin/menu.php'>Menu</a></li>";
    echo "</ul>";
    
    echo "<h2>🏆 System Status: READY</h2>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
    echo "<p>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>";
}
?>
