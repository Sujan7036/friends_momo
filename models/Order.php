<?php
/**
 * Order Model
 * Friends and Momos Restaurant Management System
 */

class Order extends BaseModel {
    protected $table = 'orders';
    protected $fillable = [
        'user_id', 'customer_name', 'customer_email', 'customer_phone',
        'total_amount', 'tax_amount', 'delivery_fee', 'delivery_address',
        'special_instructions', 'payment_method', 'payment_status', 'status',
        'assigned_staff_id', 'estimated_delivery_time', 'actual_delivery_time'
    ];
    
    /**
     * Create a new order - Simplified for college project
     */
    public function createOrder($userId, $orderData) {
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            
            $pdo->beginTransaction();
            
            // Insert order with only existing columns
            $stmt = $pdo->prepare("
                INSERT INTO orders (
                    user_id, customer_name, customer_email, customer_phone,
                    total_amount, tax_amount, delivery_fee, 
                    delivery_address, special_instructions, payment_method,
                    payment_status, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $userId,
                $orderData['customer_name'] ?? null,
                $orderData['customer_email'] ?? null,
                $orderData['customer_phone'] ?? null,
                $orderData['total_amount'],
                $orderData['tax_amount'] ?? 0,
                $orderData['delivery_fee'] ?? 0,
                $orderData['delivery_address'] ?? null,
                $orderData['special_instructions'] ?? null,
                $orderData['payment_method'] ?? 'cash',
                $orderData['payment_status'] ?? 'pending',
                'pending'
            ]);
            
            $orderId = $pdo->lastInsertId();
            
            // Insert order items
            $itemStmt = $pdo->prepare("
                INSERT INTO order_items (
                    order_id, menu_item_id, quantity, unit_price, 
                    total_price, special_instructions
                ) VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($orderData['items'] as $item) {
                $itemStmt->execute([
                    $orderId,
                    $item['menu_item_id'],
                    $item['quantity'],
                    $item['unit_price'],
                    $item['total_price'],
                    $item['special_instructions'] ?? null
                ]);
            }
            
            $pdo->commit();
            return $orderId;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Order creation failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user's order count
     */
    public function getUserOrderCount($userId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM orders 
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }
    
    /**
     * Get user's total spent amount - Simplified
     */
    public function getUserTotalSpent($userId) {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(total_amount), 0) 
            FROM orders 
            WHERE user_id = ? AND status IN ('delivered', 'completed')
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }
    
    /**
     * Get user's pending orders
     */
    public function getUserPendingOrders($userId) {
        $stmt = $this->db->prepare("
            SELECT * 
            FROM orders 
            WHERE user_id = ? AND status IN ('pending', 'confirmed', 'preparing')
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get user's recent orders
     */
    public function getUserRecentOrders($userId, $limit = 10) {
        $stmt = $this->db->prepare("
            SELECT 
                o.*,
                COUNT(oi.id) as item_count
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.user_id = ?
            GROUP BY o.id
            ORDER BY o.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get order by ID with items
     */
    public function getOrderWithItems($orderId, $userId = null) {
        $whereClause = $userId ? "WHERE o.id = ? AND o.user_id = ?" : "WHERE o.id = ?";
        $params = $userId ? [$orderId, $userId] : [$orderId];
        
        $stmt = $this->db->prepare("
            SELECT 
                o.*,
                u.first_name, u.last_name, u.email
            FROM orders o
            JOIN users u ON o.user_id = u.id
            $whereClause
        ");
        $stmt->execute($params);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($order) {
            // Get order items
            $itemStmt = $this->db->prepare("
                SELECT 
                    oi.*,
                    mi.name, mi.description, mi.image
                FROM order_items oi
                JOIN menu_items mi ON oi.menu_item_id = mi.id
                WHERE oi.order_id = ?
                ORDER BY oi.id
            ");
            $itemStmt->execute([$orderId]);
            $order['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $order;
    }
    
    /**
     * Update order status
     */
    public function updateOrderStatus($orderId, $status, $notes = null) {
        try {
            $stmt = $this->db->prepare("
                UPDATE orders 
                SET status = ?, 
                    status_updated_at = NOW(),
                    admin_notes = CASE 
                        WHEN ? IS NOT NULL THEN CONCAT(COALESCE(admin_notes, ''), '\n', ?)
                        ELSE admin_notes 
                    END
                WHERE id = ?
            ");
            
            return $stmt->execute([$status, $notes, $notes, $orderId]);
            
        } catch (Exception $e) {
            error_log("Order status update failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user's favorite items based on order history
     */
    public function getUserFavoriteItems($userId, $limit = 10) {
        $stmt = $this->db->prepare("
            SELECT 
                mi.*,
                COUNT(oi.id) as order_count,
                SUM(oi.quantity) as total_quantity
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            JOIN menu_items mi ON oi.menu_item_id = mi.id
            WHERE o.user_id = ? AND o.status IN ('delivered', 'completed')
            GROUP BY mi.id
            ORDER BY order_count DESC, total_quantity DESC
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all orders with pagination and filters
     */
    public function getAllOrders($page = 1, $limit = 20, $filters = []) {
        $offset = ($page - 1) * $limit;
        $whereConditions = [];
        $params = [];
        
        // Build where conditions
        if (!empty($filters['status'])) {
            $whereConditions[] = "o.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereConditions[] = "DATE(o.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereConditions[] = "DATE(o.created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['user_id'])) {
            $whereConditions[] = "o.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['search'])) {
            $whereConditions[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereClause = empty($whereConditions) ? '' : 'WHERE ' . implode(' AND ', $whereConditions);
        
        // Get total count
        $countStmt = $this->db->prepare("
            SELECT COUNT(DISTINCT o.id)
            FROM orders o
            JOIN users u ON o.user_id = u.id
            $whereClause
        ");
        $countStmt->execute($params);
        $totalCount = $countStmt->fetchColumn();
        
        // Get orders
        $stmt = $this->db->prepare("
            SELECT 
                o.*,
                u.first_name, u.last_name, u.email,
                COUNT(oi.id) as item_count
            FROM orders o
            JOIN users u ON o.user_id = u.id
            LEFT JOIN order_items oi ON o.id = oi.order_id
            $whereClause
            GROUP BY o.id
            ORDER BY o.created_at DESC
            LIMIT ? OFFSET ?
        ");
        
        $params[] = $limit;
        $params[] = $offset;
        $stmt->execute($params);
        
        return [
            'orders' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $totalCount,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($totalCount / $limit)
        ];
    }
    
    /**
     * Get order statistics
     */
    public function getOrderStatistics($dateFrom = null, $dateTo = null) {
        $whereClause = '';
        $params = [];
        
        if ($dateFrom && $dateTo) {
            $whereClause = "WHERE DATE(created_at) BETWEEN ? AND ?";
            $params = [$dateFrom, $dateTo];
        } elseif ($dateFrom) {
            $whereClause = "WHERE DATE(created_at) >= ?";
            $params = [$dateFrom];
        } elseif ($dateTo) {
            $whereClause = "WHERE DATE(created_at) <= ?";
            $params = [$dateTo];
        }
        
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_orders,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_orders,
                COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed_orders,
                COUNT(CASE WHEN status = 'preparing' THEN 1 END) as preparing_orders,
                COUNT(CASE WHEN status = 'delivered' THEN 1 END) as delivered_orders,
                COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_orders,
                COALESCE(SUM(total_amount), 0) as total_revenue,
                COALESCE(AVG(total_amount), 0) as average_order_value
            FROM orders
            $whereClause
        ");
        
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get daily sales data - Simplified
     */
    public function getDailySales($days = 30) {
        $stmt = $this->db->prepare("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as order_count,
                COALESCE(SUM(total_amount), 0) as revenue
            FROM orders
            WHERE status IN ('delivered', 'completed')
            AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            GROUP BY DATE(created_at)
            ORDER BY date DESC
        ");
        
        $stmt->execute([$days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get popular menu items
     */
    public function getPopularMenuItems($limit = 10, $dateFrom = null, $dateTo = null) {
        $whereClause = "WHERE o.status IN ('delivered', 'completed')";
        $params = [];
        
        if ($dateFrom && $dateTo) {
            $whereClause .= " AND DATE(o.created_at) BETWEEN ? AND ?";
            $params = [$dateFrom, $dateTo];
        }
        
        // Check which columns exist in the database
        try {
            $orderItemsColumns = $this->db->fetchAll("DESCRIBE order_items");
            $hasImage = false;
            $hasImageUrl = false;
            $hasTotalPrice = false;
            $hasUnitPrice = false;
            
            // Check menu_items columns
            $menuItemsColumns = $this->db->fetchAll("DESCRIBE menu_items");
            foreach ($menuItemsColumns as $column) {
                if ($column['Field'] === 'image') $hasImage = true;
                if ($column['Field'] === 'image_url') $hasImageUrl = true;
            }
            
            // Check order_items columns
            foreach ($orderItemsColumns as $column) {
                if ($column['Field'] === 'total_price') $hasTotalPrice = true;
                if ($column['Field'] === 'unit_price') $hasUnitPrice = true;
            }
            
            $imageColumn = $hasImage ? 'mi.image' : ($hasImageUrl ? 'mi.image_url' : "'default.jpg'");
            $revenueColumn = $hasTotalPrice ? 'oi.total_price' : ($hasUnitPrice ? '(oi.unit_price * oi.quantity)' : '(mi.price * oi.quantity)');
            
        } catch (Exception $e) {
            $imageColumn = "'default.jpg'";
            $revenueColumn = '(mi.price * oi.quantity)'; // Fallback calculation
        }
        
        $stmt = $this->db->prepare("
            SELECT 
                mi.id, mi.name, mi.price,
                $imageColumn as image,
                SUM(oi.quantity) as total_quantity,
                COUNT(DISTINCT o.id) as order_count,
                SUM($revenueColumn) as total_revenue
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            JOIN menu_items mi ON oi.menu_item_id = mi.id
            $whereClause
            GROUP BY mi.id
            ORDER BY total_quantity DESC
            LIMIT ?
        ");
        
        $params[] = $limit;
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cancel order
     */
    public function cancelOrder($orderId, $userId = null, $reason = null) {
        try {
            $whereClause = $userId ? "WHERE id = ? AND user_id = ?" : "WHERE id = ?";
            $params = $userId ? [$orderId, $userId] : [$orderId];
            
            // Check if order can be cancelled
            $stmt = $this->db->prepare("
                SELECT status 
                FROM orders 
                $whereClause
            ");
            $stmt->execute($params);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$order) {
                return ['success' => false, 'message' => 'Order not found'];
            }
            
            if (!in_array($order['status'], ['pending', 'confirmed'])) {
                return ['success' => false, 'message' => 'Order cannot be cancelled'];
            }
            
            // Update order status
            $updateStmt = $this->db->prepare("
                UPDATE orders 
                SET status = 'cancelled',
                    cancellation_reason = ?,
                    cancelled_at = NOW()
                $whereClause
            ");
            
            $updateParams = [$reason];
            if ($userId) {
                $updateParams[] = $orderId;
                $updateParams[] = $userId;
            } else {
                $updateParams[] = $orderId;
            }
            
            $success = $updateStmt->execute($updateParams);
            
            return [
                'success' => $success,
                'message' => $success ? 'Order cancelled successfully' : 'Failed to cancel order'
            ];
            
        } catch (Exception $e) {
            error_log("Order cancellation failed: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while cancelling the order'];
        }
    }
    
    /**
     * Get orders for a specific date range
     */
    public function getOrdersByDateRange($dateFrom, $dateTo, $status = null) {
        $whereClause = "WHERE DATE(created_at) BETWEEN ? AND ?";
        $params = [$dateFrom, $dateTo];
        
        if ($status) {
            $whereClause .= " AND status = ?";
            $params[] = $status;
        }
        
        $stmt = $this->db->prepare("
            SELECT 
                o.*,
                u.first_name, u.last_name, u.email,
                COUNT(oi.id) as item_count
            FROM orders o
            JOIN users u ON o.user_id = u.id
            LEFT JOIN order_items oi ON o.id = oi.order_id
            $whereClause
            GROUP BY o.id
            ORDER BY o.created_at DESC
        ");
        
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get orders with filters for admin management
     */
    public function getOrdersWithFilters($status = '', $dateFrom = '', $dateTo = '', $orderType = '', $customerId = '') {
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            
            $sql = "SELECT o.*, 
                          CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                          u.email as customer_email,
                          u.phone as customer_phone,
                          COUNT(oi.id) as item_count
                   FROM orders o
                   LEFT JOIN users u ON o.user_id = u.id
                   LEFT JOIN order_items oi ON o.id = oi.order_id
                   WHERE 1=1";
            
            $params = [];
            
            if (!empty($status)) {
                $sql .= " AND o.status = ?";
                $params[] = $status;
            }
            
            if (!empty($dateFrom)) {
                $sql .= " AND DATE(o.created_at) >= ?";
                $params[] = $dateFrom;
            }
            
            if (!empty($dateTo)) {
                $sql .= " AND DATE(o.created_at) <= ?";
                $params[] = $dateTo;
            }
            
            if (!empty($customerId)) {
                $sql .= " AND o.user_id = ?";
                $params[] = $customerId;
            }
            
            $sql .= " GROUP BY o.id ORDER BY o.created_at DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching orders: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update order status
     */
    public function updateStatus($orderId, $status) {
        try {
            return $this->update($orderId, ['status' => $status]);
        } catch (Exception $e) {
            error_log("Error updating order status: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Assign staff to order
     */
    public function assignStaff($orderId, $staffId) {
        try {
            return $this->update($orderId, ['assigned_staff_id' => $staffId]);
        } catch (Exception $e) {
            error_log("Error assigning staff: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all orders created today
     */
    public function getTodayOrders() {
        $db = Database::getInstance();
        $pdo = $db->getConnection();
        $sql = "SELECT * FROM orders WHERE DATE(created_at) = CURDATE() ORDER BY created_at DESC";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all pending orders
     */
    public function getPendingOrders() {
        $db = Database::getInstance();
        $pdo = $db->getConnection();
        $sql = "SELECT * FROM orders WHERE status = 'pending' ORDER BY created_at DESC";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all active orders (preparing, ready)
     */
    public function getActiveOrders() {
        $db = Database::getInstance();
        $pdo = $db->getConnection();
        $sql = "SELECT * FROM orders WHERE status IN ('preparing', 'ready') ORDER BY created_at ASC";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    }
}
?>
