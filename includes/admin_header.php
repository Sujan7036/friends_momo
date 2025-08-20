<?php
/**
 * Admin Header
 * Friends and Momos Restaurant Management System
 */

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ' . BASE_URL . '/views/public/login.php?redirect=admin');
    exit;
}

$currentUser = $_SESSION['user'];
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin Panel - Friends and Momos') ?></title>
    <meta name="description" content="Admin panel for Friends and Momos restaurant management system">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= ASSETS_URL ?>/images/favicon.ico">
    <link rel="apple-touch-icon" href="<?= ASSETS_URL ?>/images/apple-touch-icon.png">
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="<?= htmlspecialchars($bodyClass ?? 'admin-body') ?>">
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="header-container">
            <!-- Logo Section -->
            <div class="header-logo">
                <a href="<?= BASE_URL ?>/views/admin/dashboard.php" class="logo-link">
                    <img src="<?= ASSETS_URL ?>/images/logo.png" alt="Friends and Momos" class="logo-image">
                    <span class="logo-text">Admin Panel</span>
                </a>
            </div>
            
            <!-- Navigation Menu -->
            <nav class="admin-nav">
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>/views/admin/dashboard.php" 
                           class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                            <i class="fas fa-chart-pie"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>/views/admin/orders.php" 
                           class="nav-link <?= $currentPage === 'orders' ? 'active' : '' ?>">
                            <i class="fas fa-shopping-bag"></i>
                            <span>Orders</span>
                            <?php
                            // Get pending orders count for notification
                            try {
                                $db = Database::getInstance();
                                $stmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
                                $stmt->execute();
                                $pendingCount = $stmt->fetchColumn();
                                if ($pendingCount > 0):
                            ?>
                                <span class="notification-badge"><?= $pendingCount ?></span>
                            <?php endif; } catch (Exception $e) { /* Ignore errors */ } ?>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>/views/admin/reservations.php" 
                           class="nav-link <?= $currentPage === 'reservations' ? 'active' : '' ?>">
                            <i class="fas fa-calendar-check"></i>
                            <span>Reservations</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>/views/admin/menu.php" 
                           class="nav-link <?= $currentPage === 'menu' ? 'active' : '' ?>">
                            <i class="fas fa-utensils"></i>
                            <span>Menu</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>/views/admin/customers.php" 
                           class="nav-link <?= $currentPage === 'customers' ? 'active' : '' ?>">
                            <i class="fas fa-users"></i>
                            <span>Customers</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>/views/admin/reports.php" 
                           class="nav-link <?= $currentPage === 'reports' ? 'active' : '' ?>">
                            <i class="fas fa-chart-bar"></i>
                            <span>Reports</span>
                        </a>
                    </li>
                    
                </ul>
            </nav>
            
            <!-- Header Actions -->
            <div class="header-actions">
                <!-- Notifications -->
                <div class="notification-dropdown">
                    <button type="button" class="notification-btn" id="notification-toggle">
                        <i class="fas fa-bell"></i>
                        <span class="notification-count">3</span>
                    </button>
                    
                    <div class="notification-panel" id="notification-panel">
                        <div class="notification-header">
                            <h4>Notifications</h4>
                            <button type="button" class="mark-all-read">Mark all read</button>
                        </div>
                        
                        <div class="notification-list">
                            <div class="notification-item unread">
                                <div class="notification-icon order">
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                                <div class="notification-content">
                                    <p class="notification-text">New order #000123 received</p>
                                    <span class="notification-time">2 minutes ago</span>
                                </div>
                            </div>
                            
                            <div class="notification-item unread">
                                <div class="notification-icon reservation">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div class="notification-content">
                                    <p class="notification-text">Table reservation for 4 people at 7:00 PM</p>
                                    <span class="notification-time">15 minutes ago</span>
                                </div>
                            </div>
                            
                            <div class="notification-item">
                                <div class="notification-icon system">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div class="notification-content">
                                    <p class="notification-text">Daily sales report is ready</p>
                                    <span class="notification-time">1 hour ago</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="notification-footer">
                            <a href="<?= BASE_URL ?>/views/admin/notifications.php">View all notifications</a>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="quick-actions">
                    <a href="<?= BASE_URL ?>/views/public/index.php" 
                       class="quick-action-btn" 
                       title="View Website" 
                       target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                    
                    <button type="button" class="quick-action-btn" title="Refresh" onclick="location.reload()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                
                <!-- User Menu -->
                <div class="user-menu">
                    <button type="button" class="user-menu-btn" id="user-menu-toggle">
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="user-info">
                            <span class="user-name"><?= htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']) ?></span>
                            <span class="user-role">Administrator</span>
                        </div>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    
                    <div class="user-dropdown" id="user-dropdown">
                        <div class="dropdown-header">
                            <div class="user-avatar large">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="user-details">
                                <span class="user-name"><?= htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']) ?></span>
                                <span class="user-email"><?= htmlspecialchars($currentUser['email']) ?></span>
                            </div>
                        </div>
                        
                        <div class="dropdown-menu">
                            <a href="<?= BASE_URL ?>/views/admin/profile.php" class="dropdown-item">
                                <i class="fas fa-user-circle"></i>
                                <span>My Profile</span>
                            </a>
                            
                            <a href="<?= BASE_URL ?>/views/admin/help.php" class="dropdown-item">
                                <i class="fas fa-question-circle"></i>
                                <span>Help & Support</span>
                            </a>
                            
                            <div class="dropdown-divider"></div>
                            
                            <a href="<?= BASE_URL ?>/controllers/AuthController.php?action=logout" class="dropdown-item logout">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Sign Out</span>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Mobile Menu Toggle -->
                <button type="button" class="mobile-menu-toggle" id="mobile-menu-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </header>
    
    <!-- Mobile Navigation -->
    <div class="mobile-nav" id="mobile-nav">
        <div class="mobile-nav-header">
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-details">
                    <span class="user-name"><?= htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']) ?></span>
                    <span class="user-role">Administrator</span>
                </div>
            </div>
        </div>
        
        <nav class="mobile-nav-menu">
            <a href="<?= BASE_URL ?>/views/admin/dashboard.php" 
               class="mobile-nav-item <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                <i class="fas fa-chart-pie"></i>
                <span>Dashboard</span>
            </a>
            
            <a href="<?= BASE_URL ?>/views/admin/orders.php" 
               class="mobile-nav-item <?= $currentPage === 'orders' ? 'active' : '' ?>">
                <i class="fas fa-shopping-bag"></i>
                <span>Orders</span>
            </a>
            
            <a href="<?= BASE_URL ?>/views/admin/reservations.php" 
               class="mobile-nav-item <?= $currentPage === 'reservations' ? 'active' : '' ?>">
                <i class="fas fa-calendar-check"></i>
                <span>Reservations</span>
            </a>
            
            <a href="<?= BASE_URL ?>/views/admin/menu.php" 
               class="mobile-nav-item <?= $currentPage === 'menu' ? 'active' : '' ?>">
                <i class="fas fa-utensils"></i>
                <span>Menu</span>
            </a>
            
            <a href="<?= BASE_URL ?>/views/admin/customers.php" 
               class="mobile-nav-item <?= $currentPage === 'customers' ? 'active' : '' ?>">
                <i class="fas fa-users"></i>
                <span>Customers</span>
            </a>
            
            <a href="<?= BASE_URL ?>/views/admin/reports.php" 
               class="mobile-nav-item <?= $currentPage === 'reports' ? 'active' : '' ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>
            
        </nav>
        
        <div class="mobile-nav-footer">
            <a href="<?= BASE_URL ?>/views/public/index.php" 
               class="mobile-nav-item" 
               target="_blank">
                <i class="fas fa-external-link-alt"></i>
                <span>View Website</span>
            </a>
            
            <a href="<?= BASE_URL ?>/controllers/AuthController.php?action=logout" 
               class="mobile-nav-item logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Sign Out</span>
            </a>
        </div>
    </div>
    
    <!-- Mobile Nav Overlay -->
    <div class="mobile-nav-overlay" id="mobile-nav-overlay"></div>
    
    <!-- Toast Container -->
    <div id="toast-container" class="toast-container"></div>
    
    <!-- Main Content -->
    <main class="admin-main">
