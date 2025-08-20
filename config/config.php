<?php
/**
 * Application Configuration
 * Friends and Momos Restaurant Management System
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Application settings
define('APP_NAME', 'Friends and Momos');
define('APP_VERSION', '1.0.0');
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost/friends_momo');
define('APP_DEBUG', $_ENV['APP_DEBUG'] ?? true);

// File paths
define('ROOT_PATH', dirname(__DIR__));
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('UPLOADS_PATH', ASSETS_PATH . '/uploads');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('INCLUDES_PATH', ROOT_PATH . '/includes');

// URLs
define('BASE_URL', rtrim(APP_URL, '/'));
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOADS_URL', ASSETS_URL . '/uploads');

// Database settings (loaded from database.php)
require_once ROOT_PATH . '/config/database.php';

// Security settings
define('PASSWORD_MIN_LENGTH', 8);
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// File upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Email settings
define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? 'localhost');
define('SMTP_PORT', $_ENV['SMTP_PORT'] ?? 587);
define('SMTP_USERNAME', $_ENV['SMTP_USERNAME'] ?? '');
define('SMTP_PASSWORD', $_ENV['SMTP_PASSWORD'] ?? '');
define('FROM_EMAIL', $_ENV['FROM_EMAIL'] ?? 'noreply@friendsandmomos.com');
define('FROM_NAME', $_ENV['FROM_NAME'] ?? 'Friends and Momos');

// Payment settings
define('PAYMENT_CURRENCY', 'AUD');
define('TAX_RATE', 0.10); // 10% GST

// Restaurant settings
define('RESTAURANT_NAME', 'Friends and Momos');
define('RESTAURANT_ADDRESS', 'Gungahlin, ACT, Australia');
define('RESTAURANT_PHONE', '+61 2 1234 5678');
define('RESTAURANT_EMAIL', 'info@friendsandmomos.com');

// Business hours
define('BUSINESS_HOURS', [
    'monday' => ['open' => '11:00', 'close' => '22:00'],
    'tuesday' => ['open' => '11:00', 'close' => '22:00'],
    'wednesday' => ['open' => '11:00', 'close' => '22:00'],
    'thursday' => ['open' => '11:00', 'close' => '22:00'],
    'friday' => ['open' => '11:00', 'close' => '23:00'],
    'saturday' => ['open' => '11:00', 'close' => '23:00'],
    'sunday' => ['open' => '11:00', 'close' => '22:00']
]);

// Table settings
define('MAX_GUESTS_PER_TABLE', 8);
define('RESERVATION_ADVANCE_DAYS', 30);

// Timezone
date_default_timezone_set('Australia/Sydney');

// Auto-load classes
spl_autoload_register(function ($className) {
    $directories = [
        ROOT_PATH . '/models/',
        ROOT_PATH . '/controllers/',
        ROOT_PATH . '/includes/'
    ];
    
    foreach ($directories as $directory) {
        $file = $directory . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            break;
        }
    }
});

// Include common functions
require_once INCLUDES_PATH . '/functions.php';

// CSRF Token generation
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
