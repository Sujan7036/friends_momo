<?php
/**
 * User Login Page
 * Friends and Momos Restaurant Management System
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/User.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    $userType = $_SESSION['user_type'] ?? 'customer';
    if ($userType === 'admin') {
        header('Location: views/admin/dashboard.php');
    } elseif ($userType === 'staff') {
        header('Location: views/staff/dashboard.php');
    } else {
        header('Location: views/user/dashboard.php');
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        try {
            $userModel = new User();
            $user = $userModel->authenticate($email, $password);
            
            if ($user) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_type'] = $user['role'];
                $_SESSION['user'] = $user;
                
                // Redirect based on user type
                switch ($user['role']) {
                    case 'admin':
                        header('Location: views/admin/dashboard.php');
                        break;
                    case 'staff':
                        header('Location: views/staff/dashboard.php');
                        break;
                    default:
                        header('Location: views/user/dashboard.php');
                }
                exit();
            } else {
                $error = 'Invalid email address or password. Please try again.';
            }
        } catch (Exception $e) {
            $error = 'Login failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Friends & Momos</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= ASSETS_URL ?>/css/auth.css" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <div class="logo-section">
                    <img src="<?= ASSETS_URL ?>/images/logo.png" alt="Friends & Momos" class="logo">
                    <h1 class="brand-name">Friends & Momos</h1>
                </div>
                <h2>Welcome Back</h2>
                <p>Sign in to your account</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="auth-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
                <p><a href="views/public">← Back to Home</a></p>
            </div>
            
        </div>
    </div>
    
    <script src="<?= ASSETS_URL ?>/js/auth.js"></script>
</body>
</html>
