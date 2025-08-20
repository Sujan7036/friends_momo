<?php
/**
 * Login Page
 * Friends and Momos Restaurant Management System
 */

// Page configuration
$pageTitle = "Login - Friends and Momos | Authentic Himalayan Cuisine";
$bodyClass = "login-page";

// Load required files
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/models/User.php';

// Initialize models
$userModel = new User();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $redirectUrl = $_GET['redirect'] ?? 'dashboard';
    $redirectPath = $redirectUrl === 'checkout' ? '/views/public/checkout.php' : '/views/user/dashboard.php';
    header('Location: ' . BASE_URL . $redirectPath);
    exit;
}

// Handle form submission
$formSubmitted = false;
$formErrors = [];
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formSubmitted = true;
    
    // Get form data
    $formData = [
        'email' => trim($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'remember_me' => isset($_POST['remember_me'])
    ];
    
    // Validate form data
    if (empty($formData['email'])) {
        $formErrors['email'] = 'Email address is required.';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $formErrors['email'] = 'Please enter a valid email address.';
    }
    
    if (empty($formData['password'])) {
        $formErrors['password'] = 'Password is required.';
    }
    
    // Attempt login if no validation errors
    if (empty($formErrors)) {
        $user = $userModel->login($formData['email'], $formData['password']);
        
        if ($user) {
            // Set session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = $user;
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_type'] = $user['role'];
            
            // Set remember me cookie if requested
            if ($formData['remember_me']) {
                $token = bin2hex(random_bytes(32));
                $userModel->saveRememberToken($user['id'], $token);
                setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', true, true); // 30 days
            }
            
            // Log successful login
            error_log("User login: " . $user['email'] . " from " . $_SERVER['REMOTE_ADDR']);
            
            // Redirect based on user role and redirect parameter
            $redirectUrl = $_GET['redirect'] ?? 'dashboard';
            
            if ($user['role'] === 'admin') {
                $redirectPath = '/views/admin/dashboard.php';
            } elseif ($user['role'] === 'staff') {
                $redirectPath = '/views/staff/dashboard.php';
            } elseif ($redirectUrl === 'checkout') {
                $redirectPath = '/views/public/checkout.php';
            } else {
                $redirectPath = '/views/user/dashboard.php';
            }
            
            header('Location: ' . BASE_URL . $redirectPath);
            exit;
        } else {
            $formErrors['general'] = 'Invalid email address or password. Please try again.';
        }
    }
}

// Include header
include_once dirname(__DIR__, 2) . '/includes/header.php';
?>

<!-- Login Hero Section -->
<section class="login-hero">
    <div class="container">
        <div class="login-hero-content">
            <h1 class="page-title">Welcome Back</h1>
            <p class="page-description">
                Sign in to your account to place orders, track deliveries, and enjoy exclusive member benefits.
            </p>
        </div>
    </div>
</section>

