<?php
/**
 * User Dashboard
 * Friends and Momos Restaurant Management System
 */

// Page configuration
$pageTitle = "My Dashboard - Friends and Momos | Authentic Himalayan Cuisine";
$bodyClass = "dashboard-page";

// Load required files
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/models/User.php';
require_once dirname(__DIR__, 2) . '/models/Order.php';
require_once dirname(__DIR__, 2) . '/models/Reservation.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/views/public/login.php?redirect=dashboard');
    exit;
}

// Initialize models
$userModel = new User();
$orderModel = new Order();
$reservationModel = new Reservation();

// Get user data
$user = $_SESSION['user'];
$userId = $_SESSION['user_id'];

// Get user statistics
$userStats = [
    'total_orders' => $orderModel->getUserOrderCount($userId),
    'total_spent' => $orderModel->getUserTotalSpent($userId),
    'pending_orders' => $orderModel->getUserPendingOrders($userId),
    'upcoming_reservations' => $reservationModel->getUserUpcomingReservations($userId)
];

// Get recent orders
$recentOrders = $orderModel->getUserRecentOrders($userId, 5);

// Get recent reservations
$recentReservations = $reservationModel->getUserRecentReservations($userId, 3);

// Get favorite items
$favoriteItems = $orderModel->getUserFavoriteItems($userId, 4);

// Helper function to map item names to available images
function getItemImage($itemName) {
    $imageMappings = [
        'momo' => 'momo.png',
        'momos' => 'momo.png',
        'chicken momo' => 'momo.png',
        'pork momo' => 'momo.png',
        'veg momo' => 'momo.png',
        'chowmein' => 'chowmin.png',
        'chow mein' => 'chowmin.png',
        'chicken chowmein' => 'chowmin.png',
        'pork chowmein' => 'chowmin.png',
        'laphing' => 'laphing.png',
        'pani puri' => 'panipuri.png',
        'panipuri' => 'panipuri.png',
        'aloo nimkin' => 'allonimkin.png',
        'aloonimkin' => 'allonimkin.png',
    ];
    
    $itemNameLower = strtolower($itemName);
    
    // Check for exact matches first
    if (isset($imageMappings[$itemNameLower])) {
        return $imageMappings[$itemNameLower];
    }
    
    // Check for partial matches
    foreach ($imageMappings as $key => $image) {
        if (strpos($itemNameLower, $key) !== false) {
            return $image;
        }
    }
    
    return 'food.png'; // Default fallback image
}

// Handle profile update
$profileUpdateSuccess = false;
$profileErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $updateData = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'date_of_birth' => $_POST['date_of_birth'] ?? null
    ];
    
    // Validate data
    if (empty($updateData['first_name'])) {
        $profileErrors['first_name'] = 'First name is required.';
    }
    
    if (empty($updateData['last_name'])) {
        $profileErrors['last_name'] = 'Last name is required.';
    }
    
    if (!empty($updateData['phone']) && !preg_match('/^[\d\s\-\+\(\)]{10,15}$/', $updateData['phone'])) {
        $profileErrors['phone'] = 'Please enter a valid phone number.';
    }
    
    if (empty($profileErrors)) {
        $success = $userModel->updateProfile($userId, $updateData);
        if ($success) {
            $profileUpdateSuccess = true;
            // Update session data
            $_SESSION['user'] = array_merge($_SESSION['user'], $updateData);
            $user = $_SESSION['user'];
        } else {
            $profileErrors['general'] = 'Failed to update profile. Please try again.';
        }
    }
}

// Include header
include_once dirname(__DIR__, 2) . '/includes/header.php';
?>

<!-- Dashboard Hero Section -->
<section class="dashboard-hero">
    <div class="container">
        <div class="hero-content">
            <div class="welcome-section">
                <h1 class="page-title">Welcome back, <?= htmlspecialchars($user['first_name'] ?? 'User') ?>!</h1>
                <p class="page-description">
                    Manage your orders, reservations, and account settings from your personal dashboard.
                </p>
            </div>
            
            <div class="quick-stats">
                <div class="stat-card">
                    <div class="stat-icon orders">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-number"><?= $userStats['total_orders'] ?></span>
                        <span class="stat-label">Total Orders</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon spent">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-number"><?= formatCurrency($userStats['total_spent']) ?></span>
                        <span class="stat-label">Total Spent</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-number"><?= count($userStats['pending_orders']) ?></span>
                        <span class="stat-label">Pending Orders</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon reservations">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-number"><?= count($userStats['upcoming_reservations']) ?></span>
                        <span class="stat-label">Upcoming Reservations</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Dashboard Content -->
