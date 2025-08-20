<?php
/**
 * User Orders Page
 * Friends and Momos Restaurant Management System
 */

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/models/Order.php';
require_once dirname(__DIR__, 2) . '/models/OrderItem.php';
require_once dirname(__DIR__, 2) . '/models/User.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

// Start session and check user access
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/login.php');
    exit();
}

$orderModel = new Order();
$orderItemModel = new OrderItem();
$userModel = new User();
$userId = $_SESSION['user_id'];

// Get filter parameters
$status = $_GET['status'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// Build filter conditions
$filters = [];
if ($status) {
    $filters['status'] = $status;
}
if ($dateFrom) {
    $filters['date_from'] = $dateFrom;
}
if ($dateTo) {
    $filters['date_to'] = $dateTo;
}

// Get orders with pagination
$orders = $orderModel->getUserOrdersWithPagination($userId, $filters, $limit, $offset);
$totalOrders = $orderModel->getUserOrderCount($userId, $filters);
$totalPages = ceil($totalOrders / $limit);

// Handle order actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $orderId = intval($_POST['order_id'] ?? 0);
    
    if ($action === 'cancel_order' && $orderId) {
        $order = $orderModel->find($orderId);
        if ($order && $order['user_id'] == $userId && in_array($order['status'], ['pending', 'confirmed'])) {
            if ($orderModel->updateStatus($orderId, 'cancelled')) {
                $success = "Order #" . str_pad($orderId, 4, '0', STR_PAD_LEFT) . " has been cancelled.";
            } else {
                $error = "Failed to cancel order. Please try again.";
            }
        } else {
            $error = "Order cannot be cancelled.";
        }
        
        // Refresh orders after action
        $orders = $orderModel->getUserOrdersWithPagination($userId, $filters, $limit, $offset);
    }
}