<!-- Login Form Section -->
<section class="login-form-section">
    <div class="container">
        <div class="login-content">
            <!-- Login Form -->
            <div class="login-form-container">
                <div class="form-header">
                    <div class="logo-section">
                        <img src="<?= ASSETS_URL ?>/images/logo.png" alt="Friends and Momos" class="form-logo">
                        <div class="brand-text">
                            <h1 class="brand-name">Friends & Momos</h1>
                            <p class="brand-tagline">Authentic Himalayan Cuisine</p>
                        </div>
                    </div>
                    <div class="form-title">
                        <h2>Sign In</h2>
                        <p>Enter your credentials to access your account</p>
                    </div>
                </div>
                
                <?php if (!empty($formErrors['general'])): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?= htmlspecialchars($formErrors['general']) ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="login-form" class="login-form" novalidate>
                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope"></i>
                            Email Address
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-input <?= isset($formErrors['email']) ? 'error' : '' ?>"
                               value="<?= htmlspecialchars($formData['email'] ?? '') ?>"
                               placeholder="Enter your email address"
                               required>
                        <?php if (isset($formErrors['email'])): ?>
                            <span class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                <?= htmlspecialchars($formErrors['email']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i>
                            Password
                        </label>
                        <div class="password-input-container">
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="form-input <?= isset($formErrors['password']) ? 'error' : '' ?>"
                                   placeholder="Enter your password"
                                   required>
                            <button type="button" class="password-toggle" id="password-toggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <?php if (isset($formErrors['password'])): ?>
                            <span class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                <?= htmlspecialchars($formErrors['password']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-options">
                        <div class="remember-me">
                            <input type="checkbox" 
                                   id="remember_me" 
                                   name="remember_me" 
                                   <?= ($formData['remember_me'] ?? false) ? 'checked' : '' ?>>
                            <label for="remember_me" class="checkbox-label">
                                <span class="checkbox-custom"></span>
                                Remember me for 30 days
                            </label>
                        </div>
                        
                        <a href="<?= BASE_URL ?>/views/public/forgot-password.php" class="forgot-password-link">
                            Forgot Password?
                        </a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-xl submit-btn">
                        <i class="fas fa-sign-in-alt"></i>
                        <span class="btn-text">Sign In</span>
                        <span class="btn-loading" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i>
                            Signing In...
                        </span>
                    </button>
                </form>
                
                <div class="form-footer">
                    <p class="signup-prompt">
                        Don't have an account?
                        <a href="<?= BASE_URL ?>/views/public/register.php<?= isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>" 
                           class="signup-link">
                            Create Account
                        </a>
                    </p>
                    
                    <div class="divider">
                        <span>or</span>
                    </div>
                    
                    <div class="quick-access">
                        <h4>Continue as Guest</h4>
                        <p>You can browse our menu and make reservations without an account.</p>
                        <div class="guest-actions">
                            <a href="<?= BASE_URL ?>/views/public/menu.php" class="btn btn-secondary">
                                <i class="fas fa-utensils"></i>
                                Browse Menu
                            </a>
                            <a href="<?= BASE_URL ?>/views/public/reservation.php" class="btn btn-outline">
                                <i class="fas fa-calendar-alt"></i>
                                Make Reservation
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Benefits Section -->
            <div class="benefits-section">
                <div class="benefits-card">
                    <div class="benefits-header">
                        <h3>Member Benefits</h3>
                        <p>Join our community and enjoy exclusive perks</p>
                    </div>
                    
                    <div class="benefits-list">
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="fas fa-percentage"></i>
                            </div>
                            <div class="benefit-content">
                                <h4>Exclusive Discounts</h4>
                                <p>Get special member-only discounts and promotional offers on your favorite dishes.</p>
                            </div>
                        </div>
                        
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="fas fa-shipping-fast"></i>
                            </div>
                            <div class="benefit-content">
                                <h4>Fast Checkout</h4>
                                <p>Save your delivery details and payment methods for lightning-fast ordering.</p>
                            </div>
                        </div>
                        
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="fas fa-history"></i>
                            </div>
                            <div class="benefit-content">
                                <h4>Order History</h4>
                                <p>Track your orders, reorder favorites, and access your complete order history.</p>
                            </div>
                        </div>
                        
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="benefit-content">
                                <h4>Loyalty Rewards</h4>
                                <p>Earn points with every order and unlock special rewards and free dishes.</p>
                            </div>
                        </div>
                        
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="benefit-content">
                                <h4>Priority Reservations</h4>
                                <p>Get priority booking for tables and special events at our restaurant.</p>
                            </div>
                        </div>
                        
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="benefit-content">
                                <h4>Order Updates</h4>
                                <p>Receive real-time notifications about your order status and delivery updates.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="benefits-footer">
                        <p>Join thousands of satisfied customers who trust Friends and Momos for authentic Himalayan cuisine.</p>
                        <div class="stats">
                            <div class="stat">
                                <span class="stat-number">5000+</span>
                                <span class="stat-label">Happy Members</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number">4.8★</span>
                                <span class="stat-label">Average Rating</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number">50k+</span>
                                <span class="stat-label">Orders Delivered</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Security Notice -->
<section class="security-section">
    <div class="container">
        <div class="security-notice">
            <div class="security-content">
                <div class="security-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="security-text">
                    <h4>Your Privacy & Security</h4>
                    <p>
                        We use industry-standard encryption to protect your personal information and payment details. 
                        Your data is secure with us and we never share it with third parties.
                    </p>
                </div>
            </div>
            <div class="security-badges">
                <div class="security-badge">
                    <i class="fas fa-lock"></i>
                    <span>SSL Encrypted</span>
                </div>
                <div class="security-badge">
                    <i class="fas fa-credit-card"></i>
                    <span>Secure Payments</span>
                </div>
                <div class="security-badge">
                    <i class="fas fa-user-shield"></i>
                    <span>Privacy Protected</span>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Login Page Specific Styles */
.login-hero {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
    color: var(--white);
    padding: var(--space-12) 0 var(--space-8);
    text-align: center;
}

.login-hero-content {
    max-width: 600px;
    margin: 0 auto;
}

.page-title {
    font-size: var(--text-4xl);
    font-weight: 700;
    margin-bottom: var(--space-4);
    color: var(--white);
}

.page-description {
    font-size: var(--text-lg);
    color: var(--gray-100);
    line-height: 1.6;
}

.login-form-section {
    padding: var(--space-16) 0;
    background-color: var(--gray-50);
}

.login-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-12);
    align-items: start;
    max-width: 1200px;
    margin: 0 auto;
}

