<?php
/**
 * Admin Reports
 * Friends and Momos Restaurant Management System
 */

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/models/User.php';
require_once dirname(__DIR__, 2) . '/models/Order.php';
require_once dirname(__DIR__, 2) . '/models/Reservation.php';
require_once dirname(__DIR__, 2) . '/models/MenuItem.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../../public/login.php');
    exit();
}

$pageTitle = "Reports - Admin Panel";
$currentPage = "reports";

// Initialize models
$orderModel = new Order();
$reservationModel = new Reservation();
$menuItemModel = new MenuItem();
$userModel = new User();

// Get date range (default: last 30 days)
$dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
$dateTo = $_GET['date_to'] ?? date('Y-m-d');

// Get report data
$orderStats = $orderModel->getOrderStatistics($dateFrom, $dateTo);
$popularItems = $orderModel->getPopularMenuItems(10, $dateFrom, $dateTo);
$dailySales = $orderModel->getDailySales(30);
$customerStats = $userModel->getCustomerStats();
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
            <h1><i class="fas fa-chart-bar"></i> Reports & Analytics</h1>
            <div class="date-filter">
                <form method="GET" class="filters-form">
                    <div class="filter-group">
                        <label for="date_from">From:</label>
                        <input type="date" name="date_from" id="date_from" value="<?= $dateFrom ?>">
                    </div>
                    <div class="filter-group">
                        <label for="date_to">To:</label>
                        <input type="date" name="date_to" id="date_to" value="<?= $dateTo ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Update Report
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Key Metrics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-content">
                    <h3>$<?= number_format($orderStats['total_revenue'] ?? 0, 2) ?></h3>
                    <p>Total Revenue</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $orderStats['total_orders'] ?? 0 ?></h3>
                    <p>Total Orders</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-content">
                    <h3>$<?= number_format(($orderStats['total_revenue'] ?? 0) / max(($orderStats['total_orders'] ?? 1), 1), 2) ?></h3>
                    <p>Average Order Value</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $customerStats['total_customers'] ?? 0 ?></h3>
                    <p>Total Customers</p>
                </div>
            </div>
        </div>
        
        <!-- Charts Section -->
        <div class="reports-grid">
            <!-- Daily Sales Chart -->
            <div class="report-card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-line"></i> Daily Sales (Last 30 Days)</h3>
                </div>
                <div class="card-content">
                    <canvas id="salesChart" width="400" height="200"></canvas>
                </div>
            </div>
            
            <!-- Popular Items -->
            <div class="report-card">
                <div class="card-header">
                    <h3><i class="fas fa-star"></i> Popular Menu Items</h3>
                </div>
                <div class="card-content">
                    <div class="popular-items-list">
                        <?php if (!empty($popularItems)): ?>
                            <?php foreach ($popularItems as $index => $item): ?>
                                <div class="popular-item">
                                    <div class="item-rank">#<?= $index + 1 ?></div>
                                    <div class="item-info">
                                        <strong><?= htmlspecialchars($item['name']) ?></strong>
                                        <span class="item-stats"><?= $item['total_quantity'] ?> sold | $<?= number_format($item['total_revenue'], 2) ?> revenue</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No sales data available for the selected period.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Export Options -->
        <div class="export-section">
            <h3>Export Reports</h3>
            <div class="export-buttons">
                <button class="btn btn-secondary" onclick="exportReport('pdf')">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </button>
                <button class="btn btn-secondary" onclick="exportReport('csv')">
                    <i class="fas fa-file-csv"></i> Export CSV
                </button>
                <button class="btn btn-secondary" onclick="exportReport('excel')">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
            </div>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    // Sales Chart
    const salesData = <?= json_encode($dailySales) ?>;
    const ctx = document.getElementById('salesChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: salesData.map(day => day.date),
            datasets: [{
                label: 'Daily Sales ($)',
                data: salesData.map(day => parseFloat(day.revenue)),
                borderColor: '#dc2626',
                backgroundColor: 'rgba(220, 38, 38, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toFixed(2);
                        }
                    }
                }
            }
        }
    });
    
    function exportReport(format) {
        const params = new URLSearchParams(window.location.search);
        params.set('export', format);
        window.open('export_report.php?' + params.toString(), '_blank');
    }
    </script>
    
    <style>
    .reports-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }
    
    .report-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    .card-header {
        background-color: #f9fafb;
        padding: 1rem;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .card-header h3 {
        margin: 0;
        color: #1f2937;
        font-size: 1.125rem;
        font-weight: 600;
    }
    
    .card-content {
        padding: 1rem;
    }
    
    .popular-items-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .popular-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.75rem;
        background-color: #f9fafb;
        border-radius: 6px;
    }
    
    .item-rank {
        background-color: #dc2626;
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.875rem;
    }
    
    .item-info {
        flex: 1;
    }
    
    .item-info strong {
        display: block;
        color: #1f2937;
        font-weight: 600;
    }
    
    .item-stats {
        color: #6b7280;
        font-size: 0.875rem;
    }
    
    .export-section {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    .export-section h3 {
        margin: 0 0 1rem 0;
        color: #1f2937;
    }
    
    .export-buttons {
        display: flex;
        gap: 1rem;
    }
    
    .date-filter {
        display: flex;
        align-items: end;
        gap: 1rem;
    }
    
    @media (max-width: 768px) {
        .reports-grid {
            grid-template-columns: 1fr;
        }
        
        .date-filter {
            flex-direction: column;
            align-items: stretch;
        }
        
        .export-buttons {
            flex-direction: column;
        }
    }
    </style>
</body>
</html>
