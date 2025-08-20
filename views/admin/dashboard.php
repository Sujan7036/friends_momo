<?php
/**
 * Admin Dashboard
 * Friends and Momos Restaurant Management System
 */

// Page configuration
$pageTitle = "Admin Dashboard - Friends and Momos | Restaurant Management";
$bodyClass = "admin-dashboard";

// Load required files
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/models/User.php';
require_once dirname(__DIR__, 2) . '/models/Order.php';
require_once dirname(__DIR__, 2) . '/models/Reservation.php';
require_once dirname(__DIR__, 2) . '/models/MenuItem.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ' . BASE_URL . '/views/public/login.php?redirect=admin');
    exit;
}

// Initialize models
$userModel = new User();
$orderModel = new Order();
$reservationModel = new Reservation();
$menuItemModel = new MenuItem();

// Get date range for statistics (default: last 30 days)
$dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
$dateTo = $_GET['date_to'] ?? date('Y-m-d');

// Get statistics
$orderStats = $orderModel->getOrderStatistics($dateFrom, $dateTo);
$reservationStats = $reservationModel->getReservationStatistics($dateFrom, $dateTo);
$dailySales = $orderModel->getDailySales(30);
$popularItems = $orderModel->getPopularMenuItems(10, $dateFrom, $dateTo);
$todaysReservations = $reservationModel->getTodaysReservations();
$recentOrders = $orderModel->getAllOrders(1, 10)['orders'];

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

// Calculate growth metrics
$previousPeriod = [
    'from' => date('Y-m-d', strtotime($dateFrom . ' -30 days')),
    'to' => date('Y-m-d', strtotime($dateTo . ' -30 days'))
];
$previousOrderStats = $orderModel->getOrderStatistics($previousPeriod['from'], $previousPeriod['to']);

$revenueGrowth = $previousOrderStats['total_revenue'] > 0 
    ? (($orderStats['total_revenue'] - $previousOrderStats['total_revenue']) / $previousOrderStats['total_revenue']) * 100 
    : 0;

$orderGrowth = $previousOrderStats['total_orders'] > 0 
    ? (($orderStats['total_orders'] - $previousOrderStats['total_orders']) / $previousOrderStats['total_orders']) * 100 
    : 0;

// Include admin header
include_once dirname(__DIR__, 2) . '/includes/admin_header.php';
?>

