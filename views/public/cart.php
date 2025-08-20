<?php
/**
 * Shopping Cart Page
 * Friends and Momos Restaurant Management System
 */

// Page configuration
$pageTitle = "Shopping Cart - Friends and Momos | Authentic Himalayan Cuisine";
$bodyClass = "cart-page";

// Load required files
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/models/MenuItem.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$user = $isLoggedIn ? $_SESSION['user'] : null;

// Initialize models
$menuItemModel = new MenuItem();

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

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'update_quantity':
            $itemId = (int)$_POST['item_id'];
            $quantity = (int)$_POST['quantity'];
            
            if ($quantity > 0) {
                $_SESSION['cart'][$itemId] = $quantity;
            } else {
                unset($_SESSION['cart'][$itemId]);
            }
            
            echo json_encode(['success' => true]);
            exit;
            
        case 'remove_item':
            $itemId = (int)$_POST['item_id'];
            unset($_SESSION['cart'][$itemId]);
            
            echo json_encode(['success' => true]);
            exit;
            
        case 'clear_cart':
            $_SESSION['cart'] = [];
            
            echo json_encode(['success' => true]);
            exit;
    }
}

// Get cart items
$cartItems = [];
$cartTotal = 0;
$cartCount = 0;

if (!empty($_SESSION['cart'])) {
    $itemIds = array_keys($_SESSION['cart']);
    $items = $menuItemModel->getItemsByIds($itemIds);
    
    foreach ($items as $item) {
        $quantity = isset($_SESSION['cart'][$item['id']]) ? $_SESSION['cart'][$item['id']] : 0;
        $subtotal = $item['price'] * $quantity;
        
        $cartItems[] = [
            'id' => $item['id'],
            'name' => $item['name'],
            'description' => $item['description'],
            'price' => $item['price'],
            'image_url' => $item['image_url'],
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
        
        $cartTotal += $subtotal;
        $cartCount += $quantity;
    }
}

// Calculate taxes and fees
$gstRate = 0.10; // 10% GST
$gstAmount = $cartTotal * $gstRate;
$deliveryFee = $cartTotal >= 30 ? 0 : 5.00; // Free delivery over $30
$finalTotal = $cartTotal + $gstAmount + $deliveryFee;

// Include header
include_once dirname(__DIR__, 2) . '/includes/header.php';
?>

<!-- Cart Hero Section -->
<section class="cart-hero">
    <div class="container">
        <div class="cart-hero-content">
            <h1 class="page-title">Shopping Cart</h1>
            <p class="page-description">
                Review your order and proceed to checkout for authentic Himalayan flavors delivered to your door.
            </p>
        </div>
    </div>
</section>

<!-- Cart Content -->
<section class="cart-content">
    <div class="container">
        <?php if (empty($cartItems)): ?>
            <!-- Empty Cart -->
            <div class="empty-cart">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added any delicious items to your cart yet. Explore our menu and discover the authentic flavors of the Himalayas!</p>
                <div class="empty-cart-actions">
                    <a href="<?= BASE_URL ?>/views/public/menu.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-utensils"></i>
                        Explore Menu
                    </a>
                    <a href="<?= BASE_URL ?>/views/public/index.php" class="btn btn-secondary btn-lg">
                        <i class="fas fa-home"></i>
                        Back to Home
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Cart Items -->
            <div class="cart-layout">
                <div class="cart-items-section">
                    <div class="cart-header">
                        <h2>Your Order (<?= $cartCount ?> item<?= $cartCount !== 1 ? 's' : '' ?>)</h2>
                        <button type="button" id="clear-cart-btn" class="btn btn-outline btn-sm">
                            <i class="fas fa-trash"></i>
                            Clear Cart
                        </button>
                    </div>
                    
                    <div class="cart-items">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="cart-item" data-item-id="<?= $item['id'] ?>">
                                <div class="item-image">
                                    <img src="<?= ASSETS_URL ?>/images/<?= getItemImage($item['name']) ?>" 
                                         alt="<?= htmlspecialchars($item['name']) ?>"
                                         onerror="this.src='<?= ASSETS_URL ?>/images/food.png'">
                                </div>
                                
                                <div class="item-details">
                                    <h3 class="item-name"><?= htmlspecialchars($item['name']) ?></h3>
                                    <p class="item-description"><?= htmlspecialchars($item['description']) ?></p>
                                    
                                    <div class="item-meta">
                                        <?php if (isset($item['is_vegetarian']) && $item['is_vegetarian']): ?>
                                            <span class="meta-badge vegetarian">
                                                <i class="fas fa-leaf"></i> Vegetarian
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($item['is_vegan']) && $item['is_vegan']): ?>
                                            <span class="meta-badge vegan">
                                                <i class="fas fa-seedling"></i> Vegan
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($item['spice_level']) && $item['spice_level'] !== 'mild'): ?>
                                            <span class="meta-badge spicy">
                                                <i class="fas fa-pepper-hot"></i> <?= ucfirst($item['spice_level']) ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($item['preparation_time']) && $item['preparation_time']): ?>
                                            <span class="meta-badge prep-time">
                                                <i class="fas fa-clock"></i> <?= $item['preparation_time'] ?> min
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="item-actions">
                                    <div class="price-section">
                                        <span class="item-price"><?= formatCurrency($item['price']) ?></span>
                                        <span class="item-subtotal"><?= formatCurrency($item['subtotal']) ?></span>
                                    </div>
                                    
                                    <div class="quantity-controls">
                                        <button type="button" class="quantity-btn decrease" data-action="decrease">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <span class="quantity-display"><?= $item['quantity'] ?></span>
                                        <button type="button" class="quantity-btn increase" data-action="increase">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                    
                                    <button type="button" class="remove-item-btn" data-item-id="<?= $item['id'] ?>">
                                        <i class="fas fa-trash"></i>
                                        Remove
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Continue Shopping -->
                    <div class="continue-shopping">
                        <a href="<?= BASE_URL ?>/views/public/menu.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Continue Shopping
                        </a>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="order-summary-section">
                    <div class="order-summary">
                        <h3 class="summary-title">Order Summary</h3>
                        
                        <div class="summary-details">
                            <div class="summary-item">
                                <span class="summary-label">Subtotal (<?= $cartCount ?> items)</span>
                                <span class="summary-value"><?= formatCurrency($cartTotal) ?></span>
                            </div>
                            
                            <div class="summary-item">
                                <span class="summary-label">GST (10%)</span>
                                <span class="summary-value"><?= formatCurrency($gstAmount) ?></span>
                            </div>
                            
                            <div class="summary-item">
                                <span class="summary-label">
                                    Delivery Fee
                                    <?php if ($deliveryFee === 0): ?>
                                        <span class="delivery-note">FREE</span>
                                    <?php endif; ?>
                                </span>
                                <span class="summary-value"><?= formatCurrency($deliveryFee) ?></span>
                            </div>
                            
                            <?php if ($cartTotal < 30 && $deliveryFee > 0): ?>
                                <div class="delivery-offer">
                                    <i class="fas fa-info-circle"></i>
                                    Add <?= formatCurrency(30 - $cartTotal) ?> more for free delivery
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="summary-total">
                            <div class="total-row">
                                <span class="total-label">Total</span>
                                <span class="total-value"><?= formatCurrency($finalTotal) ?></span>
                            </div>
                        </div>
                        
                        <div class="checkout-actions">
                            <?php if ($isLoggedIn): ?>
                                <a href="<?= BASE_URL ?>/views/public/checkout.php" class="btn btn-primary btn-lg checkout-btn">
                                    <i class="fas fa-credit-card"></i>
                                    Proceed to Checkout
                                </a>
                            <?php else: ?>
                                <a href="<?= BASE_URL ?>/views/public/login.php?redirect=checkout" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt"></i>
                                    Login to Checkout
                                </a>
                                <p class="checkout-note">
                                    <i class="fas fa-info-circle"></i>
                                    You need to be logged in to place an order. 
                                    <a href="<?= BASE_URL ?>/views/public/login.php?redirect=checkout">Login</a> or 
                                    <a href="<?= BASE_URL ?>/views/public/register.php?redirect=checkout">create an account</a> to continue.
                                </p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="delivery-info">
                            <h4><i class="fas fa-truck"></i> Delivery Information</h4>
                            <ul>
                                <li>Estimated delivery: 45-60 minutes</li>
                                <li>Free delivery on orders over $30</li>
                                <li>Delivery area: Gungahlin and surrounding suburbs</li>
                                <li>Orders placed before 9:00 PM will be delivered the same day</li>
                            </ul>
                        </div>
                        
                        <div class="contact-info">
                            <h4><i class="fas fa-phone"></i> Need Help?</h4>
                            <p>Call us at <a href="tel:+61262424567">(02) 6242 4567</a> for assistance with your order.</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Recommended Items -->
