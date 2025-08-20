<?php
/**
 * Get Order Details API
 * Returns detailed order information for user interface
 */

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/models/Order.php';
require_once dirname(__DIR__, 2) . '/models/OrderItem.php';
require_once dirname(__DIR__, 2) . '/models/MenuItem.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

// Start session and check authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

$orderId = intval($_GET['id'] ?? 0);
$userId = $_SESSION['user_id'];

if (!$orderId) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit();
}

try {
    $orderModel = new Order();
    $orderItemModel = new OrderItem();
    $menuItemModel = new MenuItem();
    
    // Get order details
    $order = $orderModel->find($orderId);
    
    if (!$order || $order['user_id'] != $userId) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit();
    }
    
    // Get order items
    $orderItems = $orderItemModel->getByOrderId($orderId);
    
    // Get menu item details for each order item
    foreach ($orderItems as &$item) {
        $menuItem = $menuItemModel->find($item['menu_item_id']);
        $item['menu_item'] = $menuItem;
        $item['total_price'] = $item['quantity'] * $item['price'];
    }
    
    // Build HTML content
    ob_start();
    ?>
    <div class="order-details">
        <div class="order-header-info">
            <div class="order-summary">
                <h4>Order #<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></h4>
                <p class="order-meta">
                    <span class="order-date">
                        <i class="fas fa-calendar"></i>
                        <?= date('M j, Y g:i A', strtotime($order['created_at'])) ?>
                    </span>
                    <span class="order-status">
                        <i class="fas fa-circle"></i>
                        <?= ucfirst($order['status']) ?>
                    </span>
                </p>
            </div>
            
            <div class="order-info-grid">
                <div class="info-item">
                    <label>Order Type:</label>
                    <span><?= ucfirst(str_replace('_', ' ', $order['order_type'])) ?></span>
                </div>
                
                <?php if ($order['order_type'] === 'delivery' && !empty($order['delivery_address'])): ?>
                    <div class="info-item">
                        <label>Delivery Address:</label>
                        <span><?= htmlspecialchars($order['delivery_address']) ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($order['phone'])): ?>
                    <div class="info-item">
                        <label>Contact Phone:</label>
                        <span><?= htmlspecialchars($order['phone']) ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($order['special_instructions'])): ?>
                    <div class="info-item full-width">
                        <label>Special Instructions:</label>
                        <span><?= htmlspecialchars($order['special_instructions']) ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="order-items">
            <h5>Order Items</h5>
            <div class="items-list">
                <?php foreach ($orderItems as $item): ?>
                    <div class="order-item-detail">
                        <div class="item-image">
                            <?php if (!empty($item['menu_item']['image'])): ?>
                                <img src="<?= ASSETS_URL ?>/images/<?= htmlspecialchars($item['menu_item']['image']) ?>" 
                                     alt="<?= htmlspecialchars($item['menu_item']['name']) ?>">
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fas fa-utensils"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="item-info">
                            <h6><?= htmlspecialchars($item['menu_item']['name']) ?></h6>
                            <p class="item-description"><?= htmlspecialchars($item['menu_item']['description']) ?></p>
                            
                            <div class="item-details">
                                <span class="quantity">Qty: <?= $item['quantity'] ?></span>
                                <span class="unit-price">@ <?= formatCurrency($item['price']) ?></span>
                                <span class="total-price"><?= formatCurrency($item['total_price']) ?></span>
                            </div>
                            
                            <?php if (!empty($item['special_instructions'])): ?>
                                <p class="item-instructions">
                                    <i class="fas fa-sticky-note"></i>
                                    <?= htmlspecialchars($item['special_instructions']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="order-totals">
            <div class="totals-breakdown">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span><?= formatCurrency($order['subtotal']) ?></span>
                </div>
                
                <?php if ($order['tax_amount'] > 0): ?>
                    <div class="total-row">
                        <span>Tax:</span>
                        <span><?= formatCurrency($order['tax_amount']) ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($order['delivery_fee'] > 0): ?>
                    <div class="total-row">
                        <span>Delivery Fee:</span>
                        <span><?= formatCurrency($order['delivery_fee']) ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($order['discount_amount'] > 0): ?>
                    <div class="total-row discount">
                        <span>Discount:</span>
                        <span>-<?= formatCurrency($order['discount_amount']) ?></span>
                    </div>
                <?php endif; ?>
                
                <div class="total-row grand-total">
                    <span>Total:</span>
                    <span><?= formatCurrency($order['total_amount']) ?></span>
                </div>
            </div>
        </div>
        
        <?php if ($order['status'] === 'delivered'): ?>
            <div class="order-actions-modal">
                <button type="button" class="btn btn-primary" onclick="reorderItems(<?= $order['id'] ?>)">
                    <i class="fas fa-redo"></i> Reorder These Items
                </button>
                <button type="button" class="btn btn-secondary" onclick="rateOrder(<?= $order['id'] ?>)">
                    <i class="fas fa-star"></i> Rate This Order
                </button>
            </div>
        <?php endif; ?>
    </div>
    
    <style>
    .order-details {
        max-height: 70vh;
        overflow-y: auto;
    }
    
    .order-header-info {
        margin-bottom: 2rem;
    }
    
    .order-summary h4 {
        color: #2c3e50;
        margin-bottom: 0.5rem;
    }
    
    .order-meta {
        display: flex;
        gap: 2rem;
        color: #6c757d;
        margin-bottom: 1rem;
    }
    
    .order-meta span {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .order-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
    }
    
    .info-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .info-item.full-width {
        grid-column: 1 / -1;
    }
    
    .info-item label {
        font-weight: 500;
        color: #6c757d;
        font-size: 0.9rem;
    }
    
    .info-item span {
        color: #2c3e50;
        font-weight: 500;
    }
    
    .order-items h5 {
        color: #2c3e50;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e9ecef;
    }
    
    .items-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .order-item-detail {
        display: flex;
        gap: 1rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }
    
    .item-image {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        overflow: hidden;
        flex-shrink: 0;
    }
    
    .item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .no-image {
        width: 100%;
        height: 100%;
        background: #dee2e6;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        font-size: 1.5rem;
    }
    
    .item-info {
        flex: 1;
    }
    
    .item-info h6 {
        color: #2c3e50;
        margin-bottom: 0.5rem;
        font-size: 1.1rem;
    }
    
    .item-description {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 0.75rem;
    }
    
    .item-details {
        display: flex;
        gap: 1rem;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }
    
    .quantity {
        color: #6c757d;
    }
    
    .unit-price {
        color: #007bff;
    }
    
    .total-price {
        color: #28a745;
        font-size: 1.1rem;
    }
    
    .item-instructions {
        color: #856404;
        background: #fff3cd;
        padding: 0.5rem;
        border-radius: 4px;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .order-totals {
        margin-top: 2rem;
        padding-top: 1rem;
        border-top: 2px solid #e9ecef;
    }
    
    .totals-breakdown {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 8px;
    }
    
    .total-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.75rem;
        font-weight: 500;
    }
    
    .total-row.discount span:last-child {
        color: #dc3545;
    }
    
    .total-row.grand-total {
        border-top: 2px solid #dee2e6;
        padding-top: 0.75rem;
        margin-top: 0.75rem;
        font-size: 1.25rem;
        font-weight: bold;
        color: #2c3e50;
    }
    
    .order-actions-modal {
        margin-top: 2rem;
        display: flex;
        gap: 1rem;
        justify-content: center;
    }
    
    @media (max-width: 768px) {
        .order-info-grid {
            grid-template-columns: 1fr;
        }
        
        .order-item-detail {
            flex-direction: column;
            text-align: center;
        }
        
        .item-image {
            align-self: center;
        }
        
        .item-details {
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .order-actions-modal {
            flex-direction: column;
        }
    }
    </style>
    <?php
    
    $html = ob_get_clean();
    
    echo json_encode([
        'success' => true,
        'html' => $html,
        'order' => $order
    ]);
    
} catch (Exception $e) {
    error_log("Error in get-order-details.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while loading order details']);
}
