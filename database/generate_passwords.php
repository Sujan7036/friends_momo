<?php
// Generate proper password hashes for the default users
$passwords = [
    'admin123' => password_hash('admin123', PASSWORD_DEFAULT),
    'staff123' => password_hash('staff123', PASSWORD_DEFAULT),
    'customer123' => password_hash('customer123', PASSWORD_DEFAULT)
];

echo "-- Generated Password Hashes\n";
echo "-- Use these in your database:\n\n";

foreach ($passwords as $plain => $hash) {
    echo "-- Password: {$plain}\n";
    echo "-- Hash: {$hash}\n\n";
}

// Update SQL for existing users
echo "-- SQL to update existing users:\n";
echo "UPDATE users SET password = '{$passwords['admin123']}', password_hash = '{$passwords['admin123']}' WHERE email = 'admin@friendsandmomos.com';\n";
echo "UPDATE users SET password = '{$passwords['staff123']}', password_hash = '{$passwords['staff123']}' WHERE email = 'kitchen@friendsandmomos.com';\n";
echo "UPDATE users SET password = '{$passwords['customer123']}', password_hash = '{$passwords['customer123']}' WHERE email = 'john.doe@example.com';\n";
?>
