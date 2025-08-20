<?php
/**
 * Admin Orders Management
 * Friends and Momos Restaurant Management System
 */

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/models/Order.php';
require_once dirname(__DIR__, 2) . '/models/User.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../public/login.php');
    exit();
}

$pageTitle = "Orders Management - Admin Panel";
$currentPage = "orders";

$orderModel = new Order();
$userModel = new User();
$message = '';
$messageType = '';

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $orderId = $_POST['order_id'] ?? 0;
    $action = $_POST['action'];
    
    // Debug information
    error_log("Order update attempt - Order ID: $orderId, Action: $action");
    
    switch ($action) {
        case 'update_status':
            $newStatus = $_POST['status'];
            error_log("Updating order $orderId to status: $newStatus");
            
            if ($orderModel->updateStatus($orderId, $newStatus)) {
                $message = "Order status updated successfully.";
                $messageType = 'success';
                error_log("Order status update successful");
            } else {
                $message = "Failed to update order status.";
                $messageType = 'error';
                error_log("Order status update failed");
            }
            break;
            
        case 'assign_staff':
            $staffId = $_POST['staff_id'];
            if ($orderModel->assignStaff($orderId, $staffId)) {
                $message = "Staff assigned successfully.";
                $messageType = 'success';
            } else {
                $message = "Failed to assign staff.";
                $messageType = 'error';
            }
            break;
    }
}

