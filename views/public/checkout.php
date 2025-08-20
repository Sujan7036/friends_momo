<?php
/**
 * Checkout Page
 * Friends and Momos Restaurant Management System
 */

require_once '../../config/config.php';
require_once '../../models/User.php';
require_once '../../models/MenuItem.php';
require_once '../../models/Order.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=checkout');
    exit;
}

// Check if cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$menuItemModel = new MenuItem();
$orderModel = new Order();
$userModel = new User();

// Get user details
$user = $userModel->getById($_SESSION['user_id']);

// Get cart items
$cartItems = [];
$cartTotal = 0;

if (!empty($_SESSION['cart'])) {
    $itemIds = array_keys($_SESSION['cart']);
    $items = $menuItemModel->getItemsByIds($itemIds);
    
    foreach ($items as $item) {
        $quantity = $_SESSION['cart'][$item['id']];
        $subtotal = $item['price'] * $quantity;
        
        $cartItems[] = array_merge($item, [
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ]);
        
        $cartTotal += $subtotal;
    }
}

// Calculate taxes and fees
$gstRate = 0.10; // 10% GST
$gstAmount = $cartTotal * $gstRate;
$deliveryFee = $cartTotal >= 25 ? 0 : 5; // Free delivery over $25
$finalTotal = $cartTotal + $gstAmount + $deliveryFee;

$message = '';
$messageType = '';

// Handle checkout submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    // Prepare order items array for the database
    $orderItems = [];
    foreach ($cartItems as $item) {
        $orderItems[] = [
            'menu_item_id' => $item['id'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['price'],
            'total_price' => $item['subtotal'],
            'special_instructions' => null
        ];
    }
    
    $orderData = [
        'customer_name' => $user['first_name'] . ' ' . $user['last_name'],
        'customer_email' => $user['email'],
        'customer_phone' => $_POST['phone'] ?? $user['phone'],
        'total_amount' => $finalTotal,
        'tax_amount' => $gstAmount,
        'delivery_fee' => $deliveryFee,
        'delivery_address' => $_POST['delivery_address'] ?? '',
        'special_instructions' => $_POST['special_instructions'] ?? '',
        'payment_method' => $_POST['payment_method'] ?? 'cash',
        'payment_status' => 'pending',
        'items' => $orderItems
    ];
    
    try {
        $orderId = $orderModel->createOrder($_SESSION['user_id'], $orderData);
        
        if ($orderId) {
            // Clear cart
            $_SESSION['cart'] = [];
            
            // Redirect to order confirmation
            header('Location: order_confirmation.php?order_id=' . $orderId);
            exit;
        } else {
            $message = 'Failed to place order. Please try again.';
            $messageType = 'error';
        }
    } catch (Exception $e) {
        $message = 'Error placing order: ' . $e->getMessage();
        $messageType = 'error';
    }
}

$pageTitle = 'Checkout - Friends & Momos';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .checkout-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        
        .checkout-form {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .order-summary {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: fit-content;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .item-details h4 {
            margin: 0 0 0.25rem 0;
            color: #333;
        }
        
        .item-quantity {
            color: #666;
            font-size: 0.9rem;
        }
        
        .item-price {
            font-weight: 600;
            color: #333;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
        }
        
        .total-row.final {
            border-top: 2px solid #333;
            margin-top: 1rem;
            padding-top: 1rem;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .place-order-btn {
            width: 100%;
            background: #dc2626;
            color: white;
            padding: 1rem;
            border: none;
            border-radius: 4px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 1rem;
        }
        
        .place-order-btn:hover {
            background: #b91c1c;
        }
        
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <main>
        <div class="checkout-container">
            <div class="checkout-form">
                <h2><i class="fas fa-credit-card"></i> Checkout</h2>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType ?>">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="order_type">Order Type</label>
                        <select name="order_type" id="order_type" required>
                            <option value="delivery">Delivery</option>
                            <option value="pickup">Pickup</option>
                            <option value="dine_in">Dine In</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="delivery_address_group">
                        <label for="delivery_address">Delivery Address</label>
                        <textarea name="delivery_address" id="delivery_address" 
                                placeholder="Enter your full delivery address"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" name="phone" id="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="special_instructions">Special Instructions (Optional)</label>
                        <textarea name="special_instructions" id="special_instructions" 
                                placeholder="Any special requests or dietary requirements..."></textarea>
                    </div>
                    
                    <button type="submit" name="place_order" class="place-order-btn">
                        <i class="fas fa-check"></i> Place Order
                    </button>
                </form>
            </div>
            
            <div class="order-summary">
                <h3><i class="fas fa-receipt"></i> Order Summary</h3>
                
                <?php foreach ($cartItems as $item): ?>
                    <div class="order-item">
                        <div class="item-details">
                            <h4><?= htmlspecialchars($item['name']) ?></h4>
                            <div class="item-quantity">Qty: <?= $item['quantity'] ?> × $<?= number_format($item['price'], 2) ?></div>
                        </div>
                        <div class="item-price">$<?= number_format($item['subtotal'], 2) ?></div>
                    </div>
                <?php endforeach; ?>
                
                <div class="total-breakdown">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span>$<?= number_format($cartTotal, 2) ?></span>
                    </div>
                    <div class="total-row">
                        <span>GST (10%):</span>
                        <span>$<?= number_format($gstAmount, 2) ?></span>
                    </div>
                    <div class="total-row">
                        <span>Delivery Fee:</span>
                        <span><?= $deliveryFee > 0 ? '$' . number_format($deliveryFee, 2) : 'FREE' ?></span>
                    </div>
                    <div class="total-row final">
                        <span>Total:</span>
                        <span>$<?= number_format($finalTotal, 2) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Show/hide delivery address based on order type
        document.getElementById('order_type').addEventListener('change', function() {
            const deliveryGroup = document.getElementById('delivery_address_group');
            const deliveryAddress = document.getElementById('delivery_address');
            
            if (this.value === 'delivery') {
                deliveryGroup.style.display = 'block';
                deliveryAddress.required = true;
            } else {
                deliveryGroup.style.display = 'none';
                deliveryAddress.required = false;
            }
        });
    </script>
</body>
</html>
