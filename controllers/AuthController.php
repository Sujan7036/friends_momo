<?php
/**
 * Authentication Controller
 * Friends and Momos Restaurant Management System
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/models/User.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Handle login
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectWithError('Invalid request method');
            return;
        }
        
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $rememberMe = isset($_POST['remember_me']);
        $redirectTo = $_POST['redirect'] ?? '';
        
        // Validate input
        if (empty($email) || empty($password)) {
            $this->redirectWithError('Please fill in all fields', $redirectTo);
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirectWithError('Please enter a valid email address', $redirectTo);
            return;
        }
        
        // Check rate limiting
        if ($this->isRateLimited($email)) {
            $this->redirectWithError('Too many login attempts. Please try again later.', $redirectTo);
            return;
        }
        
        // Attempt login
        $loginResult = $this->userModel->login($email, $password);
        
        if ($loginResult['success']) {
            $user = $loginResult['user'];
            
            // Check if account is active
            if ($user['status'] !== 'active') {
                $this->logFailedAttempt($email);
                $this->redirectWithError('Your account is not active. Please contact support.', $redirectTo);
                return;
            }
            
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = $user;
            $_SESSION['login_time'] = time();
            
            // Handle remember me
            if ($rememberMe) {
                $this->setRememberMeToken($user['id']);
            }
            
            // Update last login
            $this->userModel->updateLastLogin($user['id']);
            
            // Log successful login
            $this->logActivity($user['id'], 'login', 'User logged in successfully');
            
            // Clear failed attempts
            $this->clearFailedAttempts($email);
            
            // Redirect based on user role and redirect parameter
            $this->redirectAfterLogin($user, $redirectTo);
            
        } else {
            // Log failed attempt
            $this->logFailedAttempt($email);
            $this->redirectWithError($loginResult['message'], $redirectTo);
        }
    }
    
    /**
     * Handle logout
     */
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            // Log logout activity
            $this->logActivity($_SESSION['user_id'], 'logout', 'User logged out');
            
            // Clear remember me token if exists
            if (isset($_COOKIE['remember_token'])) {
                $this->clearRememberMeToken($_SESSION['user_id']);
            }
        }
        
        // Clear session
        session_unset();
        session_destroy();
        
        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', true, true);
        }
        
        // Redirect to login page
        header('Location: ' . BASE_URL . '/views/public/login.php?message=logged_out');
        exit;
    }
    
    /**
     * Handle registration
     */
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectWithError('Invalid request method');
            return;
        }
        
        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'phone' => trim($_POST['phone'] ?? ''),
            'terms_accepted' => isset($_POST['terms_accepted'])
        ];
        
        // Validate input
        $validation = $this->validateRegistrationData($data);
        if (!$validation['valid']) {
            $this->redirectWithError($validation['errors'][0]);
            return;
        }
        
        // Check if email already exists
        if ($this->userModel->emailExists($data['email'])) {
            $this->redirectWithError('An account with this email already exists');
            return;
        }
        
        // Create user
        $userId = $this->userModel->createUser([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'],
            'role' => 'customer'
        ]);
        
        if ($userId) {
            // Log registration activity
            $this->logActivity($userId, 'register', 'User registered successfully');
            
            // Auto-login the user
            $user = $this->userModel->getUserById($userId);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = $user;
            $_SESSION['login_time'] = time();
            
            // Redirect to dashboard or intended page
            $redirectTo = $_POST['redirect'] ?? '';
            $this->redirectAfterLogin($user, $redirectTo);
            
        } else {
            $this->redirectWithError('Registration failed. Please try again.');
        }
    }
    
    /**
     * Handle password reset request
     */
    public function forgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectWithError('Invalid request method');
            return;
        }
        
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirectWithError('Please enter a valid email address');
            return;
        }
        
        // Check if user exists
        $user = $this->userModel->getUserByEmail($email);
        if (!$user) {
            // Don't reveal if email exists or not for security
            header('Location: ' . BASE_URL . '/views/public/login.php?message=reset_sent');
            exit;
        }
        
        // Generate reset token
        $resetToken = $this->generateResetToken();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Save reset token
        $saved = $this->userModel->savePasswordResetToken($user['id'], $resetToken, $expiresAt);
        
        if ($saved) {
            // Send reset email (in a real app)
            $this->sendPasswordResetEmail($user, $resetToken);
            
            // Log activity
            $this->logActivity($user['id'], 'password_reset_request', 'Password reset requested');
        }
        
        // Always show success message for security
        header('Location: ' . BASE_URL . '/views/public/login.php?message=reset_sent');
        exit;
    }
    
    /**
     * Handle password reset
     */
    public function resetPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectWithError('Invalid request method');
            return;
        }
        
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate input
        if (empty($token) || empty($password) || empty($confirmPassword)) {
            $this->redirectWithError('Please fill in all fields');
            return;
        }
        
        if ($password !== $confirmPassword) {
            $this->redirectWithError('Passwords do not match');
            return;
        }
        
        if (strlen($password) < 8) {
            $this->redirectWithError('Password must be at least 8 characters long');
            return;
        }
        
        // Verify reset token
        $userId = $this->userModel->verifyPasswordResetToken($token);
        if (!$userId) {
            $this->redirectWithError('Invalid or expired reset token');
            return;
        }
        
        // Reset password
        $success = $this->userModel->resetPassword($userId, $password);
        if ($success) {
            // Clear reset token
            $this->userModel->clearPasswordResetToken($userId);
            
            // Log activity
            $this->logActivity($userId, 'password_reset', 'Password reset successfully');
            
            header('Location: ' . BASE_URL . '/views/public/login.php?message=password_reset');
        } else {
            $this->redirectWithError('Failed to reset password. Please try again.');
        }
        exit;
    }
    
    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Check if user has specific role
     */
    public static function hasRole($role) {
        return self::isAuthenticated() && $_SESSION['user']['role'] === $role;
    }
    
    /**
     * Require authentication
     */
    public static function requireAuth($redirectTo = 'login') {
        if (!self::isAuthenticated()) {
            $currentUrl = $_SERVER['REQUEST_URI'];
            header('Location: ' . BASE_URL . '/views/public/login.php?redirect=' . urlencode($currentUrl));
            exit;
        }
    }
    
    /**
     * Require specific role
     */
    public static function requireRole($role, $redirectTo = 'login') {
        self::requireAuth();
        
        if (!self::hasRole($role)) {
            header('Location: ' . BASE_URL . '/views/public/login.php?error=access_denied');
            exit;
        }
    }
    
    /**
     * Validate registration data
     */
    private function validateRegistrationData($data) {
        $errors = [];
        
        if (empty($data['first_name'])) {
            $errors[] = 'First name is required';
        }
        
        if (empty($data['last_name'])) {
            $errors[] = 'Last name is required';
        }
        
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required';
        }
        
        if (empty($data['password'])) {
            $errors[] = 'Password is required';
        } elseif (strlen($data['password']) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        
        if ($data['password'] !== $data['confirm_password']) {
            $errors[] = 'Passwords do not match';
        }
        
        if (!$data['terms_accepted']) {
            $errors[] = 'You must accept the terms and conditions';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Check rate limiting for login attempts
     */
    private function isRateLimited($email) {
        $key = 'login_attempts_' . md5($email);
        $attempts = $_SESSION[$key] ?? [];
        
        // Remove attempts older than 15 minutes
        $cutoff = time() - (15 * 60);
        $attempts = array_filter($attempts, function($timestamp) use ($cutoff) {
            return $timestamp > $cutoff;
        });
        
        $_SESSION[$key] = $attempts;
        
        // Check if too many attempts
        return count($attempts) >= 5;
    }
    
    /**
     * Log failed login attempt
     */
    private function logFailedAttempt($email) {
        $key = 'login_attempts_' . md5($email);
        $attempts = $_SESSION[$key] ?? [];
        $attempts[] = time();
        $_SESSION[$key] = $attempts;
    }
    
    /**
     * Clear failed login attempts
     */
    private function clearFailedAttempts($email) {
        $key = 'login_attempts_' . md5($email);
        unset($_SESSION[$key]);
    }
    
    /**
     * Set remember me token
     */
    private function setRememberMeToken($userId) {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        // Save token to database
        $this->userModel->saveRememberToken($userId, $token, $expiresAt);
        
        // Set cookie
        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', true, true);
    }
    
    /**
     * Clear remember me token
     */
    private function clearRememberMeToken($userId) {
        $this->userModel->clearRememberToken($userId);
        setcookie('remember_token', '', time() - 3600, '/', '', true, true);
    }
    
    /**
     * Generate password reset token
     */
    private function generateResetToken() {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Send password reset email (placeholder)
     */
    private function sendPasswordResetEmail($user, $token) {
        // In a real application, this would send an email
        // For now, we'll just log it
        error_log("Password reset token for {$user['email']}: $token");
    }
    
    /**
     * Log user activity
     */
    private function logActivity($userId, $action, $description) {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                INSERT INTO user_activity_logs (user_id, action, description, ip_address, user_agent, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $userId,
                $action,
                $description,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);
        } catch (Exception $e) {
            error_log("Failed to log activity: " . $e->getMessage());
        }
    }
    
    /**
     * Redirect after successful login
     */
    private function redirectAfterLogin($user, $redirectTo = '') {
        // Determine redirect URL
        $redirectUrl = '';
        
        if (!empty($redirectTo)) {
            // Use provided redirect URL
            $redirectUrl = $redirectTo === 'admin' 
                ? BASE_URL . '/views/admin/dashboard.php'
                : $redirectTo;
        } else {
            // Default redirect based on role
            switch ($user['role']) {
                case 'admin':
                    $redirectUrl = BASE_URL . '/views/admin/dashboard.php';
                    break;
                case 'customer':
                default:
                    $redirectUrl = BASE_URL . '/views/user/dashboard.php';
                    break;
            }
        }
        
        header('Location: ' . $redirectUrl);
        exit;
    }
    
    /**
     * Redirect with error message
     */
    private function redirectWithError($message, $redirectTo = '') {
        $url = BASE_URL . '/views/public/login.php?error=' . urlencode($message);
        if (!empty($redirectTo)) {
            $url .= '&redirect=' . urlencode($redirectTo);
        }
        header('Location: ' . $url);
        exit;
    }
}

// Handle direct requests to this controller
if (basename($_SERVER['PHP_SELF']) === 'AuthController.php') {
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    $controller = new AuthController();
    
    switch ($action) {
        case 'login':
            $controller->login();
            break;
        case 'logout':
            $controller->logout();
            break;
        case 'register':
            $controller->register();
            break;
        case 'forgot_password':
            $controller->forgotPassword();
            break;
        case 'reset_password':
            $controller->resetPassword();
            break;
        default:
            header('Location: ' . BASE_URL . '/views/public/login.php');
            exit;
    }
}
?>