.login-form-container {
    background-color: var(--white);
    border-radius: var(--radius-2xl);
    padding: var(--space-8);
    box-shadow: var(--shadow-xl);
}

.form-header {
    text-align: center;
    margin-bottom: var(--space-8);
}

.logo-section {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-4);
    margin-bottom: var(--space-6);
}

.form-logo {
    width: 60px;
    height: 60px;
    object-fit: contain;
}

.brand-text {
    text-align: left;
}

.brand-name {
    font-size: var(--text-2xl);
    font-weight: 700;
    color: var(--primary-color);
    margin: 0;
    line-height: 1.2;
}

.brand-tagline {
    font-size: var(--text-sm);
    color: var(--gray-600);
    margin: 0;
    font-weight: 400;
}

.form-title {
    text-align: center;
    margin-bottom: var(--space-6);
}

.form-title h2 {
    font-size: var(--text-3xl);
    font-weight: 700;
    color: var(--gray-900);
    margin: 0;
}

.form-title p {
    font-size: var(--text-base);
    color: var(--gray-600);
    margin-top: var(--space-2);
}
}

.form-header p {
    color: var(--gray-600);
    font-size: var(--text-base);
    margin: 0;
}

.login-form {
    display: flex;
    flex-direction: column;
    gap: var(--space-6);
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.form-label {
    font-weight: 600;
    color: var(--gray-700);
    font-size: var(--text-base);
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.form-label i {
    color: var(--primary-color);
    width: 20px;
}

.form-input {
    padding: var(--space-4);
    border: 2px solid var(--gray-300);
    border-radius: var(--radius-lg);
    font-size: var(--text-base);
    transition: var(--transition);
    background-color: var(--white);
}

.form-input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
}

.form-input.error {
    border-color: var(--error-color);
    background-color: var(--error-light);
}

.form-input::placeholder {
    color: var(--gray-500);
}

.password-input-container {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: var(--space-4);
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--gray-500);
    cursor: pointer;
    padding: var(--space-1);
    border-radius: var(--radius);
    transition: var(--transition);
}

.password-toggle:hover {
    color: var(--primary-color);
    background-color: var(--primary-light);
}

.error-message {
    color: var(--error-color);
    font-size: var(--text-sm);
    display: flex;
    align-items: center;
    gap: var(--space-2);
    margin-top: var(--space-1);
}

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: var(--space-4);
}

.remember-me {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    cursor: pointer;
    font-size: var(--text-sm);
    color: var(--gray-700);
}

.checkbox-custom {
    width: 20px;
    height: 20px;
    border: 2px solid var(--gray-300);
    border-radius: var(--radius);
    position: relative;
    transition: var(--transition);
}