<!-- Admin Dashboard Hero -->
<section class="admin-hero">
    <div class="container">
        <div class="hero-content">
            <div class="welcome-section">
                <h1 class="page-title">Restaurant Dashboard</h1>
                <p class="page-description">
                    Monitor your restaurant's performance, manage orders, and track key metrics.
                </p>
            </div>
            
            <div class="date-filter">
                <form method="GET" class="filter-form">
                    <div class="date-inputs">
                        <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>" class="form-input">
                        <span class="date-separator">to</span>
                        <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>" class="form-input">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i>
                        Filter
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Overview -->
<section class="stats-overview">
    <div class="container">
        <div class="stats-grid">
            <!-- Revenue Card -->
            <div class="stat-card revenue-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Revenue</h3>
                        <p class="stat-value"><?= formatCurrency($orderStats['total_revenue']) ?></p>
                        <div class="stat-growth <?= $revenueGrowth >= 0 ? 'positive' : 'negative' ?>">
                            <i class="fas fa-arrow-<?= $revenueGrowth >= 0 ? 'up' : 'down' ?>"></i>
                            <?= abs(round($revenueGrowth, 1)) ?>% vs last period
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Orders Card -->
            <div class="stat-card orders-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Orders</h3>
                        <p class="stat-value"><?= number_format($orderStats['total_orders']) ?></p>
                        <div class="stat-growth <?= $orderGrowth >= 0 ? 'positive' : 'negative' ?>">
                            <i class="fas fa-arrow-<?= $orderGrowth >= 0 ? 'up' : 'down' ?>"></i>
                            <?= abs(round($orderGrowth, 1)) ?>% vs last period
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Average Order Value -->
            <div class="stat-card aov-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Avg Order Value</h3>
                        <p class="stat-value"><?= formatCurrency($orderStats['average_order_value']) ?></p>
                        <div class="stat-breakdown">
                            From <?= number_format($orderStats['total_orders']) ?> orders
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Reservations -->
            <div class="stat-card reservations-card">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Reservations</h3>
                        <p class="stat-value"><?= number_format($reservationStats['total_reservations']) ?></p>
                        <div class="stat-breakdown">
                            <?= number_format($reservationStats['total_guests']) ?> total guests
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Dashboard Content -->
<section class="dashboard-content">
    <div class="container">
        <div class="content-grid">
            <!-- Main Content Area -->
            <div class="main-content">
                <!-- Sales Chart -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>Sales Trend (Last 30 Days)</h3>
                        <div class="chart-controls">
                            <button type="button" class="btn btn-outline btn-sm active" data-period="30">30 Days</button>
                            <button type="button" class="btn btn-outline btn-sm" data-period="7">7 Days</button>
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="chart-container">
                            <canvas id="sales-chart" width="400" height="150"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Orders -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>Recent Orders</h3>
                        <a href="<?= BASE_URL ?>/views/admin/orders.php" class="view-all-link">
                            View All <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="card-content">
                        <div class="orders-table">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Items</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Time</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td>
                                                <span class="order-number">#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></span>
                                            </td>
                                            <td>
                                                <div class="customer-info">
                                                    <span class="customer-name"><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></span>
                                                    <span class="customer-email"><?= htmlspecialchars($order['email']) ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="item-count"><?= $order['item_count'] ?> item<?= $order['item_count'] !== 1 ? 's' : '' ?></span>
                                            </td>
                                            <td>
                                                <span class="amount"><?= formatCurrency($order['final_amount']) ?></span>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?= $order['status'] ?>">
                                                    <?= ucfirst($order['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="order-time" title="<?= date('F j, Y g:i A', strtotime($order['created_at'])) ?>">
                                                    <?= timeAgo($order['created_at']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="<?= BASE_URL ?>/views/admin/order-details.php?id=<?= $order['id'] ?>" 
                                                       class="btn btn-outline btn-xs">
                                                        View
                                                    </a>
                                                    <?php if ($order['status'] === 'pending'): ?>
                                                        <button type="button" 
                                                                class="btn btn-primary btn-xs confirm-order-btn"
                                                                data-order-id="<?= $order['id'] ?>">
                                                            Confirm
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Popular Menu Items -->
                <div class="content-card">
                    <div class="card-header">
                        <h3>Popular Menu Items</h3>
                        <a href="<?= BASE_URL ?>/views/admin/menu.php" class="view-all-link">
                            Manage Menu <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="card-content">
                        <div class="popular-items-grid">
                            <?php foreach ($popularItems as $item): ?>
                                <div class="popular-item">
                                    <div class="item-image">
                                        <img src="<?= ASSETS_URL ?>/images/<?= getItemImage($item['name']) ?>" 
                                             alt="<?= htmlspecialchars($item['name']) ?>"
                                             onerror="this.src='<?= ASSETS_URL ?>/images/food.png'">
                                        <div class="item-rank">#<?= array_search($item, $popularItems) + 1 ?></div>
                                    </div>
                                    <div class="item-details">
                                        <h4 class="item-name"><?= htmlspecialchars($item['name']) ?></h4>
                                        <div class="item-stats">
                                            <span class="quantity-sold">
                                                <i class="fas fa-shopping-cart"></i>
                                                <?= number_format($item['total_quantity']) ?> sold
                                            </span>
                                            <span class="revenue">
                                                <i class="fas fa-dollar-sign"></i>
                                                <?= formatCurrency($item['total_revenue']) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Order Status Overview -->
                <div class="sidebar-card">
                    <h4>Order Status Overview</h4>
                    <div class="status-overview">
                        <div class="status-item">
                            <div class="status-indicator pending"></div>
                            <span class="status-label">Pending</span>
                            <span class="status-count"><?= $orderStats['pending_orders'] ?></span>
                        </div>
                        <div class="status-item">
                            <div class="status-indicator confirmed"></div>
                            <span class="status-label">Confirmed</span>
                            <span class="status-count"><?= $orderStats['confirmed_orders'] ?></span>
                        </div>
                        <div class="status-item">
                            <div class="status-indicator preparing"></div>
                            <span class="status-label">Preparing</span>
                            <span class="status-count"><?= $orderStats['preparing_orders'] ?></span>
                        </div>
                        <div class="status-item">
                            <div class="status-indicator delivered"></div>
                            <span class="status-label">Delivered</span>
                            <span class="status-count"><?= $orderStats['delivered_orders'] ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Today's Reservations -->
                <div class="sidebar-card">
                    <h4>Today's Reservations</h4>
                    <div class="reservations-list">
                        <?php if (empty($todaysReservations)): ?>
                            <div class="empty-state">
                                <i class="fas fa-calendar-alt"></i>
                                <p>No reservations today</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($todaysReservations as $reservation): ?>
                                <div class="reservation-item">
                                    <div class="reservation-time">
                                        <?= date('g:i A', strtotime($reservation['reservation_time'])) ?>
                                    </div>
                                    <div class="reservation-details">
                                        <span class="guest-name"><?= htmlspecialchars($reservation['guest_name']) ?></span>
                                        <span class="guest-count"><?= $reservation['guests'] ?> guest<?= $reservation['guests'] !== 1 ? 's' : '' ?></span>
                                    </div>
                                    <div class="reservation-status">
                                        <span class="status-badge status-<?= $reservation['status'] ?>">
                                            <?= ucfirst($reservation['status']) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <a href="<?= BASE_URL ?>/views/admin/reservations.php" class="btn btn-outline btn-sm">
                        View All Reservations
                    </a>
                </div>
                

            </div>
        </div>
    </div>
</section>

<style>
/* Admin Dashboard Specific Styles */
.admin-hero {
    background: linear-gradient(135deg, var(--gray-900) 0%, var(--gray-800) 100%);
    color: var(--white);
    padding: var(--space-8) 0;
    border-bottom: 1px solid var(--gray-700);
}

.hero-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: var(--space-8);
}

.welcome-section h1 {
    font-size: var(--text-3xl);
    font-weight: 700;
    margin-bottom: var(--space-2);
    color: var(--white);
}

.page-description {
    color: var(--gray-300);
    font-size: var(--text-lg);
}

.date-filter .filter-form {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.date-inputs {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.date-separator {
    color: var(--gray-400);
    font-weight: 500;
}

.stats-overview {
    padding: var(--space-8) 0;
    background: var(--gray-50);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--space-6);
}

.stat-card {
    background: var(--white);
    border-radius: var(--radius-xl);
    padding: var(--space-6);
    box-shadow: var(--shadow);
    border-left: 4px solid var(--gray-300);
    transition: var(--transition);
}

.stat-card:hover {
    box-shadow: var(--shadow-lg);
    transform: translateY(-2px);
}

.revenue-card { border-left-color: var(--success-color); }
.orders-card { border-left-color: var(--primary-color); }
.aov-card { border-left-color: var(--info-color); }
.reservations-card { border-left-color: var(--secondary-color); }

.stat-header {
    display: flex;
    align-items: flex-start;
    gap: var(--space-4);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: var(--text-xl);
    color: var(--white);
}

.revenue-card .stat-icon { background: var(--success-color); }
.orders-card .stat-icon { background: var(--primary-color); }
.aov-card .stat-icon { background: var(--info-color); }
.reservations-card .stat-icon { background: var(--secondary-color); }

.stat-info {
    flex: 1;
}

.stat-info h3 {
    font-size: var(--text-sm);
    font-weight: 600;
    color: var(--gray-600);
    margin-bottom: var(--space-2);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-value {
    font-size: var(--text-3xl);
    font-weight: 700;
    color: var(--gray-900);
    margin-bottom: var(--space-2);
}

.stat-growth {
    font-size: var(--text-sm);
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: var(--space-1);
}

.stat-growth.positive { color: var(--success-color); }
.stat-growth.negative { color: var(--error-color); }

.stat-breakdown {
    font-size: var(--text-sm);
    color: var(--gray-500);
}

.dashboard-content {
    padding: var(--space-8) 0;
    background: var(--gray-50);
}

.content-grid {
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
    background: var(--white);
    border-radius: var(--radius-xl);
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

.chart-controls {
    display: flex;
    gap: var(--space-2);
}

.card-content {
    padding: var(--space-6);
}

.chart-container {
    position: relative;
    height: 300px;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
}

.data-table th {
    background: var(--gray-50);
    padding: var(--space-3);
    text-align: left;
    font-weight: 600;
    color: var(--gray-700);
    border-bottom: 1px solid var(--gray-200);
    font-size: var(--text-sm);
}

.data-table td {
    padding: var(--space-4) var(--space-3);
    border-bottom: 1px solid var(--gray-100);
    vertical-align: middle;
}

.order-number {
    font-weight: 600;
    color: var(--gray-900);
}

.customer-info {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
}

.customer-name {
    font-weight: 500;
    color: var(--gray-900);
}

.customer-email {
    font-size: var(--text-sm);
    color: var(--gray-500);
}

.item-count {
    color: var(--gray-600);
    font-size: var(--text-sm);
}

.amount {
    font-weight: 600;
    color: var(--primary-color);
}

.order-time {
    color: var(--gray-500);
    font-size: var(--text-sm);
}

.action-buttons {
    display: flex;
    gap: var(--space-2);
}

.popular-items-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--space-4);
}

.popular-item {
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-xl);
    overflow: hidden;
    transition: var(--transition);
}

.popular-item:hover {
    box-shadow: var(--shadow);
    border-color: var(--primary-color);
}

.item-image {
    position: relative;
    height: 120px;
    overflow: hidden;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-rank {
    position: absolute;
    top: var(--space-2);
    left: var(--space-2);
    width: 30px;
    height: 30px;
    background: var(--primary-color);
    color: var(--white);
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: var(--text-sm);
}

.item-details {
    padding: var(--space-4);
}

.item-name {
    font-size: var(--text-base);
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: var(--space-3);
}

.item-stats {
    display: flex;
    justify-content: space-between;
    gap: var(--space-2);
}

.quantity-sold, .revenue {
    display: flex;
    align-items: center;
    gap: var(--space-1);
    font-size: var(--text-sm);
    color: var(--gray-600);
}

.sidebar {
    display: flex;
    flex-direction: column;
    gap: var(--space-6);
    position: sticky;
    top: var(--space-8);
}

.sidebar-card {
    background: var(--white);
    border-radius: var(--radius-xl);
    padding: var(--space-6);
    box-shadow: var(--shadow);
}

.sidebar-card h4 {
    font-size: var(--text-lg);
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: var(--space-4);
}

.status-overview {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.status-item {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: var(--radius-full);
}

.status-indicator.pending { background: var(--warning-color); }
.status-indicator.confirmed { background: var(--info-color); }
.status-indicator.preparing { background: var(--primary-color); }
.status-indicator.delivered { background: var(--success-color); }

.status-label {
    flex: 1;
    color: var(--gray-700);
    font-weight: 500;
}

.status-count {
    font-weight: 600;
    color: var(--gray-900);
}

.reservations-list {
    margin-bottom: var(--space-4);
}

.reservation-item {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-3) 0;
    border-bottom: 1px solid var(--gray-100);
}

.reservation-item:last-child {
    border-bottom: none;
}

.reservation-time {
    font-weight: 600;
    color: var(--primary-color);
    min-width: 70px;
}

.reservation-details {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
}

.guest-name {
    font-weight: 500;
    color: var(--gray-900);
}

.guest-count {
    font-size: var(--text-sm);
    color: var(--gray-500);
}

.empty-state {
    text-align: center;
    padding: var(--space-6);
    color: var(--gray-500);
}

.empty-state i {
    font-size: var(--text-2xl);
    margin-bottom: var(--space-2);
}

.quick-actions {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.action-btn {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-3);
    border: 1px solid var(--gray-200);
    border-radius: var(--radius);
    text-decoration: none;
    color: var(--gray-700);
    transition: var(--transition);
    position: relative;
}

.action-btn:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
    background: var(--primary-light);
}

.action-btn.urgent {
    border-color: var(--warning-color);
    background: var(--warning-light);
    color: var(--warning-dark);
}

.action-btn i {
    font-size: var(--text-lg);
}

.action-btn span:first-of-type {
    flex: 1;
    font-weight: 500;
}

.action-count {
    background: var(--primary-color);
    color: var(--white);
    font-size: var(--text-xs);
    font-weight: 600;
    padding: var(--space-1) var(--space-2);
    border-radius: var(--radius-full);
    min-width: 20px;
    text-align: center;
}

.action-btn.urgent .action-count {
    background: var(--warning-color);
}

/* Responsive Design */
@media (max-width: 968px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
    
    .hero-content {
        flex-direction: column;
        gap: var(--space-4);
        text-align: center;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .popular-items-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .data-table {
        font-size: var(--text-sm);
    }
    
    .data-table th,
    .data-table td {
        padding: var(--space-2);
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .date-inputs {
        flex-direction: column;
        gap: var(--space-2);
    }
    
    .filter-form {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize sales chart
    initializeSalesChart();
    
    // Chart period controls
    const chartControls = document.querySelectorAll('.chart-controls button');
    chartControls.forEach(btn => {
        btn.addEventListener('click', function() {
            chartControls.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            updateSalesChart(this.dataset.period);
        });
    });
    
    // Confirm order buttons
    const confirmOrderBtns = document.querySelectorAll('.confirm-order-btn');
    confirmOrderBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            confirmOrder(orderId, this);
        });
    });
    
    function initializeSalesChart() {
        const ctx = document.getElementById('sales-chart').getContext('2d');
        const salesData = <?= json_encode(array_reverse($dailySales)) ?>;
        
        const labels = salesData.map(item => {
            const date = new Date(item.date);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        });
        
        const revenueData = salesData.map(item => parseFloat(item.revenue));
        const orderData = salesData.map(item => parseInt(item.order_count));
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue ($)',
                    data: revenueData,
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    yAxisID: 'y',
                    tension: 0.4
                }, {
                    label: 'Orders',
                    data: orderData,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    yAxisID: 'y1',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Revenue ($)'
                        },
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Orders'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (context.datasetIndex === 0) {
                                    return 'Revenue: $' + context.parsed.y.toFixed(2);
                                } else {
                                    return 'Orders: ' + context.parsed.y;
                                }
                            }
                        }
                    }
                }
            }
        });
    }
    
    function updateSalesChart(period) {
        // In a real app, this would fetch new data via AJAX
        console.log('Updating chart for period:', period);
    }
    
    function confirmOrder(orderId, button) {
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        button.disabled = true;
        
        fetch(`<?= BASE_URL ?>/api/orders.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'update_status',
                order_id: orderId,
                status: 'confirmed'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update status badge
                const statusBadge = button.closest('tr').querySelector('.status-badge');
                statusBadge.className = 'status-badge status-confirmed';
                statusBadge.textContent = 'Confirmed';
                
                // Remove confirm button
                button.remove();
                
                showToast('Order confirmed successfully!', 'success');
            } else {
                showToast('Failed to confirm order', 'error');
                button.innerHTML = originalText;
                button.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }
});
</script>

<?php include_once dirname(__DIR__, 2) . '/includes/admin_footer.php'; ?>