<?php if (!empty($cartItems)): ?>
<section class="recommended-items">
    <div class="container">
        <h3 class="section-title">You might also like</h3>
        <div class="recommendations-grid" id="recommendations">
            <!-- Recommendations will be loaded via JavaScript -->
        </div>
    </div>
</section>
<?php endif; ?>

<style>
/* Cart Page Specific Styles */
.cart-hero {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
    color: var(--white);
    padding: var(--space-12) 0 var(--space-8);
    text-align: center;
}

.cart-hero-content {
    max-width: 600px;
    margin: 0 auto;
}

.page-title {
    font-size: var(--text-4xl);
    font-weight: 700;
    margin-bottom: var(--space-4);
    color: var(--white);
}

.page-description {
    font-size: var(--text-lg);
    color: var(--gray-100);
    line-height: 1.6;
}

.cart-content {
    padding: var(--space-16) 0;
}

.empty-cart {
    text-align: center;
    padding: var(--space-20) var(--space-8);
    max-width: 600px;
    margin: 0 auto;
}

.empty-cart-icon {
    font-size: var(--text-6xl);
    color: var(--gray-400);
    margin-bottom: var(--space-8);
}

.empty-cart h2 {
    font-size: var(--text-3xl);
    color: var(--gray-900);
    margin-bottom: var(--space-4);
}

