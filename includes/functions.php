<?php
/**
 * Common Functions
 * Friends and Momos Restaurant Management System
 */

/**
 * Sanitize input data
 */
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email address
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (Australian format)
 */
function isValidPhone($phone) {
    $pattern = '/^(\+61|0)[2-9]\d{8}$/';
    return preg_match($pattern, $phone);
}

/**
 * Generate secure password hash
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redirect to a URL
 */
function redirect($url, $statusCode = 302) {
    header("Location: $url", true, $statusCode);
    exit();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Require login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect(BASE_URL . '/views/public/login.php');
    }
}

/**
 * Require admin access
 */
function requireAdmin() {
    if (!isAdmin()) {
        redirect(BASE_URL . '/views/public/index.php');
    }
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

/**
 * Format date
 */
function formatDate($date, $format = 'M j, Y') {
    return date($format, strtotime($date));
}

/**
 * Format datetime
 */
function formatDateTime($datetime, $format = 'M j, Y g:i A') {
    return date($format, strtotime($datetime));
}

/**
 * Generate unique filename
 */
function generateUniqueFilename($originalName) {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    return uniqid('upload_', true) . '.' . $extension;
}

/**
 * Validate file upload
 */
function validateFileUpload($file, $allowedTypes = ALLOWED_IMAGE_TYPES, $maxSize = MAX_FILE_SIZE) {
    $errors = [];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload failed.';
        return $errors;
    }
    
    if ($file['size'] > $maxSize) {
        $errors[] = 'File size exceeds maximum allowed size.';
    }
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedTypes)) {
        $errors[] = 'File type not allowed.';
    }
    
    return $errors;
}

/**
 * Upload file
 */
function uploadFile($file, $destination = 'general') {
    $errors = validateFileUpload($file);
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    $uploadDir = UPLOADS_PATH . '/' . $destination . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $filename = generateUniqueFilename($file['name']);
    $uploadPath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $uploadPath,
            'url' => UPLOADS_URL . '/' . $destination . '/' . $filename
        ];
    } else {
        return ['success' => false, 'errors' => ['Failed to upload file.']];
    }
}

/**
 * Send email (basic implementation)
 */
function sendEmail($to, $subject, $message, $headers = []) {
    $defaultHeaders = [
        'From: ' . FROM_NAME . ' <' . FROM_EMAIL . '>',
        'Reply-To: ' . FROM_EMAIL,
        'X-Mailer: PHP/' . phpversion(),
        'Content-Type: text/html; charset=UTF-8'
    ];
    
    $allHeaders = array_merge($defaultHeaders, $headers);
    $headerString = implode("\r\n", $allHeaders);
    
    return mail($to, $subject, $message, $headerString);
}

/**
 * Log activity
 */
function logActivity($userId, $action, $details = '') {
    try {
        $db = Database::getInstance();
        $sql = "INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        $db->query($sql, [
            $userId,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

/**
 * Generate random string
 */
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Calculate distance between two points (for delivery)
 */
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Earth's radius in kilometers
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
    return $earthRadius * $c;
}

/**
 * Get cart from session
 */
function getCart() {
    return $_SESSION['cart'] ?? [];
}

/**
 * Add item to cart
 */
function addToCart($itemId, $quantity = 1, $options = []) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $cartKey = $itemId . '_' . md5(serialize($options));
    
    if (isset($_SESSION['cart'][$cartKey])) {
        $_SESSION['cart'][$cartKey]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$cartKey] = [
            'item_id' => $itemId,
            'quantity' => $quantity,
            'options' => $options
        ];
    }
}

/**
 * Remove item from cart
 */
function removeFromCart($cartKey) {
    if (isset($_SESSION['cart'][$cartKey])) {
        unset($_SESSION['cart'][$cartKey]);
    }
}

/**
 * Clear cart
 */
function clearCart() {
    $_SESSION['cart'] = [];
}

/**
 * Get cart total
 */
function getCartTotal() {
    $total = 0;
    $cart = getCart();
    
    if (empty($cart)) {
        return $total;
    }
    
    $db = Database::getInstance();
    $itemIds = array_column($cart, 'item_id');
    $placeholders = str_repeat('?,', count($itemIds) - 1) . '?';
    
    $sql = "SELECT id, price FROM menu_items WHERE id IN ($placeholders)";
    $items = $db->fetchAll($sql, $itemIds);
    
    $prices = array_column($items, 'price', 'id');
    
    foreach ($cart as $cartItem) {
        if (isset($prices[$cartItem['item_id']])) {
            $total += $prices[$cartItem['item_id']] * $cartItem['quantity'];
        }
    }
    
    return $total;
}

/**
 * Flash message system
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessages() {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

/**
 * Rate limiting
 */
function isRateLimited($key, $maxAttempts = 5, $timeWindow = 900) {
    $cacheFile = sys_get_temp_dir() . '/rate_limit_' . md5($key);
    $now = time();
    
    if (file_exists($cacheFile)) {
        $data = json_decode(file_get_contents($cacheFile), true);
        if ($data && ($now - $data['timestamp']) < $timeWindow) {
            if ($data['attempts'] >= $maxAttempts) {
                return true;
            }
            $data['attempts']++;
        } else {
            $data = ['attempts' => 1, 'timestamp' => $now];
        }
    } else {
        $data = ['attempts' => 1, 'timestamp' => $now];
    }
    
    file_put_contents($cacheFile, json_encode($data));
    return false;
}