// Get order statistics
$orderStats = $orderModel->getUserOrderStats($userId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Friends & Momos</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= ASSETS_URL ?>/css/user.css" rel="stylesheet">
</head>
<body>
    <div class="user-container">
        <!-- Navigation -->
        <nav class="user-nav">
            <div class="nav-brand">
                <img src="<?= ASSETS_URL ?>/images/logo.png" alt="Friends & Momos">
                <span>Friends & Momos</span>
            </div>
            
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="../../index.php" class="nav-link">
                    <i class="fas fa-home"></i> Home
                </a>
                <a href="../../menu.php" class="nav-link">
                    <i class="fas fa-utensils"></i> Menu
                </a>
                <a href="../../logout.php" class="nav-link logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </nav>
        
        <!-- Main Content -->
        <div class="orders-main">
            <!-- Header -->
            <header class="page-header">
                <div class="header-content">
                    <h1><i class="fas fa-shopping-bag"></i> My Orders</h1>
                    <p>Track and manage your order history</p>
                </div>
                
                <div class="header-actions">
                    <a href="../../menu.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Order
                    </a>
                </div>
            </header>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <!-- Order Statistics -->
            <div class="order-stats">
                <div class="stat-card">
                    <div class="stat-icon total">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $orderStats['total_orders'] ?? 0 ?></h3>
                        <p>Total Orders</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon spent">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= formatCurrency($orderStats['total_spent'] ?? 0) ?></h3>
                        <p>Total Spent</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $orderStats['pending_orders'] ?? 0 ?></h3>
                        <p>Pending Orders</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon completed">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $orderStats['completed_orders'] ?? 0 ?></h3>
                        <p>Completed Orders</p>
                    </div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="filters-section">
                <form method="GET" class="filters-form">
                    <div class="filter-group">
                        <label for="status">Status:</label>
                        <select name="status" id="status">
                            <option value="">All Status</option>
                            <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="confirmed" <?= $status === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                            <option value="preparing" <?= $status === 'preparing' ? 'selected' : '' ?>>Preparing</option>
                            <option value="ready" <?= $status === 'ready' ? 'selected' : '' ?>>Ready</option>
                            <option value="delivered" <?= $status === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                            <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="date_from">From Date:</label>
                        <input type="date" name="date_from" id="date_from" value="<?= htmlspecialchars($dateFrom) ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label for="date_to">To Date:</label>
                        <input type="date" name="date_to" id="date_to" value="<?= htmlspecialchars($dateTo) ?>">
                    </div>
                    
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="orders.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Orders List -->
            <div class="orders-section">
                <?php if (!empty($orders)): ?>
                    <div class="orders-grid">
                        <?php foreach ($orders as $order): ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <div class="order-id">
                                        <h3>#<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></h3>
                                        <span class="order-date"><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></span>
                                    </div>
                                    
                                    <div class="order-status">
                                        <span class="status-badge <?= $order['status'] ?>">
                                            <i class="fas fa-circle"></i>
                                            <?= ucfirst($order['status']) ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="order-details">
                                    <div class="order-info">
                                        <div class="info-item">
                                            <span class="label">Type:</span>
                                            <span class="value"><?= ucfirst(str_replace('_', ' ', $order['order_type'])) ?></span>
                                        </div>
                                        
                                        <div class="info-item">
                                            <span class="label">Total:</span>
                                            <span class="value amount"><?= formatCurrency($order['total_amount']) ?></span>
                                        </div>
                                        
                                        <?php if ($order['order_type'] === 'delivery' && !empty($order['delivery_address'])): ?>
                                            <div class="info-item">
                                                <span class="label">Address:</span>
                                                <span class="value"><?= htmlspecialchars($order['delivery_address']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($order['special_instructions'])): ?>
                                            <div class="info-item">
                                                <span class="label">Instructions:</span>
                                                <span class="value"><?= htmlspecialchars($order['special_instructions']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="order-actions">
                                    <button class="btn btn-small btn-outline" onclick="viewOrderDetails(<?= $order['id'] ?>)">
                                        <i class="fas fa-eye"></i> View Details
                                    </button>
                                    
                                    <?php if ($order['status'] === 'delivered'): ?>
                                        <button class="btn btn-small btn-primary" onclick="reorderItems(<?= $order['id'] ?>)">
                                            <i class="fas fa-redo"></i> Reorder
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if (in_array($order['status'], ['pending', 'confirmed'])): ?>
                                        <button class="btn btn-small btn-danger" onclick="cancelOrder(<?= $order['id'] ?>)">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($order['status'] === 'delivered'): ?>
                                        <button class="btn btn-small btn-secondary" onclick="rateOrder(<?= $order['id'] ?>)">
                                            <i class="fas fa-star"></i> Rate
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?= $page - 1 ?>&status=<?= urlencode($status) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>" 
                                   class="pagination-link">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <a href="?page=<?= $i ?>&status=<?= urlencode($status) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>" 
                                   class="pagination-link <?= $i === $page ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?= $page + 1 ?>&status=<?= urlencode($status) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>" 
                                   class="pagination-link">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="no-orders">
                        <div class="no-orders-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <h3>No orders found</h3>
                        <p>You haven't placed any orders yet or no orders match your filters.</p>
                        <div class="no-orders-actions">
                            <a href="../../menu.php" class="btn btn-primary">
                                <i class="fas fa-utensils"></i> Browse Menu
                            </a>
                            <?php if ($status || $dateFrom || $dateTo): ?>
                                <a href="orders.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear Filters
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Order Details Modal -->
    <div id="orderDetailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Order Details</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body" id="orderDetailsContent">
                <!-- Order details will be loaded here -->
            </div>
        </div>
    </div>
    
    <!-- Cancel Order Modal -->
    <div id="cancelOrderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Cancel Order</h3>
                <span class="close" onclick="closeCancelModal()">&times;</span>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this order?</p>
                <p class="warning-text">
                    <i class="fas fa-exclamation-triangle"></i>
                    This action cannot be undone.
                </p>
                <form method="POST" id="cancelOrderForm">
                    <input type="hidden" name="action" value="cancel_order">
                    <input type="hidden" name="order_id" id="cancelOrderId">
                    
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeCancelModal()">
                            <i class="fas fa-times"></i> No, Keep Order
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-check"></i> Yes, Cancel Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // View order details
        function viewOrderDetails(orderId) {
            fetch(`../api/get-order-details.php?id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('orderDetailsContent').innerHTML = data.html;
                        document.getElementById('orderDetailsModal').style.display = 'block';
                    } else {
                        alert('Failed to load order details');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load order details');
                });
        }
        
        // Cancel order
        function cancelOrder(orderId) {
            document.getElementById('cancelOrderId').value = orderId;
            document.getElementById('cancelOrderModal').style.display = 'block';
        }
        
        // Reorder items
        function reorderItems(orderId) {
            if (confirm('Add all items from this order to your cart?')) {
                window.location.href = `../../menu.php?reorder=${orderId}`;
            }
        }
        
        // Rate order
        function rateOrder(orderId) {
            window.location.href = `rate-order.php?id=${orderId}`;
        }
        
        // Modal functions
        function closeModal() {
            document.getElementById('orderDetailsModal').style.display = 'none';
        }
        
        function closeCancelModal() {
            document.getElementById('cancelOrderModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const orderModal = document.getElementById('orderDetailsModal');
            const cancelModal = document.getElementById('cancelOrderModal');
            
            if (event.target === orderModal) {
                orderModal.style.display = 'none';
            }
            if (event.target === cancelModal) {
                cancelModal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
