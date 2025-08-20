<?php
/**
 * Category Model
 * Friends and Momos Restaurant Management System
 */

require_once 'BaseModel.php';

class Category extends BaseModel {
    protected $table = 'categories';
    protected $fillable = [
        'name', 'description', 'image', 'display_order', 'is_active'
    ];
    
    /**
     * Get all active categories
     */
    public function getActiveCategories() {
        return $this->findAll(['is_active' => 1], 'display_order ASC, name ASC');
    }
    
    /**
     * Get category with menu items count
     */
    public function getCategoriesWithItemCount() {
        $sql = "SELECT c.*, COUNT(mi.id) as item_count 
                FROM {$this->table} c 
                LEFT JOIN menu_items mi ON c.id = mi.category_id AND mi.is_available = 1
                WHERE c.is_active = 1 
                GROUP BY c.id 
                ORDER BY c.display_order ASC, c.name ASC";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Check if category name exists
     */
    public function nameExists($name, $excludeId = null) {
        $conditions = ['name' => $name];
        
        if ($excludeId) {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE name = ? AND id != ?";
            $result = $this->db->fetch($sql, [$name, $excludeId]);
            return $result['count'] > 0;
        }
        
        return $this->exists($conditions);
    }
    
    /**
     * Get next sort order
     */
    public function getNextSortOrder() {
        $sql = "SELECT MAX(display_order) as max_order FROM {$this->table}";
        $result = $this->db->fetch($sql);
        return ($result['max_order'] ?? 0) + 1;
    }
    
    /**
     * Update sort order
     */
    public function updateSortOrder($categoryId, $newOrder) {
        return $this->update($categoryId, ['display_order' => $newOrder]);
    }
    
    /**
     * Toggle category status
     */
    public function toggleStatus($categoryId) {
        $category = $this->find($categoryId);
        if (!$category) {
            return false;
        }
        
        $newStatus = $category['is_active'] ? 0 : 1;
        return $this->update($categoryId, ['is_active' => $newStatus]);
    }
    
    /**
     * Get categories for admin (including inactive)
     */
    public function getAllForAdmin() {
        return $this->findAll([], 'display_order ASC, name ASC');
    }
    
    /**
     * Delete category (only if no menu items)
     */
    public function deleteCategory($categoryId) {
        // Check if category has menu items
        $sql = "SELECT COUNT(*) as count FROM menu_items WHERE category_id = ?";
        $result = $this->db->fetch($sql, [$categoryId]);
        
        if ($result['count'] > 0) {
            return ['success' => false, 'message' => 'Cannot delete category with existing menu items'];
        }
        
        $deleted = $this->delete($categoryId);
        return [
            'success' => $deleted,
            'message' => $deleted ? 'Category deleted successfully' : 'Failed to delete category'
        ];
    }
}