.empty-cart p {
    font-size: var(--text-lg);
    color: var(--gray-600);
    line-height: 1.6;
    margin-bottom: var(--space-8);
}

.empty-cart-actions {
    display: flex;
    gap: var(--space-4);
    justify-content: center;
    flex-wrap: wrap;
}

.cart-layout {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: var(--space-12);
    align-items: start;
}

.cart-items-section {
    background-color: var(--white);
    border-radius: var(--radius-2xl);
    padding: var(--space-8);
    box-shadow: var(--shadow);
}

.cart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-8);
    padding-bottom: var(--space-4);
    border-bottom: 2px solid var(--gray-200);
}

.cart-header h2 {
    font-size: var(--text-2xl);
    color: var(--gray-900);
    margin: 0;
}

.cart-items {
    display: flex;
    flex-direction: column;
    gap: var(--space-6);
}

.cart-item {
    display: grid;
    grid-template-columns: 120px 1fr auto;
    gap: var(--space-6);
    padding: var(--space-6);
    border: 2px solid var(--gray-200);
    border-radius: var(--radius-xl);
    transition: var(--transition);
}

.cart-item:hover {
    border-color: var(--primary-color);
    box-shadow: var(--shadow);
}

.item-image {
    width: 120px;
    height: 120px;
    border-radius: var(--radius-xl);
    overflow: hidden;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-details {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.item-name {
    font-size: var(--text-xl);
    font-weight: 600;
    color: var(--gray-900);
    margin: 0;
}

.item-description {
    color: var(--gray-600);
    line-height: 1.5;
    margin: 0;
}

.item-meta {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-2);
}

.meta-badge {
    display: inline-flex;
    align-items: center;
    gap: var(--space-1);
    padding: var(--space-1) var(--space-2);
    border-radius: var(--radius);
    font-size: var(--text-xs);
    font-weight: 500;
    background-color: var(--gray-100);
    color: var(--gray-700);
}

.meta-badge.vegetarian {
    background-color: var(--success-light);
    color: var(--success-dark);
}

.meta-badge.vegan {
    background-color: var(--info-light);
    color: var(--info-dark);
}

.meta-badge.spicy {
    background-color: var(--warning-light);
    color: var(--warning-dark);
}

.meta-badge.prep-time {
    background-color: var(--primary-light);
    color: var(--primary-dark);
}

.item-actions {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
    align-items: flex-end;
    min-width: 150px;
}

.price-section {
    text-align: right;
}

.item-price {
    display: block;
    font-size: var(--text-sm);
    color: var(--gray-500);
}

.item-subtotal {
    display: block;
    font-size: var(--text-xl);
    font-weight: 700;
    color: var(--primary-color);
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    border: 2px solid var(--gray-300);
    border-radius: var(--radius-lg);
    padding: var(--space-1);
}

.quantity-btn {
    width: 32px;
    height: 32px;
    border: none;
    background-color: transparent;
    color: var(--primary-color);
    cursor: pointer;
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
}

.quantity-btn:hover {
    background-color: var(--primary-color);
    color: var(--white);
}

.quantity-display {
    min-width: 40px;
    text-align: center;
    font-weight: 600;
    color: var(--gray-900);
}

.remove-item-btn {
    padding: var(--space-2) var(--space-3);
    background-color: var(--error-light);
    color: var(--error-dark);
    border: none;
    border-radius: var(--radius);
    cursor: pointer;
    font-size: var(--text-sm);
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.remove-item-btn:hover {
    background-color: var(--error-color);
    color: var(--white);
}

.continue-shopping {
    margin-top: var(--space-8);
    padding-top: var(--space-6);
    border-top: 1px solid var(--gray-200);
}

.order-summary-section {
    position: sticky;
    top: var(--space-8);
}

.order-summary {
    background-color: var(--white);
    border-radius: var(--radius-2xl);
    padding: var(--space-8);
    box-shadow: var(--shadow);
}

.summary-title {
    font-size: var(--text-xl);
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: var(--space-6);
    padding-bottom: var(--space-3);
    border-bottom: 2px solid var(--gray-200);
}

.summary-details {
    margin-bottom: var(--space-6);
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-3) 0;
    border-bottom: 1px solid var(--gray-200);
}

.summary-item:last-child {
    border-bottom: none;
}

.summary-label {
    color: var(--gray-700);
    font-size: var(--text-base);
}

.summary-value {
    font-weight: 600;
    color: var(--gray-900);
}

.delivery-note {
    background-color: var(--success-color);
    color: var(--white);
    padding: var(--space-1) var(--space-2);
    border-radius: var(--radius);
    font-size: var(--text-xs);
    font-weight: 600;
    margin-left: var(--space-2);
}

.delivery-offer {
    background-color: var(--info-light);
    color: var(--info-dark);
    padding: var(--space-3);
    border-radius: var(--radius);
    font-size: var(--text-sm);
    display: flex;
    align-items: center;
    gap: var(--space-2);
    margin-top: var(--space-3);
}

.summary-total {
    padding: var(--space-4) 0;
    border-top: 2px solid var(--primary-color);
    border-bottom: 2px solid var(--primary-color);
    margin-bottom: var(--space-6);
}

.total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.total-label {
    font-size: var(--text-lg);
    font-weight: 600;
    color: var(--gray-900);
}

.total-value {
    font-size: var(--text-2xl);
    font-weight: 700;
    color: var(--primary-color);
}

.checkout-actions {
    margin-bottom: var(--space-6);
}

.checkout-btn {
    width: 100%;
    justify-content: center;
}

.checkout-note {
    text-align: center;
    color: var(--gray-600);
    font-size: var(--text-sm);
    margin-top: var(--space-4);
    line-height: 1.5;
}

.checkout-note a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
}

