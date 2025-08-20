<?php
/**
 * MenuItem Model
 * Friends and Momos Restaurant Management System
 */

require_once 'BaseModel.php';

class MenuItem extends BaseModel {
    protected $table = 'menu_items';
    protected $fillable = [
        'category_id', 'name', 'description', 'price', 'image_url', 'is_available', 'is_featured',
        'prep_time', 'calories', 'spice_level', 'is_vegetarian', 'is_vegan',
        'is_gluten_free', 'ingredients', 'allergens', 'display_order'
    ];
    
    /**
     * Get all available menu items with category info
     */
    public function getAvailableItems() {
        $sql = "SELECT mi.*, c.name as category_name,
                       COALESCE(oi.order_count, 0) as order_count,
                       mi.prep_time as preparation_time,
                       0 as discount_percentage,
                       mi.price as discounted_price
                FROM {$this->table} mi 
                JOIN categories c ON mi.category_id = c.id 
                LEFT JOIN (
                    SELECT menu_item_id, SUM(quantity) as order_count
                    FROM order_items 
                    GROUP BY menu_item_id
                ) oi ON mi.id = oi.menu_item_id
                WHERE mi.is_available = 1 AND c.is_active = 1 
                ORDER BY c.display_order ASC, mi.display_order ASC, mi.name ASC";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get menu items by category
     */
    public function getItemsByCategory($categoryId) {
        $sql = "SELECT mi.*, c.name as category_name,
                       COALESCE(oi.order_count, 0) as order_count,
                       mi.prep_time as preparation_time,
                       0 as discount_percentage,
                       mi.price as discounted_price
                FROM {$this->table} mi 
                JOIN categories c ON mi.category_id = c.id 
                LEFT JOIN (
                    SELECT menu_item_id, SUM(quantity) as order_count
                    FROM order_items 
                    GROUP BY menu_item_id
                ) oi ON mi.id = oi.menu_item_id
                WHERE mi.category_id = ? AND mi.is_available = 1 AND c.is_active = 1 
                ORDER BY mi.display_order ASC, mi.name ASC";
        return $this->db->fetchAll($sql, [$categoryId]);
    }
    
    /**
     * Get single menu item with category info
     */
    public function getItemWithCategory($itemId) {
        $sql = "SELECT mi.*, c.name as category_name,
                       COALESCE(oi.order_count, 0) as order_count,
                       mi.prep_time as preparation_time,
                       0 as discount_percentage,
                       mi.price as discounted_price
                FROM {$this->table} mi 
                JOIN categories c ON mi.category_id = c.id 
                LEFT JOIN (
                    SELECT menu_item_id, SUM(quantity) as order_count
                    FROM order_items 
                    GROUP BY menu_item_id
                ) oi ON mi.id = oi.menu_item_id
                WHERE mi.id = ? 
                LIMIT 1";
        return $this->db->fetch($sql, [$itemId]);
    }
    
    /**
     * Search menu items
     */
    public function searchItems($searchTerm, $categoryId = null, $dietaryFilters = []) {
        $sql = "SELECT mi.*, c.name as category_name,
                       COALESCE(oi.order_count, 0) as order_count,
                       mi.prep_time as preparation_time,
                       0 as discount_percentage,
                       mi.price as discounted_price
                FROM {$this->table} mi 
                JOIN categories c ON mi.category_id = c.id 
                LEFT JOIN (
                    SELECT menu_item_id, SUM(quantity) as order_count
                    FROM order_items 
                    GROUP BY menu_item_id
                ) oi ON mi.id = oi.menu_item_id
                WHERE mi.is_available = 1 AND c.is_active = 1";
        
        $params = [];
        
        // Search term
        if (!empty($searchTerm)) {
            $sql .= " AND (mi.name LIKE ? OR mi.description LIKE ? OR mi.ingredients LIKE ?)";
            $searchParam = "%{$searchTerm}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        // Category filter
        if ($categoryId) {
            $sql .= " AND mi.category_id = ?";
            $params[] = $categoryId;
        }
        
        // Dietary filters
        if (isset($dietaryFilters['vegetarian']) && $dietaryFilters['vegetarian']) {
            $sql .= " AND mi.is_vegetarian = 1";
        }
        
        if (isset($dietaryFilters['vegan']) && $dietaryFilters['vegan']) {
            $sql .= " AND mi.is_vegan = 1";
        }
        
        if (isset($dietaryFilters['gluten_free']) && $dietaryFilters['gluten_free']) {
            $sql .= " AND mi.is_gluten_free = 1";
        }
        
        if (isset($dietaryFilters['spice_level']) && !empty($dietaryFilters['spice_level'])) {
            $sql .= " AND mi.spice_level = ?";
            $params[] = $dietaryFilters['spice_level'];
        }
        
        $sql .= " ORDER BY c.display_order ASC, mi.display_order ASC, mi.name ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get featured/popular items
     */
    public function getFeaturedItems($limit = 6) {
        $sql = "SELECT mi.*, c.name as category_name,
                       COALESCE(oi.order_count, 0) as order_count,
                       mi.prep_time as preparation_time,
                       0 as discount_percentage,
                       mi.price as discounted_price
                FROM {$this->table} mi 
                JOIN categories c ON mi.category_id = c.id 
                LEFT JOIN (
                    SELECT menu_item_id, SUM(quantity) as order_count 
                    FROM order_items oi
                    JOIN orders o ON oi.order_id = o.id
                    WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY menu_item_id
                ) oi ON mi.id = oi.menu_item_id
                WHERE mi.is_available = 1 AND c.is_active = 1 
                ORDER BY oi.order_count DESC, mi.display_order ASC 
                LIMIT ?";
        return $this->db->fetchAll($sql, [$limit]);
    }
    
    /**
     * Get items by multiple IDs (for cart)
     */
    public function getItemsByIds($itemIds) {
        if (empty($itemIds)) {
            return [];
        }
        
        $placeholders = str_repeat('?,', count($itemIds) - 1) . '?';
        $sql = "SELECT mi.*, c.name as category_name,
                       mi.preparation_time,
                       COALESCE(oi.order_count, 0) as order_count,
                       0 as discount_percentage,
                       mi.price as discounted_price
                FROM {$this->table} mi 
                JOIN categories c ON mi.category_id = c.id 
                LEFT JOIN (
                    SELECT menu_item_id, SUM(quantity) as order_count
                    FROM order_items 
                    GROUP BY menu_item_id
                ) oi ON mi.id = oi.menu_item_id
                WHERE mi.id IN ({$placeholders}) AND mi.is_available = 1 AND c.is_active = 1";
        
        return $this->db->fetchAll($sql, $itemIds);
    }
    
    /**
     * Check if item name exists in category
     */
    public function nameExistsInCategory($name, $categoryId, $excludeId = null) {
        $conditions = ['name' => $name, 'category_id' => $categoryId];
        
        if ($excludeId) {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                    WHERE name = ? AND category_id = ? AND id != ?";
            $result = $this->db->fetch($sql, [$name, $categoryId, $excludeId]);
            return $result['count'] > 0;
        }
        
        return $this->exists($conditions);
    }
    
    /**
     * Get next sort order for category
     */
    public function getNextSortOrderForCategory($categoryId) {
        $sql = "SELECT MAX(display_order) as max_order FROM {$this->table} WHERE category_id = ?";
        $result = $this->db->fetch($sql, [$categoryId]);
        return ($result['max_order'] ?? 0) + 1;
    }
    
    /**
     * Toggle item availability
     */
    public function toggleAvailability($itemId) {
        $item = $this->find($itemId);
        if (!$item) {
            return false;
        }
        
        $newStatus = $item['is_available'] ? 0 : 1;
        return $this->update($itemId, ['is_available' => $newStatus]);
    }
    
    /**
     * Update item price
     */
    public function updatePrice($itemId, $newPrice) {
        return $this->update($itemId, ['price' => $newPrice]);
    }
    
    /**
     * Get items for admin (including unavailable)
     */
    public function getAllForAdmin($categoryId = null) {
        $sql = "SELECT mi.*, c.name as category_name 
                FROM {$this->table} mi 
                JOIN categories c ON mi.category_id = c.id";
        
        $params = [];
        
        if ($categoryId) {
            $sql .= " WHERE mi.category_id = ?";
            $params[] = $categoryId;
        }
        
        $sql .= " ORDER BY c.display_order ASC, mi.display_order ASC, mi.name ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get menu statistics
     */
    public function getMenuStats() {
        $stats = [];
        
        // Total items
        $stats['total_items'] = $this->count();
        
        // Available items
        $stats['available_items'] = $this->count(['is_available' => 1]);
        
        // Vegetarian items
        $stats['vegetarian_items'] = $this->count(['is_vegetarian' => 1, 'is_available' => 1]);
        
        // Vegan items
        $stats['vegan_items'] = $this->count(['is_vegan' => 1, 'is_available' => 1]);
        
        // Average price
        $sql = "SELECT AVG(price) as avg_price FROM {$this->table} WHERE is_available = 1";
        $result = $this->db->fetch($sql);
        $stats['average_price'] = round($result['avg_price'] ?? 0, 2);
        
        // Price range
        $sql = "SELECT MIN(price) as min_price, MAX(price) as max_price 
                FROM {$this->table} WHERE is_available = 1";
        $result = $this->db->fetch($sql);
        $stats['price_range'] = [
            'min' => $result['min_price'] ?? 0,
            'max' => $result['max_price'] ?? 0
        ];
        
        return $stats;
    }
    
    /**
     * Get popular items report
     */
    public function getPopularItemsReport($days = 30, $limit = 10) {
        $sql = "SELECT mi.name, mi.price, c.name as category_name,
                       SUM(oi.quantity) as total_ordered,
                       SUM(oi.total_price) as total_revenue,
                       COUNT(DISTINCT oi.order_id) as order_count
                FROM {$this->table} mi
                JOIN categories c ON mi.category_id = c.id
                JOIN order_items oi ON mi.id = oi.menu_item_id
                JOIN orders o ON oi.order_id = o.id
                WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                      AND o.status NOT IN ('cancelled')
                GROUP BY mi.id
                ORDER BY total_ordered DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$days, $limit]);
    }
}