<section class="dashboard-content">
    <div class="container">
        <div class="dashboard-grid">
            <!-- Main Content -->
            <div class="main-content">
                <!-- Recent Orders -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>Recent Orders</h3>
                        <a href="<?= BASE_URL ?>/views/user/orders.php" class="view-all-link">
                            View All <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    
                    <div class="card-content">
                        <?php if (empty($recentOrders)): ?>
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <h4>No orders yet</h4>
                                <p>Start exploring our delicious menu and place your first order!</p>
                                <a href="<?= BASE_URL ?>/views/public/menu.php" class="btn btn-primary">
                                    <i class="fas fa-utensils"></i>
                                    Browse Menu
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="orders-list">
                                <?php foreach ($recentOrders as $order): ?>
                                    <div class="order-item">
                                        <div class="order-header">
                                            <div class="order-info">
                                                <span class="order-number">#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></span>
                                                <span class="order-date"><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></span>
                                            </div>
                                            <div class="order-status">
                                                <span class="status-badge status-<?= $order['status'] ?>">
                                                    <?= ucfirst($order['status']) ?>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="order-details">
                                            <div class="order-items">
                                                <span class="items-count"><?= $order['item_count'] ?> item<?= $order['item_count'] !== 1 ? 's' : '' ?></span>
                                                <span class="order-total"><?= formatCurrency($order['total_amount']) ?></span>
                                            </div>
                                            
                                            <div class="order-actions">
                                                <a href="<?= BASE_URL ?>/views/user/order-details.php?id=<?= $order['id'] ?>" 
                                                   class="btn btn-outline btn-sm">
                                                    View Details
                                                </a>
                                                
                                                <?php if ($order['status'] === 'delivered'): ?>
                                                    <button type="button" class="btn btn-primary btn-sm reorder-btn" 
                                                            data-order-id="<?= $order['id'] ?>">
                                                        <i class="fas fa-redo"></i>
                                                        Reorder
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Recent Reservations -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>Recent Reservations</h3>
                        <a href="<?= BASE_URL ?>/views/user/reservations.php" class="view-all-link">
                            View All <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    
                    <div class="card-content">
                        <?php if (empty($recentReservations)): ?>
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <h4>No reservations yet</h4>
                                <p>Book a table and enjoy authentic Himalayan cuisine in our restaurant!</p>
                                <a href="<?= BASE_URL ?>/views/public/reservation.php" class="btn btn-primary">
                                    <i class="fas fa-calendar-check"></i>
                                    Make Reservation
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="reservations-list">
                                <?php foreach ($recentReservations as $reservation): ?>
                                    <div class="reservation-item">
                                        <div class="reservation-date">
                                            <div class="date-display">
                                                <span class="day"><?= date('j', strtotime($reservation['reservation_date'])) ?></span>
                                                <span class="month"><?= date('M', strtotime($reservation['reservation_date'])) ?></span>
                                            </div>
                                        </div>
                                        
                                        <div class="reservation-details">
                                            <div class="reservation-info">
                                                <h4 class="reservation-title">
                                                    Table for <?= $reservation['guests'] ?> 
                                                    <?= $reservation['guests'] === 1 ? 'person' : 'people' ?>
                                                </h4>
                                                <p class="reservation-time">
                                                    <i class="fas fa-clock"></i>
                                                    <?= date('g:i A', strtotime($reservation['reservation_time'])) ?>
                                                </p>
                                                <?php if ($reservation['special_requests']): ?>
                                                    <p class="special-requests">
                                                        <i class="fas fa-comment"></i>
                                                        <?= htmlspecialchars($reservation['special_requests']) ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="reservation-status">
                                                <span class="status-badge status-<?= $reservation['status'] ?>">
                                                    <?= ucfirst($reservation['status']) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Favorite Items -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>Your Favorites</h3>
                        <a href="<?= BASE_URL ?>/views/public/menu.php" class="view-all-link">
                            Browse Menu <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    
                    <div class="card-content">
                        <?php if (empty($favoriteItems)): ?>
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <h4>No favorites yet</h4>
                                <p>Order some dishes and we'll show your most loved items here!</p>
                            </div>
                        <?php else: ?>
                            <div class="favorites-grid">
                                <?php foreach ($favoriteItems as $item): ?>
                                    <div class="favorite-item" data-item-id="<?= $item['id'] ?>">
                                        <div class="item-image">
                                            <img src="<?= ASSETS_URL ?>/images/<?= getItemImage($item['name']) ?>" 
                                                 alt="<?= htmlspecialchars($item['name']) ?>"
                                                 onerror="this.src='<?= ASSETS_URL ?>/images/food.png'">
                                        </div>
                                        
                                        <div class="item-content">
                                            <h4 class="item-name"><?= htmlspecialchars($item['name']) ?></h4>
                                            <p class="item-price"><?= formatCurrency($item['price']) ?></p>
                                            <p class="order-count">Ordered <?= $item['order_count'] ?> times</p>
                                            
                                            <button type="button" class="btn btn-primary btn-sm add-to-cart-btn" 
                                                    data-item-id="<?= $item['id'] ?>">
                                                <i class="fas fa-cart-plus"></i>
                                                Add to Cart
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Profile Card -->
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="profile-info">
                            <h3><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h3>
                            <p><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                    </div>
                    
                    <div class="profile-actions">
                        <button type="button" id="edit-profile-btn" class="btn btn-outline btn-sm">
                            <i class="fas fa-edit"></i>
                            Edit Profile
                        </button>
                        
                    </div>
                </div>
                
                
            
            </div>
        </div>
    </div>