#remember_me {
    display: none;
}

#remember_me:checked + .checkbox-label .checkbox-custom {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

#remember_me:checked + .checkbox-label .checkbox-custom::after {
    content: '\f00c';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: var(--white);
    font-size: 12px;
}

.forgot-password-link {
    color: var(--primary-color);
    text-decoration: none;
    font-size: var(--text-sm);
    font-weight: 500;
    transition: var(--transition);
}

.forgot-password-link:hover {
    text-decoration: underline;
    color: var(--primary-dark);
}

.submit-btn {
    position: relative;
    overflow: hidden;
    width: 100%;
    justify-content: center;
}

.btn-text, .btn-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-2);
}

.form-footer {
    margin-top: var(--space-8);
    text-align: center;
}

.signup-prompt {
    color: var(--gray-600);
    margin-bottom: var(--space-6);
}

.signup-link {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;
    margin-left: var(--space-2);
}

.signup-link:hover {
    text-decoration: underline;
}

.divider {
    position: relative;
    margin: var(--space-6) 0;
    padding: 0 var(--space-4);
}

.divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background-color: var(--gray-300);
}

.divider span {
    background-color: var(--white);
    color: var(--gray-500);
    padding: 0 var(--space-4);
    font-size: var(--text-sm);
    position: relative;
}

.quick-access {
    background-color: var(--gray-50);
    padding: var(--space-6);
    border-radius: var(--radius-xl);
    margin-top: var(--space-6);
}

.quick-access h4 {
    font-size: var(--text-lg);
    color: var(--gray-900);
    margin-bottom: var(--space-3);
}

.quick-access p {
    color: var(--gray-600);
    font-size: var(--text-sm);
    margin-bottom: var(--space-4);
}

.guest-actions {
    display: flex;
    gap: var(--space-3);
    justify-content: center;
    flex-wrap: wrap;
}

.benefits-section {
    background-color: var(--white);
    border-radius: var(--radius-2xl);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.benefits-card {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.benefits-header {
    background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));
    color: var(--white);
    padding: var(--space-6);
    text-align: center;
}

.benefits-header h3 {
    font-size: var(--text-2xl);
    font-weight: 700;
    margin-bottom: var(--space-2);
}

.benefits-header p {
    color: var(--gray-100);
    margin: 0;
}

.benefits-list {
    padding: var(--space-6);
    flex: 1;
}

.benefit-item {
    display: flex;
    gap: var(--space-4);
    margin-bottom: var(--space-6);
    padding-bottom: var(--space-6);
    border-bottom: 1px solid var(--gray-200);
}

.benefit-item:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.benefit-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-size: var(--text-lg);
    flex-shrink: 0;
}

.benefit-content h4 {
    font-size: var(--text-base);
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: var(--space-2);
}

.benefit-content p {
    color: var(--gray-600);
    font-size: var(--text-sm);
    line-height: 1.5;
    margin: 0;
}

.benefits-footer {
    padding: var(--space-6);
    background-color: var(--gray-50);
    text-align: center;
}

.benefits-footer p {
    color: var(--gray-700);
    margin-bottom: var(--space-6);
    font-size: var(--text-sm);
}

.stats {
    display: flex;
    justify-content: space-around;
    gap: var(--space-4);
}

.stat {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.stat-number {
    font-size: var(--text-xl);
    font-weight: 700;
    color: var(--primary-color);
}

.stat-label {
    font-size: var(--text-xs);
    color: var(--gray-600);
    margin-top: var(--space-1);
}

.security-section {
    padding: var(--space-12) 0;
    background-color: var(--white);
}

.security-notice {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: var(--space-8);
    padding: var(--space-6);
    background-color: var(--gray-50);
    border-radius: var(--radius-2xl);
    border: 1px solid var(--gray-200);
}

.security-content {
    display: flex;
    align-items: center;
    gap: var(--space-4);
}

.security-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--success-color), var(--success-light));
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-size: var(--text-xl);
    flex-shrink: 0;
}

