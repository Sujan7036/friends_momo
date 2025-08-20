<?php
/**
 * Direct Reservations Test
 * Friends and Momos Restaurant Management System
 */

echo "Testing Reservations directly...\n\n";

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/models/BaseModel.php';
require_once dirname(__DIR__) . '/models/Reservation.php';

try {
    $reservationModel = new Reservation();
    
    echo "✅ Reservation model created successfully\n";
    
    // Test methods
    if (method_exists($reservationModel, 'getReservationsWithCustomers')) {
        echo "✅ getReservationsWithCustomers method exists\n";
        
        $reservations = $reservationModel->getReservationsWithCustomers();
        echo "✅ Found " . count($reservations) . " reservations\n";
    } else {
        echo "❌ getReservationsWithCustomers method NOT found\n";
        
        // Show all available methods
        $methods = get_class_methods($reservationModel);
        echo "Available methods: " . implode(', ', $methods) . "\n";
    }
    
    if (method_exists($reservationModel, 'getReservationStats')) {
        echo "✅ getReservationStats method exists\n";
        
        $stats = $reservationModel->getReservationStats();
        echo "✅ Stats: " . json_encode($stats) . "\n";
    } else {
        echo "❌ getReservationStats method NOT found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
?>