</section>

<!-- Profile Edit Modal -->
<div id="profile-modal" class="modal">
    <div class="modal-overlay"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Profile</h3>
            <button type="button" class="modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-body">
            <?php if ($profileUpdateSuccess): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    Profile updated successfully!
                </div>
            <?php endif; ?>
            
            <form method="POST" id="profile-form">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name" class="form-label">First Name *</label>
                        <input type="text" 
                               id="first_name" 
                               name="first_name" 
                               class="form-input <?= isset($profileErrors['first_name']) ? 'error' : '' ?>"
                               value="<?= htmlspecialchars($user['first_name'] ?? '') ?>"
                               required>
                        <?php if (isset($profileErrors['first_name'])): ?>
                            <span class="error-message"><?= htmlspecialchars($profileErrors['first_name']) ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name" class="form-label">Last Name *</label>
                        <input type="text" 
                               id="last_name" 
                               name="last_name" 
                               class="form-input <?= isset($profileErrors['last_name']) ? 'error' : '' ?>"
                               value="<?= htmlspecialchars($user['last_name'] ?? '') ?>"
                               required>
                        <?php if (isset($profileErrors['last_name'])): ?>
                            <span class="error-message"><?= htmlspecialchars($profileErrors['last_name']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" 
                           id="phone" 
                           name="phone" 
                           class="form-input <?= isset($profileErrors['phone']) ? 'error' : '' ?>"
                           value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    <?php if (isset($profileErrors['phone'])): ?>
                        <span class="error-message"><?= htmlspecialchars($profileErrors['phone']) ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="address" class="form-label">Address</label>
                    <textarea id="address" 
                              name="address" 
                              class="form-input" 
                              rows="3"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                    <input type="date" 
                           id="date_of_birth" 
                           name="date_of_birth" 
                           class="form-input"
                           value="<?= htmlspecialchars($user['date_of_birth'] ?? '') ?>">
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeProfileModal()">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Dashboard Page Specific Styles */
.dashboard-hero {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
    color: var(--white);
    padding: var(--space-12) 0;
}

.hero-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: var(--space-8);
}

.welcome-section {
    flex: 1;
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

.quick-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-4);
}

