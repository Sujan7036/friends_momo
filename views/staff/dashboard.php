<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/models/Order.php';
require_once dirname(__DIR__, 2) . '/models/Reservation.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

// Start session and check staff access
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !in_array((isset($_SESSION['user_type']) ? $_SESSION['user_type'] : $_SESSION['user']['role']), ['staff', 'admin'])) {
    header('Location: ../public/login.php');
    exit();
}

try {
    $orderModel = new Order();
    $reservationModel = new Reservation();

    // Get staff-specific data
    $todayOrders = $orderModel->getTodayOrders();
    $pendingOrders = $orderModel->getPendingOrders();
    $todayReservations = $reservationModel->getTodayReservations();
    $activeOrders = $orderModel->getActiveOrders();
} catch (Exception $e) {
    // If there's an error, initialize empty arrays
    $todayOrders = [];
    $pendingOrders = [];
    $todayReservations = [];
    $activeOrders = [];
    $error = "Error loading dashboard data: " . $e->getMessage();
}

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $orderId = $_POST['order_id'] ?? 0;
    $action = $_POST['action'];
    
    switch ($action) {
        case 'mark_ready':
            $orderModel->updateStatus($orderId, 'ready');
            $success = "Order marked as ready.";
            break;
            
        case 'mark_delivered':
            $orderModel->updateStatus($orderId, 'delivered');
            $success = "Order marked as delivered.";
            break;
            
        case 'start_preparing':
            $orderModel->updateStatus($orderId, 'preparing');
            $success = "Order preparation started.";
            break;
    }
    
    // Refresh data after update
    $pendingOrders = $orderModel->getPendingOrders();
    $activeOrders = $orderModel->getActiveOrders();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - Friends & Momos</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= ASSETS_URL ?>/css/staff.css" rel="stylesheet">
