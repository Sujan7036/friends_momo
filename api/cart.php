<?php
/**
 * Cart API Endpoint
 * Friends and Momos Restaurant Management System
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/models/MenuItem.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

class CartAPI {
    private $menuItemModel;
    
    public function __construct() {
        $this->menuItemModel = new MenuItem();
        
        // Initialize cart in session if not exists
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }
    
    /**
     * Handle API requests
     */
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $input = json_decode(file_get_contents('php://input'), true);
        
        if ($method === 'POST' && isset($input['action'])) {
            switch ($input['action']) {
                case 'add':
                    return $this->addToCart($input);
                case 'update':
                    return $this->updateCartItem($input);
                case 'remove':
                    return $this->removeFromCart($input);
                case 'clear':
                    return $this->clearCart();
                case 'get':
                    return $this->getCart();
                default:
                    return $this->errorResponse('Invalid action');
            }
        } elseif ($method === 'GET') {
            return $this->getCart();
        } else {
            return $this->errorResponse('Invalid request method');
        }
    }
    
    /**
     * Add item to cart
     */
    private function addToCart($input) {
        try {
            $itemId = intval($input['item_id'] ?? 0);
            $quantity = intval($input['quantity'] ?? 1);
            $specialInstructions = trim($input['special_instructions'] ?? '');
            
            if ($itemId <= 0) {
                return $this->errorResponse('Invalid item ID');
            }
            
            if ($quantity <= 0) {
                return $this->errorResponse('Invalid quantity');
            }
            
            // Get menu item details
            $menuItem = $this->menuItemModel->getItemWithCategory($itemId);
            if (!$menuItem) {
                return $this->errorResponse('Menu item not found');
            }
            
            if (!$menuItem['is_available']) {
                return $this->errorResponse('This item is currently unavailable');
            }
            
            // Check if item already exists in cart
            $cartKey = $this->generateCartKey($itemId, $specialInstructions);
            
            if (isset($_SESSION['cart'][$cartKey])) {
                // Update existing item
                $_SESSION['cart'][$cartKey]['quantity'] += $quantity;
            } else {
                // Add new item
                $_SESSION['cart'][$cartKey] = [
                    'id' => $itemId,
                    'name' => $menuItem['name'],
                    'price' => $menuItem['price'],
                    'image' => $menuItem['image'],
                    'quantity' => $quantity,
                    'special_instructions' => $specialInstructions,
                    'added_at' => time()
                ];
            }
            
            return $this->successResponse([
                'message' => 'Item added to cart successfully',
                'cart' => $this->getCartSummary()
            ]);
            
        } catch (Exception $e) {
            error_log("Cart add error: " . $e->getMessage());
            return $this->errorResponse('Failed to add item to cart');
        }
    }
    
    /**
     * Update cart item quantity
     */
    private function updateCartItem($input) {
        try {
            $cartKey = $input['cart_key'] ?? '';
            $quantity = intval($input['quantity'] ?? 0);
            
            if (empty($cartKey)) {
                return $this->errorResponse('Invalid cart key');
            }
            
            if (!isset($_SESSION['cart'][$cartKey])) {
                return $this->errorResponse('Item not found in cart');
            }
            
            if ($quantity <= 0) {
                // Remove item if quantity is 0 or negative
                unset($_SESSION['cart'][$cartKey]);
                $message = 'Item removed from cart';
            } else {
                // Update quantity
                $_SESSION['cart'][$cartKey]['quantity'] = $quantity;
                $message = 'Cart updated successfully';
            }
            
            return $this->successResponse([
                'message' => $message,
                'cart' => $this->getCartSummary()
            ]);
            
        } catch (Exception $e) {
            error_log("Cart update error: " . $e->getMessage());
            return $this->errorResponse('Failed to update cart');
        }
    }
    
    /**
     * Remove item from cart
     */
    private function removeFromCart($input) {
        try {
            $cartKey = $input['cart_key'] ?? '';
            
            if (empty($cartKey)) {
                return $this->errorResponse('Invalid cart key');
            }
            
            if (!isset($_SESSION['cart'][$cartKey])) {
                return $this->errorResponse('Item not found in cart');
            }
            
            unset($_SESSION['cart'][$cartKey]);
            
            return $this->successResponse([
                'message' => 'Item removed from cart successfully',
                'cart' => $this->getCartSummary()
            ]);
            
        } catch (Exception $e) {
            error_log("Cart remove error: " . $e->getMessage());
            return $this->errorResponse('Failed to remove item from cart');
        }
    }
    
    /**
     * Clear all items from cart
     */
    private function clearCart() {
        try {
            $_SESSION['cart'] = [];
            
            return $this->successResponse([
                'message' => 'Cart cleared successfully',
                'cart' => $this->getCartSummary()
            ]);
            
        } catch (Exception $e) {
            error_log("Cart clear error: " . $e->getMessage());
            return $this->errorResponse('Failed to clear cart');
        }
    }
    
    /**
     * Get cart contents
     */
    private function getCart() {
        try {
            $cart = $_SESSION['cart'] ?? [];
            $cartItems = [];
            $subtotal = 0;
            
            foreach ($cart as $key => $item) {
                $itemTotal = $item['price'] * $item['quantity'];
                $subtotal += $itemTotal;
                
                $cartItems[] = [
                    'cart_key' => $key,
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'image' => $item['image'],
                    'quantity' => $item['quantity'],
                    'special_instructions' => $item['special_instructions'],
                    'item_total' => $itemTotal,
                    'added_at' => $item['added_at']
                ];
            }
            
            // Calculate totals
            $taxRate = 0.08; // 8% tax
            $taxAmount = $subtotal * $taxRate;
            $deliveryFee = $subtotal > 25 ? 0 : 3.99; // Free delivery over $25
            $total = $subtotal + $taxAmount + $deliveryFee;
            
            return $this->successResponse([
                'items' => $cartItems,
                'summary' => [
                    'item_count' => count($cartItems),
                    'total_quantity' => array_sum(array_column($cart, 'quantity')),
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'delivery_fee' => $deliveryFee,
                    'total' => $total
                ]
            ]);
            
        } catch (Exception $e) {
            error_log("Cart get error: " . $e->getMessage());
            return $this->errorResponse('Failed to get cart contents');
        }
    }
    
    /**
     * Get cart summary for quick access
     */
    private function getCartSummary() {
        $cart = $_SESSION['cart'] ?? [];
        $itemCount = count($cart);
        $totalQuantity = array_sum(array_column($cart, 'quantity'));
        $subtotal = 0;
        
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        
        return [
            'item_count' => $itemCount,
            'total_quantity' => $totalQuantity,
            'subtotal' => $subtotal
        ];
    }
    
    /**
     * Generate unique cart key
     */
    private function generateCartKey($itemId, $specialInstructions = '') {
        return $itemId . '_' . md5($specialInstructions);
    }
    
    /**
     * Return success response
     */
    private function successResponse($data) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
        exit;
    }
    
    /**
     * Return error response
     */
    private function errorResponse($message, $code = 400) {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }
}

// Handle the request
$cartAPI = new CartAPI();
$cartAPI->handleRequest();
?>