.stat-card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: var(--radius-xl);
    padding: var(--space-4);
    display: flex;
    align-items: center;
    gap: var(--space-3);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: var(--text-lg);
    color: var(--white);
}

.stat-icon.orders { background: var(--info-color); }
.stat-icon.spent { background: var(--success-color); }
.stat-icon.pending { background: var(--warning-color); }
.stat-icon.reservations { background: var(--secondary-color); }

.stat-content {
    display: flex;
    flex-direction: column;
}

.stat-number {
    font-size: var(--text-xl);
    font-weight: 700;
    color: var(--white);
}

.stat-label {
    font-size: var(--text-sm);
    color: var(--gray-200);
}

.dashboard-content {
    padding: var(--space-16) 0;
    background-color: var(--gray-50);
}

.dashboard-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: var(--space-8);
    align-items: start;
}

.main-content {
    display: flex;
    flex-direction: column;
    gap: var(--space-8);
}

.content-card {
    background-color: var(--white);
    border-radius: var(--radius-2xl);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.card-header {
    padding: var(--space-6);
    border-bottom: 1px solid var(--gray-200);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h3 {
    font-size: var(--text-xl);
    font-weight: 600;
    color: var(--gray-900);
    margin: 0;
}

.view-all-link {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: var(--space-2);
    transition: var(--transition);
}

.view-all-link:hover {
    color: var(--primary-dark);
}

.card-content {
    padding: var(--space-6);
}

.empty-state {
    text-align: center;
    padding: var(--space-8) var(--space-4);
}

.empty-icon {
    font-size: var(--text-4xl);
    color: var(--gray-400);
    margin-bottom: var(--space-4);
}

.empty-state h4 {
    font-size: var(--text-lg);
    color: var(--gray-900);
    margin-bottom: var(--space-3);
}

.empty-state p {
    color: var(--gray-600);
    margin-bottom: var(--space-6);
}

.orders-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.order-item {
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-xl);
    padding: var(--space-4);
    transition: var(--transition);
}

.order-item:hover {
    border-color: var(--primary-color);
    box-shadow: var(--shadow);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-3);
}

.order-number {
    font-weight: 600;
    color: var(--gray-900);
}

.order-date {
    color: var(--gray-500);
    font-size: var(--text-sm);
    margin-left: var(--space-2);
}

.status-badge {
    padding: var(--space-1) var(--space-3);
    border-radius: var(--radius-full);
    font-size: var(--text-xs);
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending { background: var(--warning-light); color: var(--warning-dark); }
.status-confirmed { background: var(--info-light); color: var(--info-dark); }
.status-preparing { background: var(--primary-light); color: var(--primary-dark); }
.status-delivered { background: var(--success-light); color: var(--success-dark); }
.status-cancelled { background: var(--error-light); color: var(--error-dark); }

.order-details {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.order-items {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
}

.items-count {
    color: var(--gray-600);
    font-size: var(--text-sm);
}

.order-total {
    font-weight: 700;
    color: var(--primary-color);
    font-size: var(--text-lg);
}

.order-actions {
    display: flex;
    gap: var(--space-2);
}

.reservations-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.reservation-item {
    display: flex;
    gap: var(--space-4);
    padding: var(--space-4);
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-xl);
    transition: var(--transition);
}

.reservation-item:hover {
    border-color: var(--primary-color);
    box-shadow: var(--shadow);
}

.reservation-date {
    flex-shrink: 0;
}

.date-display {
    width: 60px;
    height: 60px;
    background: var(--primary-color);
    color: var(--white);
    border-radius: var(--radius-xl);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.day {
    font-size: var(--text-xl);
    font-weight: 700;
    line-height: 1;
}

.month {
    font-size: var(--text-xs);
    text-transform: uppercase;
    font-weight: 600;
}

.reservation-details {
    flex: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.reservation-title {
    font-size: var(--text-lg);
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: var(--space-2);
}

.reservation-time, .special-requests {
    color: var(--gray-600);
    font-size: var(--text-sm);
    display: flex;
    align-items: center;
    gap: var(--space-2);
    margin-bottom: var(--space-1);
}

.favorites-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--space-4);
}

.favorite-item {
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-xl);
    overflow: hidden;
    transition: var(--transition);
}

