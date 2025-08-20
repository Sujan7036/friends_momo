<?php
/**
 * Admin Reservations Management
 * Friends and Momos Restaurant Management System
 */

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/models/Reservation.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../public/login.php');
    exit();
}

$pageTitle = "Reservations Management - Admin Panel";
$currentPage = "reservations";

$reservationModel = new Reservation();
$message = '';
$messageType = '';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_status':
            $id = (int)$_POST['id'];
            $status = $_POST['status'];
            
            if ($reservationModel->update($id, ['status' => $status])) {
                $message = "Reservation status updated successfully!";
                $messageType = 'success';
            } else {
                $message = "Failed to update reservation status.";
                $messageType = 'error';
            }
            break;
            
        case 'delete':
            $id = (int)$_POST['id'];
            
            if ($reservationModel->delete($id)) {
                $message = "Reservation deleted successfully!";
                $messageType = 'success';
            } else {
                $message = "Failed to delete reservation.";
                $messageType = 'error';
            }
            break;
    }
}

// Get filter parameters
$status = $_GET['status'] ?? '';
$date = $_GET['date'] ?? '';

// Get reservations with filters
$reservations = $reservationModel->getReservationsWithCustomers($status, $date, $date);

// Get statistics
$stats = $reservationModel->getReservationStats();
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
            <h1><i class="fas fa-calendar-check"></i> Reservations Management</h1>
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
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $stats['total'] ?? 0 ?></h3>
                    <p>Total Reservations</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $stats['pending'] ?? 0 ?></h3>
                    <p>Pending</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $stats['confirmed'] ?? 0 ?></h3>
                    <p>Confirmed</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $stats['today'] ?? 0 ?></h3>
                    <p>Today</p>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="filters-section">
            <form method="GET" class="filters-form">
                <div class="filter-group">
                    <label for="status">Status:</label>
                    <select name="status" id="status">
                        <option value="">All Statuses</option>
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="confirmed" <?= $status === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="date">Date:</label>
                    <input type="date" name="date" id="date" value="<?= htmlspecialchars($date) ?>">
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
                
                <a href="reservations.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Clear
                </a>
            </form>
        </div>
        
        <!-- Reservations Table -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Date & Time</th>
                        <th>Guests</th>
                        <th>Status</th>
                        <th>Special Requests</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reservations)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No reservations found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reservations as $reservation): ?>
                            <tr>
                                <td>#<?= $reservation['id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($reservation['customer_name'] ?? $reservation['name'] ?? 'N/A') ?></strong><br>
                                    <small><?= htmlspecialchars($reservation['customer_email'] ?? $reservation['email'] ?? 'N/A') ?></small>
                                </td>
                                <td>
                                    <i class="fas fa-phone"></i> <?= htmlspecialchars($reservation['customer_phone'] ?? $reservation['phone'] ?? 'N/A') ?>
                                </td>
                                <td>
                                    <strong><?= date('M j, Y', strtotime($reservation['reservation_date'] ?? $reservation['date'])) ?></strong><br>
                                    <small><i class="fas fa-clock"></i> <?= date('g:i A', strtotime($reservation['reservation_time'] ?? $reservation['time'])) ?></small>
                                </td>
                                <td>
                                    <i class="fas fa-users"></i> <?= $reservation['guest_count'] ?? $reservation['guests'] ?? $reservation['party_size'] ?? 'N/A' ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $reservation['status'] ?>">
                                        <?= ucfirst($reservation['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= htmlspecialchars($reservation['special_requests'] ?? $reservation['notes'] ?? 'None') ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <!-- Status Update Form -->
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="id" value="<?= $reservation['id'] ?>">
                                            <select name="status" onchange="this.form.submit()" class="status-select">
                                                <option value="pending" <?= $reservation['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="confirmed" <?= $reservation['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                                <option value="completed" <?= $reservation['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                                <option value="cancelled" <?= $reservation['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                            </select>
                                        </form>
                                        
                                        <!-- Delete Button -->
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this reservation?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $reservation['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
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
    
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-right: 15px;
            color: #007bff;
        }
        
        .stat-content h3 {
            margin: 0;
            font-size: 2rem;
            color: #333;
        }
        
        .stat-content p {
            margin: 5px 0 0;
            color: #666;
        }
        
        .filters-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .filters-form {
            display: flex;
            gap: 20px;
            align-items: end;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        
        .filter-group label {
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .filter-group input,
        .filter-group select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-completed { background: #d1ecf1; color: #0c5460; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        .status-select {
            padding: 4px 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.85rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
            align-items: center;
        }
        
        .table-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th,
        .data-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .data-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .data-table tr:hover {
            background: #f8f9fa;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
        }
        
        .btn-primary { background: #007bff; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-sm { padding: 4px 8px; font-size: 0.8rem; }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .text-center { text-align: center; }
    </style>
</body>
</html>