</head>
<body>
    <div class="staff-container">
        <!-- Header -->
        <header class="staff-header">
            <div class="header-left">
                <img src="<?= ASSETS_URL ?>/images/logo.png" alt="Friends & Momos" class="header-logo">
                <h1>Staff Dashboard</h1>
            </div>
            
            <div class="header-right">
                <div class="current-time" id="currentTime"></div>
                <div class="user-info">
                    <span class="user-name"><?= htmlspecialchars((isset($_SESSION['user_name']) ? $_SESSION['user_name'] : ($_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name']))) ?></span>
                    <span class="user-role">Staff Member</span>
                </div>
                <a href="../../logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </header>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <!-- Quick Stats -->
        <div class="stats-section">
            <div class="stat-card pending">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?= count($pendingOrders) ?></h3>
                    <p>Pending Orders</p>
                </div>
            </div>
            
            <div class="stat-card preparing">
                <div class="stat-icon">
                    <i class="fas fa-utensils"></i>
                </div>
                <div class="stat-info">
                    <h3><?= count($activeOrders) ?></h3>
                    <p>Active Orders</p>
                </div>
            </div>
            
            <div class="stat-card reservations">
                <div class="stat-icon">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="stat-info">
                    <h3><?= count($todayReservations) ?></h3>
                    <p>Today's Reservations</p>
                </div>
            </div>
            
            <div class="stat-card completed">
                <div class="stat-icon">
                    <i class="fas fa-check"></i>
                </div>
                <div class="stat-info">
                    <h3><?= count($todayOrders) ?></h3>
                    <p>Today's Orders</p>
                </div>
            </div>
        </div>
        
        <div class="dashboard-content">
            <!-- Pending Orders -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-clock"></i> Pending Orders</h2>
                    <button class="refresh-btn" onclick="refreshPendingOrders()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                
                <div class="orders-grid">
                    <?php foreach ($pendingOrders as $order): ?>
                        <div class="order-card pending">
                            <div class="order-header">
                                <span class="order-id">#<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></span>
                                <span class="order-time"><?= date('g:i A', strtotime($order['created_at'])) ?></span>
                            </div>
                            
                            <div class="order-customer">
                                <strong><?= htmlspecialchars($order['customer_name']) ?></strong>
                                <span class="order-type"><?= ucfirst(str_replace('_', ' ', $order['order_type'])) ?></span>
                            </div>
                            
                            <div class="order-items">
                                <button class="view-items-btn" onclick="viewOrderItems(<?= $order['id'] ?>)">
                                    <i class="fas fa-list"></i> View Items
                                </button>
                            </div>
                            
                            <div class="order-total">
                                <strong><?= formatCurrency($order['total_amount']) ?></strong>
                            </div>
                            
                            <div class="order-actions">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <input type="hidden" name="action" value="start_preparing">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-play"></i> Start Preparing
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($pendingOrders)): ?>
                        <div class="no-orders">
                            <i class="fas fa-check-circle"></i>
                            <p>No pending orders</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Active Orders -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-utensils"></i> Active Orders</h2>
                    <button class="refresh-btn" onclick="refreshActiveOrders()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                
                <div class="orders-grid">
                    <?php foreach ($activeOrders as $order): ?>
                        <div class="order-card <?= $order['status'] ?>">
                            <div class="order-header">
                                <span class="order-id">#<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></span>
                                <span class="order-status"><?= ucfirst($order['status']) ?></span>
                            </div>
                            
                            <div class="order-customer">
                                <strong><?= htmlspecialchars($order['customer_name']) ?></strong>
                                <span class="order-type"><?= ucfirst(str_replace('_', ' ', $order['order_type'])) ?></span>
                            </div>
                            
                            <div class="order-timer">
                                <i class="fas fa-stopwatch"></i>
                                <span class="timer" data-start="<?= strtotime($order['updated_at']) ?>"></span>
                            </div>
                            
                            <div class="order-items">
                                <button class="view-items-btn" onclick="viewOrderItems(<?= $order['id'] ?>)">
                                    <i class="fas fa-list"></i> View Items
                                </button>
                            </div>
                            
                            <div class="order-actions">
                                <?php if ($order['status'] === 'preparing'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <input type="hidden" name="action" value="mark_ready">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-check"></i> Mark Ready
                                        </button>
                                    </form>
                                <?php elseif ($order['status'] === 'ready'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <input type="hidden" name="action" value="mark_delivered">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-truck"></i> Mark Delivered
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($activeOrders)): ?>
                        <div class="no-orders">
                            <i class="fas fa-coffee"></i>
                            <p>No active orders</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Today's Reservations -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-calendar"></i> Today's Reservations</h2>
                </div>
                
                <div class="reservations-list">
                    <?php foreach ($todayReservations as $reservation): ?>
                        <div class="reservation-item">
                            <div class="reservation-time">
                                <i class="fas fa-clock"></i>
                                <?= date('g:i A', strtotime($reservation['reservation_time'])) ?>
                            </div>
                            
                            <div class="reservation-details">
                                <strong><?= htmlspecialchars($reservation['customer_name']) ?></strong>
                                <span><?= $reservation['party_size'] ?> guests</span>
                                <?php if ($reservation['special_requests']): ?>
                                    <small><?= htmlspecialchars($reservation['special_requests']) ?></small>
                                <?php endif; ?>
                            </div>
                            
                            <div class="reservation-status">
                                <span class="status-badge <?= $reservation['status'] ?>">
                                    <?= ucfirst($reservation['status']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($todayReservations)): ?>
                        <div class="no-reservations">
                            <i class="fas fa-calendar-times"></i>
                            <p>No reservations for today</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Order Items Modal -->
    <div id="orderItemsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Order Items</h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body" id="orderItemsContent">
                <!-- Order items will be loaded here -->
            </div>
        </div>
    </div>
    
    <script src="<?= ASSETS_URL ?>/js/staff-dashboard.js"></script>
</body>
</html>