.favorite-item:hover {
    border-color: var(--primary-color);
    box-shadow: var(--shadow);
}

.item-image {
    height: 120px;
    overflow: hidden;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-content {
    padding: var(--space-4);
    text-align: center;
}

.item-name {
    font-size: var(--text-base);
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: var(--space-2);
}

.item-price {
    font-size: var(--text-lg);
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: var(--space-1);
}

.order-count {
    color: var(--gray-500);
    font-size: var(--text-sm);
    margin-bottom: var(--space-3);
}

.sidebar {
    display: flex;
    flex-direction: column;
    gap: var(--space-6);
    position: sticky;
    top: var(--space-8);
}

.profile-card {
    background-color: var(--white);
    border-radius: var(--radius-2xl);
    padding: var(--space-6);
    box-shadow: var(--shadow);
}

.profile-header {
    display: flex;
    align-items: center;
    gap: var(--space-4);
    margin-bottom: var(--space-6);
}

.profile-avatar {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-size: var(--text-xl);
}

.profile-info h3 {
    font-size: var(--text-lg);
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: var(--space-1);
}

.profile-info p {
    color: var(--gray-600);
    font-size: var(--text-sm);
    margin: 0;
}

.profile-actions {
    display: flex;
    gap: var(--space-3);
}

.quick-actions-card {
    background-color: var(--white);
    border-radius: var(--radius-2xl);
    padding: var(--space-6);
    box-shadow: var(--shadow);
}

.quick-actions-card h4 {
    font-size: var(--text-lg);
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: var(--space-4);
}

.action-buttons {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-3);
}

.action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-4);
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-xl);
    text-decoration: none;
    color: var(--gray-700);
    transition: var(--transition);
    text-align: center;
}

.action-btn:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
    box-shadow: var(--shadow);
}

.action-icon {
    width: 40px;
    height: 40px;
    background: var(--gray-100);
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: var(--text-lg);
    transition: var(--transition);
}

.action-btn:hover .action-icon {
    background: var(--primary-light);
    color: var(--primary-color);
}

.action-btn span {
    font-size: var(--text-sm);
    font-weight: 500;
}

.loyalty-card {
    background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));
    color: var(--white);
    border-radius: var(--radius-2xl);
    padding: var(--space-6);
    box-shadow: var(--shadow);
}

.loyalty-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-4);
}

.loyalty-header h4 {
    font-size: var(--text-lg);
    font-weight: 600;
    margin: 0;
}

.points-display {
    text-align: right;
}

.points-value {
    font-size: var(--text-2xl);
    font-weight: 700;
    display: block;
}

.points-label {
    font-size: var(--text-sm);
    opacity: 0.9;
}

.loyalty-progress {
    margin-bottom: var(--space-4);
}

.progress-bar {
    width: 100%;
    height: 8px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: var(--radius-full);
    overflow: hidden;
    margin-bottom: var(--space-2);
}

.progress-fill {
    height: 100%;
    background: var(--white);
    border-radius: var(--radius-full);
    transition: var(--transition);
}

.progress-text {
    font-size: var(--text-sm);
    opacity: 0.9;
    margin: 0;
}

