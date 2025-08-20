<?php
/**
 * Admin Customers Management
 * Friends and Momos Restaurant Management System
 */

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/models/User.php';
require_once dirname(__DIR__, 2) . '/models/Order.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../public/login.php');
    exit();
}

$pageTitle = "Customers Management - Admin Panel";
$currentPage = "customers";

$userModel = new User();
$orderModel = new Order();
$message = '';
$messageType = '';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'toggle_status':
            $id = (int)$_POST['id'];
            $currentStatus = (int)$_POST['current_status'];
            $newStatus = $currentStatus ? 0 : 1;
            
            if ($userModel->update($id, ['is_active' => $newStatus])) {
                $message = "Customer status updated successfully!";
                $messageType = 'success';
            } else {
                $message = "Failed to update customer status.";
                $messageType = 'error';
            }
            break;
            
        case 'delete':
            $id = (int)$_POST['id'];
            
            if ($userModel->delete($id)) {
                $message = "Customer deleted successfully!";
                $messageType = 'success';
            } else {
                $message = "Failed to delete customer.";
                $messageType = 'error';
            }
            break;
            
        case 'add_customer':
            $data = [
                'first_name' => $_POST['first_name'],
                'last_name' => $_POST['last_name'],
                'email' => $_POST['email'],
                'phone' => $_POST['phone'],
                'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                'role' => 'customer',
                'is_active' => 1,
                'email_verified' => 1
            ];
            
            if ($userModel->create($data)) {
                $message = "Customer added successfully!";
                $messageType = 'success';
            } else {
                $message = "Failed to add customer.";
                $messageType = 'error';
            }
            break;
    }
}

// Get filter parameters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

// Get customers with filters
$customers = $userModel->getCustomersWithStats($search, $status);

// Get customer statistics
$stats = $userModel->getCustomerStats();
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
            <h1><i class="fas fa-users"></i> Customers Management</h1>
            <button class="btn btn-primary" onclick="toggleModal('addCustomerModal')">
                <i class="fas fa-plus"></i> Add Customer
            </button>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $stats['total'] ?? 0 ?></h3>
                    <p>Total Customers</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $stats['active'] ?? 0 ?></h3>
                    <p>Active Customers</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $stats['new_this_month'] ?? 0 ?></h3>
                    <p>New This Month</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $stats['with_orders'] ?? 0 ?></h3>
                    <p>With Orders</p>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="filters-section">
            <form method="GET" class="filters-form">
                <div class="filter-group">
                    <label for="search">Search:</label>
                    <input type="text" name="search" id="search" placeholder="Name, email, or phone" value="<?= htmlspecialchars($search) ?>">
                </div>
                
                <div class="filter-group">
                    <label for="status">Status:</label>
                    <select name="status" id="status">
                        <option value="">All Customers</option>
                        <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= $status === '0' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                
                <a href="customers.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Clear
                </a>
            </form>
        </div>
        
        <!-- Customers Table -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Orders</th>
                        <th>Total Spent</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($customers)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No customers found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td>#<?= $customer['id'] ?></td>
                                <td>
                                    <div class="customer-info">
                                        <strong><?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($customer['email']) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <i class="fas fa-phone"></i> <?= htmlspecialchars($customer['phone'] ?? 'N/A') ?>
                                </td>
                                <td>
                                    <span class="order-count"><?= $customer['order_count'] ?? 0 ?></span> orders
                                </td>
                                <td>
                                    <strong>$<?= number_format($customer['total_spent'] ?? 0, 2) ?></strong>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $customer['is_active'] ? 'active' : 'inactive' ?>">
                                        <?= $customer['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td>
                                    <small><?= date('M j, Y', strtotime($customer['created_at'])) ?></small>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <!-- Toggle Status -->
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="id" value="<?= $customer['id'] ?>">
                                            <input type="hidden" name="current_status" value="<?= $customer['is_active'] ?>">
                                            <button type="submit" class="btn btn-sm <?= $customer['is_active'] ? 'btn-warning' : 'btn-success' ?>" 
                                                    title="<?= $customer['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                                <i class="fas fa-<?= $customer['is_active'] ? 'times' : 'check' ?>"></i>
                                            </button>
                                        </form>
                                        
                                        <!-- View Orders -->
                                        <a href="orders.php?customer_id=<?= $customer['id'] ?>" class="btn btn-info btn-sm" title="View Orders">
                                            <i class="fas fa-shopping-bag"></i>
                                        </a>
                                        
                                        <!-- Delete -->
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this customer?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $customer['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
    
    <!-- Add Customer Modal -->
    <div id="addCustomerModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-user-plus"></i> Add New Customer</h2>
                <button type="button" class="close" onclick="toggleModal('addCustomerModal')">&times;</button>
            </div>
            
            <form method="POST">
                <input type="hidden" name="action" value="add_customer">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" name="first_name" id="first_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" name="last_name" id="last_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" name="email" id="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" name="phone" id="phone">
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="password">Password *</label>
                        <input type="password" name="password" id="password" required>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="toggleModal('addCustomerModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <style>
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; align-items: center; }
        .stat-icon { font-size: 2.5rem; margin-right: 15px; color: #007bff; }
        .stat-content h3 { margin: 0; font-size: 2rem; color: #333; }
        .stat-content p { margin: 5px 0 0; color: #666; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .filters-section { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .filters-form { display: flex; gap: 20px; align-items: end; flex-wrap: wrap; }
        .filter-group { display: flex; flex-direction: column; }
        .filter-group label { margin-bottom: 5px; font-weight: 500; }
        .filter-group input, .filter-group select { padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; }
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; font-weight: 500; }
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
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
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-info { background: #17a2b8; color: white; }
        .btn-sm { padding: 4px 8px; font-size: 0.8rem; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .text-center { text-align: center; }
        .text-muted { color: #6c757d; }
        
        .modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal-content { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border-radius: 8px; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto; }
        .modal-header { padding: 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .modal-header h2 { margin: 0; }
        .close { background: none; border: none; font-size: 1.5rem; cursor: pointer; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; padding: 20px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { margin-bottom: 5px; font-weight: 500; }
        .form-group input { padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; }
        .modal-footer { padding: 20px; border-top: 1px solid #eee; display: flex; gap: 10px; justify-content: flex-end; }
    </style>
    
    <script>
        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.style.display = modal.style.display === 'none' ? 'block' : 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>