// Get filter parameters
$status = $_GET['status'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$customerId = $_GET['customer_id'] ?? '';

// Get orders with filters
$orders = $orderModel->getOrdersWithFilters($status, $dateFrom, $dateTo, '', $customerId);

// Get staff members for assignment
$staffMembers = $userModel->getStaffMembers();

// Get order statistics
$stats = $orderModel->getOrderStatistics();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Force black text for perfect visibility */
        * { color: #000000 !important; }
        body, html { color: #000000 !important; background-color: #ffffff !important; }
        h1, h2, h3, h4, h5, h6 { color: #000000 !important; }
        p, span, div, td, th, label { color: #000000 !important; }
        .data-table th, .data-table td { color: #000000 !important; background-color: #ffffff !important; }
        .stat-content h3, .stat-content p { color: #000000 !important; }
        .status-badge { color: #000000 !important; font-weight: bold !important; }
        .btn { color: #ffffff !important; }
        .btn-secondary { color: #000000 !important; background-color: #f8f9fa !important; }
        input, select, textarea { color: #000000 !important; background-color: #ffffff !important; }
        .text-muted { color: #333333 !important; }
    </style>
</head>
<body>
    <?php include_once dirname(__DIR__, 2) . '/includes/admin_header.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-shopping-bag"></i> Orders Management</h1>
            <button class="btn btn-primary" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <!-- Order Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $stats['total_orders'] ?? 0 ?></h3>
                    <p>Total Orders</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $stats['pending_orders'] ?? 0 ?></h3>
                    <p>Pending Orders</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon processing">
                    <i class="fas fa-utensils"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $stats['processing_orders'] ?? 0 ?></h3>
                    <p>Processing Orders</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon revenue">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-content">
                    <h3>$<?= number_format($stats['today_revenue'] ?? 0, 2) ?></h3>
                    <p>Today's Revenue</p>
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
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filter
                </button>
                
                <a href="orders.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Clear
                </a>
            </form>
        </div>
        
        <!-- Orders Table -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No orders found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>
                                    <strong>#<?= $order['id'] ?></strong>
                                </td>
                                <td>
                                    <div class="customer-info">
                                        <strong><?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></strong>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($order['customer_email'] ?? '') ?></small>
                                    </div>
                                </td>
                                <td>
                                    <span class="item-count"><?= $order['item_count'] ?? 0 ?> items</span>
                                </td>
                                <td>
                                    <strong>$<?= number_format($order['total_amount'] ?? 0, 2) ?></strong>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $order['status'] ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="payment-badge payment-<?= $order['payment_status'] ?? 'pending' ?>">
                                        <?= ucfirst($order['payment_status'] ?? 'Pending') ?>
                                    </span>
                                </td>
                                <td>
                                    <small><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></small>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-primary" onclick="showOrderModal(<?= $order['id'] ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <?php if ($order['status'] !== 'delivered' && $order['status'] !== 'cancelled'): ?>
                                            <button class="btn btn-sm btn-success" onclick="updateOrderStatus(<?= $order['id'] ?>, '<?= $order['status'] ?>')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            
                                            <button class="btn btn-sm btn-danger" onclick="updateOrderStatus(<?= $order['id'] ?>, '<?= $order['status'] ?>')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
    
    <!-- Update Order Status Modal -->
    <div id="statusModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Update Order Status</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST" id="statusForm">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="order_id" id="modalOrderId">
                
                <div class="form-group">
                    <label for="modalStatus">New Status</label>
                    <select name="status" id="modalStatus" required>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="preparing">Preparing</option>
                        <option value="ready">Ready</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update Status</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <style>
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; align-items: center; }
        .stat-icon { font-size: 2.5rem; margin-right: 15px; color: #007bff; }
        .stat-icon.pending { color: #ffc107; }
        .stat-icon.processing { color: #17a2b8; }
        .stat-icon.revenue { color: #28a745; }
        .stat-content h3 { margin: 0; font-size: 2rem; color: #333; }
        .stat-content p { margin: 5px 0 0; color: #666; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .filters-section { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .filters-form { display: flex; gap: 20px; align-items: end; flex-wrap: wrap; }
        .filter-group { display: flex; flex-direction: column; }
        .filter-group label { margin-bottom: 5px; font-weight: 500; }
        .filter-group input, .filter-group select { padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; }
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; font-weight: 500; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d1ecf1; color: #0c5460; }
        .status-preparing { background: #d4edda; color: #155724; }
        .status-ready { background: #cce5ff; color: #004085; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .payment-badge { padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; font-weight: 500; }
        .payment-pending { background: #fff3cd; color: #856404; }
        .payment-paid { background: #d4edda; color: #155724; }
        .payment-failed { background: #f8d7da; color: #721c24; }
        .action-buttons { display: flex; gap: 5px; align-items: center; }
        .table-container { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        .data-table th { background: #f8f9fa; font-weight: 600; color: #333; }
        .data-table tr:hover { background: #f8f9fa; }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; font-size: 0.9rem; }
        .btn-primary { background: #007bff; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-sm { padding: 4px 8px; font-size: 0.8rem; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .text-center { text-align: center; }
        .text-muted { color: #6c757d; }
        
        /* Modal Styles */
        .modal { position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: #fefefe; margin: 10% auto; padding: 0; border-radius: 8px; width: 90%; max-width: 500px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .modal-header { padding: 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .modal-header h3 { margin: 0; color: #333; }
        .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .close:hover, .close:focus { color: #000; text-decoration: none; }
        .form-group { padding: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #333; }
        .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; color: #333; }
        .modal-footer { padding: 20px; border-top: 1px solid #eee; display: flex; gap: 10px; justify-content: flex-end; }
    </style>
    
    <script>
        function updateOrderStatus(orderId, currentStatus) {
            // Open modal instead of direct update
            document.getElementById('modalOrderId').value = orderId;
            document.getElementById('modalStatus').value = currentStatus;
            document.getElementById('statusModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('statusModal').style.display = 'none';
        }
        
        function showOrderModal(orderId) {
            // Simple implementation for order details
            alert('Order details for #' + orderId + ' - Feature coming soon!');
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('statusModal');
            if (event.target == modal) {
                closeModal();
            }
        }
        
        // Ensure DOM is loaded before adding event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Handle form submission
            const statusForm = document.getElementById('statusForm');
            if (statusForm) {
                statusForm.addEventListener('submit', function(e) {
                    const formData = new FormData(this);
                    const orderId = formData.get('order_id');
                    const status = formData.get('status');
                    
                    if (!confirm(`Are you sure you want to update order #${orderId} to ${status}?`)) {
                        e.preventDefault();
                        return false;
                    }
                    // If confirmed, let the form submit normally
                });
            }
        });
    </script>
</body>
</html>