/* Modal Styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
    display: none;
}

.modal.active {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(5px);
}

.modal-content {
    position: relative;
    background: var(--white);
    border-radius: var(--radius-2xl);
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: var(--shadow-2xl);
}

.modal-header {
    padding: var(--space-6);
    border-bottom: 1px solid var(--gray-200);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    font-size: var(--text-xl);
    font-weight: 600;
    color: var(--gray-900);
    margin: 0;
}

.modal-close {
    width: 40px;
    height: 40px;
    border: none;
    background: var(--gray-100);
    border-radius: var(--radius-full);
    color: var(--gray-600);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
}

.modal-close:hover {
    background: var(--error-color);
    color: var(--white);
}

.modal-body {
    padding: var(--space-6);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-4);
}

.form-group {
    margin-bottom: var(--space-4);
}

.form-label {
    display: block;
    font-weight: 600;
    color: var(--gray-700);
    margin-bottom: var(--space-2);
}

.form-input {
    width: 100%;
    padding: var(--space-3);
    border: 2px solid var(--gray-300);
    border-radius: var(--radius);
    font-size: var(--text-base);
    transition: var(--transition);
}

.form-input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
}

.form-input.error {
    border-color: var(--error-color);
}

.error-message {
    color: var(--error-color);
    font-size: var(--text-sm);
    margin-top: var(--space-1);
    display: block;
}

.modal-actions {
    display: flex;
    gap: var(--space-3);
    justify-content: flex-end;
    margin-top: var(--space-6);
    padding-top: var(--space-4);
    border-top: 1px solid var(--gray-200);
}

.alert {
    padding: var(--space-4);
    border-radius: var(--radius);
    margin-bottom: var(--space-4);
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.alert-success {
    background: var(--success-light);
    color: var(--success-dark);
    border-left: 4px solid var(--success-color);
}

/* Responsive Design */
@media (max-width: 968px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
        gap: var(--space-6);
    }
    
    .sidebar {
        position: static;
    }
    
    .hero-content {
        flex-direction: column;
        gap: var(--space-6);
        text-align: center;
    }
    
    .quick-stats {
        grid-template-columns: repeat(4, 1fr);
        gap: var(--space-3);
    }
}

@media (max-width: 768px) {
    .page-title {
        font-size: var(--text-3xl);
    }
    
    .quick-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .stat-card {
        flex-direction: column;
        text-align: center;
        gap: var(--space-2);
    }
    
    .order-details {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--space-3);
    }
    
    .order-actions {
        flex-direction: column;
        width: 100%;
    }
    
    .reservation-details {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--space-2);
    }
    
    .profile-actions {
        flex-direction: column;
    }
    
    .action-buttons {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: var(--space-3);
    }
    
    .modal-actions {
        flex-direction: column;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Profile modal functionality
    const editProfileBtn = document.getElementById('edit-profile-btn');
    const profileModal = document.getElementById('profile-modal');
    const modalClose = document.querySelector('.modal-close');
    const modalOverlay = document.querySelector('.modal-overlay');
    
    editProfileBtn.addEventListener('click', openProfileModal);
    modalClose.addEventListener('click', closeProfileModal);
    modalOverlay.addEventListener('click', closeProfileModal);
    
    // Reorder functionality
    const reorderBtns = document.querySelectorAll('.reorder-btn');
    reorderBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            reorderItems(orderId);
        });
    });
    
    // Add to cart functionality
    const addToCartBtns = document.querySelectorAll('.add-to-cart-btn');
    addToCartBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            addToCart(itemId, 1);
        });
    });
    
    function openProfileModal() {
        profileModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    function closeProfileModal() {
        profileModal.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    function reorderItems(orderId) {
        // Show loading state
        event.target.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
        event.target.disabled = true;
        
        // In a real app, this would be an AJAX call to get order items and add to cart
        fetch(`<?= BASE_URL ?>/api/reorder.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ order_id: orderId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Items added to cart successfully!', 'success');
                // Update cart count in header
                updateCartCount();
            } else {
                showToast('Failed to add items to cart', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
        })
        .finally(() => {
            // Reset button
            event.target.innerHTML = '<i class="fas fa-redo"></i> Reorder';
            event.target.disabled = false;
        });
    }
    
    function addToCart(itemId, quantity) {
        // Show loading state
        event.target.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
        event.target.disabled = true;
        
        fetch(`<?= BASE_URL ?>/api/cart.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                action: 'add',
                item_id: itemId,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Item added to cart!', 'success');
                updateCartCount();
            } else {
                showToast('Failed to add item to cart', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
        })
        .finally(() => {
            // Reset button
            event.target.innerHTML = '<i class="fas fa-cart-plus"></i> Add to Cart';
            event.target.disabled = false;
        });
    }
    
    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && profileModal.classList.contains('active')) {
            closeProfileModal();
        }
    });
});

// Global functions for modal
function openProfileModal() {
    document.getElementById('profile-modal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeProfileModal() {
    document.getElementById('profile-modal').classList.remove('active');
    document.body.style.overflow = '';
}
</script>

<?php include_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
