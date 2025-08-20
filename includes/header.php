<?php
/**
 * Header Include File
 * Friends and Momos Restaurant Management System
 */

// Ensure config is loaded
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../config/config.php';
}

// Get current page for navigation highlighting
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$pageTitle = $pageTitle ?? APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Friends and Momos - Authentic Himalayan cuisine in Gungahlin. Order online for delivery or dine-in reservations.">
    <meta name="keywords" content="himalayan food, nepalese cuisine, momos, chowmein, gungahlin restaurant, authentic food">
    <meta name="author" content="Friends and Momos">
    
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= ASSETS_URL ?>/images/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= ASSETS_URL ?>/images/apple-touch-icon.png">
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
    
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Additional CSS if specified -->
    <?php if (isset($additionalCSS) && is_array($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Custom styles for specific pages -->
    <?php if (isset($customStyles)): ?>
        <style><?= $customStyles ?></style>
    <?php endif; ?>
    
    <!-- CSRF Token for JavaScript -->
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
</head>
<body class="<?= $bodyClass ?? '' ?>">
    
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-container">
                <!-- Logo Section -->
                <div class="logo-section">
                    <a href="<?= BASE_URL ?>/views/public/index.php" class="logo" title="Friends and Momos Home">
                        <img src="<?= ASSETS_URL ?>/images/logo.png" alt="Friends and Momos Logo">
                    </a>
                    <h1 class="site-title">
                        <a href="<?= BASE_URL ?>/views/public/index.php" style="text-decoration: none; color: inherit;">
                            Friends and Momos
                        </a>
                    </h1>
                </div>
                
                <!-- Navigation -->
                <nav class="nav-buttons">
                    <a href="<?= BASE_URL ?>/views/public/index.php" 
                       class="nav-btn <?= $currentPage === 'index' ? 'active' : '' ?>"
                       title="Home">
                        <i class="fas fa-home"></i>
                        <span class="nav-text">Home</span>
                    </a>
                    
                    <a href="<?= BASE_URL ?>/views/public/menu.php" 
                       class="nav-btn <?= $currentPage === 'menu' ? 'active' : '' ?>"
                       title="Our Menu">
                        <i class="fas fa-utensils"></i>
                        <span class="nav-text">Menu</span>
                    </a>
                    
                    <?php if (isLoggedIn()): ?>
                    
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/views/public/login.php" 
                           class="nav-btn <?= in_array($currentPage, ['login', 'register']) ? 'active' : '' ?>"
                           title="Login or Register">
                            <i class="fas fa-sign-in-alt"></i>
                            <span class="nav-text">Login</span>
                        </a>
                    <?php endif; ?>
                    
                    <a href="<?= BASE_URL ?>/views/public/reservation.php" 
                       class="nav-btn <?= $currentPage === 'reservation' ? 'active' : '' ?>"
                       title="Table Reservation">
                        <i class="fas fa-calendar-alt"></i>
                        <span class="nav-text">Reserve</span>
                    </a>
                    
                    <a href="<?= BASE_URL ?>/views/public/cart.php" 
                       class="nav-btn cart-btn <?= $currentPage === 'cart' ? 'active' : '' ?>"
                       title="Shopping Cart"
                       id="cart-button">
                        <i class="fas fa-shopping-cart cart-icon"></i>
                        <span class="nav-text">Cart</span>
                        <span class="cart-count" id="cart-count" style="display: none;"></span>
                    </a>
                    
                    <?php if (isAdmin()): ?>
                        <a href="<?= BASE_URL ?>/views/admin/dashboard.php" 
                           class="nav-btn admin-btn"
                           title="Admin Dashboard">
                            <i class="fas fa-cog"></i>
                            <span class="nav-text">Admin</span>
                        </a>
                    <?php endif; ?>
                    
                    <?php if (isLoggedIn()): ?>
                        <a href="<?= BASE_URL ?>/controllers/AuthController.php?action=logout" 
                           class="nav-btn logout-btn"
                           title="Logout"
                           onclick="return confirm('Are you sure you want to logout?')">
                            <i class="fas fa-sign-out-alt"></i>
                            <span class="nav-text">Logout</span>
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>
    
    <!-- Flash Messages -->
    <?php 
    $flashMessages = getFlashMessages();
    if (!empty($flashMessages)): 
    ?>
        <div class="flash-messages-container">
            <div class="container">
                <?php foreach ($flashMessages as $message): ?>
                    <div class="alert alert-<?= htmlspecialchars($message['type']) ?> flash-message">
                        <div class="alert-content">
                            <?php if ($message['type'] === 'success'): ?>
                                <i class="fas fa-check-circle"></i>
                            <?php elseif ($message['type'] === 'error'): ?>
                                <i class="fas fa-exclamation-circle"></i>
                            <?php elseif ($message['type'] === 'warning'): ?>
                                <i class="fas fa-exclamation-triangle"></i>
                            <?php else: ?>
                                <i class="fas fa-info-circle"></i>
                            <?php endif; ?>
                            <span><?= htmlspecialchars($message['message']) ?></span>
                        </div>
                        <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Main Content Wrapper -->
    <main class="main-content">

<style>
/* Additional header styles */
.nav-text {
    margin-left: var(--space-2);
}

@media (max-width: 768px) {
    .nav-text {
        display: none;
    }
    
    .nav-btn {
        padding: var(--space-2) var(--space-3);
        min-width: 44px;
        justify-content: center;
    }
}

.cart-count {
    position: absolute;
    top: -8px;
    right: -8px;
    background-color: var(--error-color);
    color: white;
    border-radius: var(--radius-full);
    font-size: var(--text-xs);
    font-weight: 600;
    min-width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    line-height: 1;
}

/* Cart Button Animations */
.cart-btn {
    position: relative;
    transition: transform 0.2s ease;
}

.cart-btn .cart-icon {
    transition: transform 0.3s ease;
}

.cart-bounce {
    animation: cartBounce 0.6s ease-out;
}

.cart-icon-bounce {
    animation: cartIconShake 0.6s ease-out;
}

.cart-count-flash {
    animation: cartCountPulse 0.5s ease-out;
}

.cart-add-indicator {
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: var(--success-color);
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    animation: indicatorPop 0.6s ease-out;
}

@keyframes cartBounce {
    0% { transform: scale(1); }
    15% { transform: scale(1.15); }
    30% { transform: scale(0.95); }
    50% { transform: scale(1.05); }
    70% { transform: scale(0.98); }
    100% { transform: scale(1); }
}

@keyframes cartIconShake {
    0%, 100% { transform: rotate(0deg); }
    10% { transform: rotate(-10deg); }
    20% { transform: rotate(10deg); }
    30% { transform: rotate(-8deg); }
    40% { transform: rotate(8deg); }
    50% { transform: rotate(-5deg); }
    60% { transform: rotate(5deg); }
    70% { transform: rotate(-2deg); }
    80% { transform: rotate(2deg); }
}

@keyframes cartCountPulse {
    0% { transform: scale(1); background-color: var(--error-color); }
    50% { transform: scale(1.3); background-color: var(--success-color); }
    100% { transform: scale(1); background-color: var(--error-color); }
}

@keyframes indicatorPop {
    0% { transform: scale(0) rotate(0deg); opacity: 0; }
    50% { transform: scale(1.2) rotate(180deg); opacity: 1; }
    100% { transform: scale(1) rotate(360deg); opacity: 1; }
}

/* Add to Cart Button States */
.add-to-cart-btn.adding-to-cart {
    background-color: var(--warning-color);
    cursor: not-allowed;
    opacity: 0.8;
}

.add-to-cart-btn.added-to-cart {
    background-color: var(--success-color);
    transform: scale(1.05);
    transition: all 0.3s ease;
}
}

.admin-btn {
    background-color: var(--accent-color);
    border-color: var(--accent-color);
}

.admin-btn:hover {
    background-color: var(--accent-dark);
    border-color: var(--accent-dark);
}

.logout-btn {
    background-color: var(--error-color);
    border-color: var(--error-color);
}

.logout-btn:hover {
    background-color: var(--error-light);
    border-color: var(--error-light);
}

.flash-messages-container {
    position: sticky;
    top: var(--header-height);
    z-index: 40;
    padding: var(--space-4) 0;
}

.flash-message {
    position: relative;
    animation: slideDown 0.3s ease-out;
}

.alert-content {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.alert-close {
    position: absolute;
    top: var(--space-2);
    right: var(--space-2);
    background: none;
    border: none;
    color: inherit;
    cursor: pointer;
    padding: var(--space-1);
    border-radius: var(--radius);
    opacity: 0.7;
    transition: var(--transition-fast);
}

.alert-close:hover {
    opacity: 1;
    background-color: rgba(255, 255, 255, 0.2);
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.main-content {
    min-height: calc(100vh - var(--header-height));
}
</style>

<script>
// Update cart count on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
    
    // Auto-hide flash messages after 5 seconds
    const flashMessages = document.querySelectorAll('.flash-message');
    flashMessages.forEach(message => {
        setTimeout(() => {
            if (message.parentElement) {
                message.style.animation = 'slideUp 0.3s ease-in forwards';
                setTimeout(() => message.remove(), 300);
            }
        }, 5000);
    });
});

function updateCartCount() {
    fetch('<?= BASE_URL ?>/api/cart.php?action=count')
        .then(response => response.json())
        .then(data => {
            const cartCountElement = document.getElementById('cart-count');
            if (data.count > 0) {
                cartCountElement.textContent = data.count;
                cartCountElement.style.display = 'flex';
            } else {
                cartCountElement.style.display = 'none';
            }
        })
        .catch(error => console.log('Cart count update failed:', error));
}

// Cart button animation function
function animateCartButton() {
    const cartButton = document.getElementById('cart-button');
    const cartCount = document.getElementById('cart-count');
    
    // Add bounce animation to cart button
    cartButton.classList.add('animate');
    
    // Add pulse animation to cart count
    if (cartCount.style.display !== 'none') {
        cartCount.classList.add('pulse');
    }
    
    // Remove animations after completion
    setTimeout(() => {
        cartButton.classList.remove('animate');
        cartCount.classList.remove('pulse');
    }, 600);
}

// Make animation function globally available
window.animateCartButton = animateCartButton;

// Add slideUp animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slideUp {
        to {
            opacity: 0;
            transform: translateY(-10px);
        }
    }
`;
document.head.appendChild(style);
</script>