.checkout-note a:hover {
    text-decoration: underline;
}

.delivery-info, .contact-info {
    margin-bottom: var(--space-6);
    padding: var(--space-4);
    background-color: var(--gray-50);
    border-radius: var(--radius-xl);
}

.delivery-info h4, .contact-info h4 {
    font-size: var(--text-base);
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: var(--space-3);
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.delivery-info ul {
    margin: 0;
    padding-left: var(--space-5);
    color: var(--gray-700);
    font-size: var(--text-sm);
}

.delivery-info li {
    margin-bottom: var(--space-1);
}

.contact-info p {
    color: var(--gray-700);
    font-size: var(--text-sm);
    margin: 0;
}

.contact-info a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
}

.contact-info a:hover {
    text-decoration: underline;
}

.recommended-items {
    padding: var(--space-16) 0;
    background-color: var(--gray-50);
}

.section-title {
    font-size: var(--text-2xl);
    color: var(--gray-900);
    margin-bottom: var(--space-8);
    text-align: center;
}

.recommendations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: var(--space-6);
}

/* Responsive Design */
@media (max-width: 968px) {
    .cart-layout {
        grid-template-columns: 1fr;
        gap: var(--space-8);
    }
    
    .order-summary-section {
        position: static;
    }
}

@media (max-width: 768px) {
    .page-title {
        font-size: var(--text-3xl);
    }
    
    .cart-item {
        grid-template-columns: 1fr;
        gap: var(--space-4);
        text-align: center;
    }
    
    .item-image {
        justify-self: center;
    }
    
    .item-actions {
        align-items: center;
        flex-direction: row;
        justify-content: space-between;
        min-width: auto;
    }
    
    .price-section {
        text-align: left;
    }
    
    .empty-cart-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .cart-header {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--space-3);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cart functionality
    const cartItems = document.querySelectorAll('.cart-item');
    const clearCartBtn = document.getElementById('clear-cart-btn');
    
    // Quantity controls
    cartItems.forEach(item => {
        const itemId = item.dataset.itemId;
        const quantityDisplay = item.querySelector('.quantity-display');
        const decreaseBtn = item.querySelector('.quantity-btn.decrease');
        const increaseBtn = item.querySelector('.quantity-btn.increase');
        const removeBtn = item.querySelector('.remove-item-btn');
        
        decreaseBtn.addEventListener('click', () => {
            const currentQuantity = parseInt(quantityDisplay.textContent);
            if (currentQuantity > 1) {
                updateQuantity(itemId, currentQuantity - 1);
            }
        });
        
        increaseBtn.addEventListener('click', () => {
            const currentQuantity = parseInt(quantityDisplay.textContent);
            updateQuantity(itemId, currentQuantity + 1);
        });
        
        removeBtn.addEventListener('click', () => {
            if (confirm('Are you sure you want to remove this item from your cart?')) {
                removeItem(itemId);
            }
        });
    });
    
    // Clear cart
    if (clearCartBtn) {
        clearCartBtn.addEventListener('click', () => {
            if (confirm('Are you sure you want to clear your entire cart?')) {
                clearCart();
            }
        });
    }
    
    function updateQuantity(itemId, quantity) {
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update_quantity&item_id=${itemId}&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                showToast('Failed to update quantity', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to update quantity', 'error');
        });
    }
    
    function removeItem(itemId) {
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=remove_item&item_id=${itemId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                showToast('Failed to remove item', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to remove item', 'error');
        });
    }
    
    function clearCart() {
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=clear_cart'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                showToast('Failed to clear cart', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to clear cart', 'error');
        });
    }
    
    // Load recommendations
    <?php if (!empty($cartItems)): ?>
    loadRecommendations();
    
    function loadRecommendations() {
        // In a real app, this would fetch recommendations based on cart items
        const recommendations = document.getElementById('recommendations');
        
        // Simulate loading
        setTimeout(() => {
            recommendations.innerHTML = `
                <div class="recommendation-card">
                    <div class="rec-image">
                        <img src="<?= ASSETS_URL ?>/images/laphing.png" alt="Laphing" onerror="this.src='<?= ASSETS_URL ?>/images/food.png'">
                    </div>
                    <div class="rec-content">
                        <h4>Spicy Laphing</h4>
                        <p>Cold noodles with spicy sauce</p>
                        <div class="rec-price"><?= formatCurrency(12.50) ?></div>
                        <button class="btn btn-primary btn-sm add-to-cart-btn" data-item-id="5">
                            <i class="fas fa-plus"></i> Add to Cart
                        </button>
                    </div>
                </div>
                <div class="recommendation-card">
                    <div class="rec-image">
                        <img src="<?= ASSETS_URL ?>/images/panipuri.png" alt="Pani Puri" onerror="this.src='<?= ASSETS_URL ?>/images/food.png'">
                    </div>
                    <div class="rec-content">
                        <h4>Pani Puri</h4>
                        <p>Crispy shells with spiced water</p>
                        <div class="rec-price"><?= formatCurrency(8.50) ?></div>
                        <button class="btn btn-primary btn-sm add-to-cart-btn" data-item-id="6">
                            <i class="fas fa-plus"></i> Add to Cart
                        </button>
                    </div>
                </div>
            `;
        }, 1000);
    }
    <?php endif; ?>
});
</script>

<style>
/* Recommendation styles */
.recommendation-card {
    background-color: var(--white);
    border-radius: var(--radius-xl);
    overflow: hidden;
    box-shadow: var(--shadow);
    transition: var(--transition);
}

.recommendation-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.rec-image {
    height: 180px;
    overflow: hidden;
}

.rec-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.rec-content {
    padding: var(--space-4);
    text-align: center;
}

.rec-content h4 {
    font-size: var(--text-lg);
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: var(--space-2);
}

.rec-content p {
    color: var(--gray-600);
    font-size: var(--text-sm);
    margin-bottom: var(--space-3);
}

.rec-price {
    font-size: var(--text-lg);
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: var(--space-4);
}
</style>

<?php include_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
