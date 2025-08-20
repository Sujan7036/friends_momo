<?php
/**
 * Order Confirmation Page
 * Friends and Momos Restaurant Management System
 */

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/models/Order.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get order ID from URL
$orderId = $_GET['order_id'] ?? 0;

if (!$orderId) {
    header('Location: menu.php');
    exit;
}

$orderModel = new Order();
$order = $orderModel->find($orderId);

// Verify order belongs to current user
if (!$order || $order['user_id'] != $_SESSION['user_id']) {
    header('Location: menu.php');
    exit;
}

$pageTitle = "Order Confirmation - Friends & Momos";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include_once '../../includes/header.php'; ?>
    
    <main class="confirmation-page">
        <div class="confirmation-container">
            <div class="confirmation-header">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1>Order Confirmed!</h1>
                <p>Thank you for your order. We've received your request and will start preparing it shortly.</p>
            </div>
            
            <div class="order-details">
                <div class="order-summary">
                    <h2>Order Summary</h2>
                    <div class="order-info">
                        <div class="info-row">
                            <span class="label">Order Number:</span>
                            <span class="value">#<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Order Date:</span>
                            <span class="value"><?= date('F j, Y g:i A', strtotime($order['created_at'])) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Total Amount:</span>
                            <span class="value total-amount">$<?= number_format($order['total_amount'], 2) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Status:</span>
                            <span class="value status"><?= ucfirst($order['status']) ?></span>
                        </div>
                        <?php if ($order['delivery_address']): ?>
                        <div class="info-row">
                            <span class="label">Delivery Address:</span>
                            <span class="value"><?= htmlspecialchars($order['delivery_address']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="next-steps">
                    <h3>What's Next?</h3>
                    <ul>
                        <li><i class="fas fa-utensils"></i> We'll start preparing your order right away</li>
                        <li><i class="fas fa-clock"></i> Estimated preparation time: 20-30 minutes</li>
                        <li><i class="fas fa-phone"></i> We'll call you when your order is ready</li>
                        <?php if ($order['delivery_address']): ?>
                        <li><i class="fas fa-truck"></i> Our delivery team will bring it to your door</li>
                        <?php else: ?>
                        <li><i class="fas fa-store"></i> You can pick up your order at our restaurant</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            
            <div class="confirmation-actions">
                <a href="menu.php" class="btn btn-primary">
                    <i class="fas fa-utensils"></i> Order More
                </a>
                <a href="../../views/public" class="btn btn-secondary">
                    <i class="fas fa-home"></i> Back to Home
                </a>
            </div>
        </div>
    </main>
    
    <style>
        .confirmation-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .confirmation-container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .confirmation-header {
            margin-bottom: 30px;
        }
        
        .success-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 20px;
        }
        
        .confirmation-header h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 2.5rem;
        }
        
        .confirmation-header p {
            color: #666;
            font-size: 1.1rem;
        }
        
        .order-details {
            text-align: left;
            margin-bottom: 30px;
        }
        
        .order-summary h2, .next-steps h3 {
            color: #333;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .order-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
        }
        
        .info-row:last-child {
            margin-bottom: 0;
        }
        
        .label {
            font-weight: 600;
            color: #555;
        }
        
        .value {
            color: #333;
        }
        
        .total-amount {
            font-weight: bold;
            color: #28a745;
            font-size: 1.2rem;
        }
        
        .status {
            background: #fff3cd;
            color: #856404;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        
        .next-steps ul {
            list-style: none;
            padding: 0;
        }
        
        .next-steps li {
            padding: 10px 0;
            color: #555;
        }
        
        .next-steps li i {
            color: #007bff;
            margin-right: 10px;
            width: 20px;
        }
        
        .confirmation-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #545b62;
            transform: translateY(-2px);
        }
        
        @media (max-width: 600px) {
            .confirmation-container {
                padding: 20px;
                margin: 20px;
            }
            
            .confirmation-header h1 {
                font-size: 2rem;
            }
            
            .confirmation-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</body>
</html>