.security-text h4 {
    font-size: var(--text-lg);
    color: var(--gray-900);
    margin-bottom: var(--space-2);
}

.security-text p {
    color: var(--gray-600);
    font-size: var(--text-sm);
    line-height: 1.5;
    margin: 0;
}

.security-badges {
    display: flex;
    gap: var(--space-4);
    flex-wrap: wrap;
}

.security-badge {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-2) var(--space-3);
    background-color: var(--white);
    border-radius: var(--radius);
    font-size: var(--text-sm);
    color: var(--gray-700);
    border: 1px solid var(--gray-300);
}

.security-badge i {
    color: var(--success-color);
}

.alert {
    padding: var(--space-4);
    border-radius: var(--radius-lg);
    margin-bottom: var(--space-6);
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.alert-error {
    background-color: var(--error-light);
    color: var(--error-dark);
    border-left: 4px solid var(--error-color);
}

/* Responsive Design */
@media (max-width: 968px) {
    .login-content {
        grid-template-columns: 1fr;
        gap: var(--space-8);
    }
    
    .security-notice {
        flex-direction: column;
        text-align: center;
        gap: var(--space-6);
    }
    
    .security-badges {
        justify-content: center;
    }
}

@media (max-width: 768px) {
    .page-title {
        font-size: var(--text-3xl);
    }
    
    .login-form-container {
        padding: var(--space-6);
    }
    
    .form-header h2 {
        font-size: var(--text-2xl);
    }
    
    .form-options {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--space-3);
    }
    
    .guest-actions {
        flex-direction: column;
    }
    
    .stats {
        flex-direction: column;
        gap: var(--space-3);
    }
    
    .security-content {
        flex-direction: column;
        text-align: center;
    }
    
    .security-badges {
        flex-direction: column;
        align-items: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('login-form');
    const submitBtn = document.querySelector('.submit-btn');
    const btnText = document.querySelector('.btn-text');
    const btnLoading = document.querySelector('.btn-loading');
    const passwordInput = document.getElementById('password');
    const passwordToggle = document.getElementById('password-toggle');
    
    // Password visibility toggle
    passwordToggle.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        const icon = this.querySelector('i');
        icon.className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
    });
    
    // Form validation
    const inputs = form.querySelectorAll('input[required]');
    
    inputs.forEach(input => {
        input.addEventListener('blur', validateField);
        input.addEventListener('input', clearErrors);
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validate all required fields
        inputs.forEach(input => {
            if (!validateField({ target: input })) {
                isValid = false;
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            return;
        }
        
        // Show loading state
        submitBtn.disabled = true;
        btnText.style.display = 'none';
        btnLoading.style.display = 'flex';
    });
    
    function validateField(e) {
        const field = e.target;
        const value = field.value.trim();
        let isValid = true;
        
        // Clear existing errors
        field.classList.remove('error');
        const existingError = field.parentNode.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
        
        // Required field validation
        if (field.hasAttribute('required') && !value) {
            showError(field, `${getFieldLabel(field)} is required.`);
            isValid = false;
        }
        
        // Email validation
        if (field.type === 'email' && value && !isValidEmail(value)) {
            showError(field, 'Please enter a valid email address.');
            isValid = false;
        }
        
        // Password validation
        if (field.type === 'password' && value && value.length < 6) {
            showError(field, 'Password must be at least 6 characters long.');
            isValid = false;
        }
        
        return isValid;
    }
    
    function clearErrors(e) {
        const field = e.target;
        field.classList.remove('error');
        const existingError = field.parentNode.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
    }
    
    function showError(field, message) {
        field.classList.add('error');
        
        const errorElement = document.createElement('span');
        errorElement.className = 'error-message';
        errorElement.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
        
        // Insert after password container if it's a password field
        const container = field.closest('.password-input-container') || field;
        container.parentNode.appendChild(errorElement);
    }
    
    function getFieldLabel(field) {
        const label = field.parentNode.querySelector('label');
        if (label) {
            return label.textContent.replace(/[^a-zA-Z\s]/g, '').trim();
        }
        return field.name;
    }
    
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
});
</script>

<?php include_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
