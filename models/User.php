<?php
/**
 * User Model
 * Friends and Momos Restaurant Management System
 */

require_once 'BaseModel.php';

class User extends BaseModel {
    protected $table = 'users';
    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone', 'password_hash', 
        'role', 'is_active', 'email_verified', 'email_verification_token',
        'password_reset_token', 'password_reset_expires', 'remember_token',
        'login_attempts', 'last_login_attempt', 'last_login_ip'
    ];
    
    /**
     * Get user by ID - Alias for find() method
     */
    public function getById($id) {
        return $this->find($id);
    }
    
    /**
     * Create new user with password hashing
     */
    public function createUser($data) {
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }
        
        if (!isset($data['email_verification_token'])) {
            $data['email_verification_token'] = bin2hex(random_bytes(32));
        }
        
        return $this->create($data);
    }
    
    /**
     * Get customers with order statistics
     */
    public function getCustomersWithStats($search = '', $status = '') {
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            
            $sql = "SELECT u.*, 
                          COUNT(o.id) as order_count,
                          COALESCE(SUM(o.total_amount), 0) as total_spent
                   FROM users u
                   LEFT JOIN orders o ON u.id = o.user_id
                   WHERE u.role = 'customer'";
            
            $params = [];
            
            if (!empty($search)) {
                $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
                $searchTerm = "%{$search}%";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            }
            
            if ($status !== '') {
                $sql .= " AND u.is_active = ?";
                $params[] = (int)$status;
            }
            
            $sql .= " GROUP BY u.id ORDER BY u.created_at DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching customers: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get customer statistics
     */
    public function getCustomerStats() {
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            
            $sql = "SELECT 
                       COUNT(*) as total,
                       SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                       SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH) THEN 1 ELSE 0 END) as new_this_month,
                       COUNT(DISTINCT o.user_id) as with_orders
                   FROM users u
                   LEFT JOIN orders o ON u.id = o.user_id
                   WHERE u.role = 'customer'";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching customer stats: " . $e->getMessage());
            return [
                'total' => 0,
                'active' => 0,
                'new_this_month' => 0,
                'with_orders' => 0
            ];
        }
    }
    
    /**
     * Get staff members for order assignment
     */
    public function getStaffMembers() {
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            
            $sql = "SELECT id, CONCAT(first_name, ' ', last_name) as name 
                   FROM users 
                   WHERE role IN ('admin', 'staff') AND is_active = 1
                   ORDER BY first_name, last_name";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching staff members: " . $e->getMessage());
            return [];
        }
        
        return $this->create($data);
    }
    
    /**
     * Authenticate user login
     */
    public function authenticate($email, $password) {
        $user = $this->findByEmail($email);
        
        if ($user && $user['is_active']) {
            // Check both possible password column names
            $passwordHash = $user['password_hash'] ?? $user['password'] ?? null;
            
            if ($passwordHash && password_verify($password, $passwordHash)) {
                // Update last login
                $this->update($user['id'], [
                    'last_login_attempt' => date('Y-m-d H:i:s'),
                    'last_login_ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'login_attempts' => 0
                ]);
                
                return $user;
            }
        }
        
        // Increment login attempts on failure
        if ($user) {
            $currentAttempts = isset($user['login_attempts']) ? $user['login_attempts'] : 0;
            $this->update($user['id'], [
                'login_attempts' => $currentAttempts + 1,
                'last_login_attempt' => date('Y-m-d H:i:s')
            ]);
        }
        
        return false;
    }
    
    /**
     * Find user by email
     */
    public function findByEmail($email) {
        return $this->findBy(['email' => $email]);
    }
    
    /**
     * Login method (alias for authenticate)
     */
    public function login($email, $password) {
        return $this->authenticate($email, $password);
    }
    
    /**
     * Check if email exists
     */
    public function emailExists($email, $excludeId = null) {
        $conditions = ['email' => $email];
        
        if ($excludeId) {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = ? AND id != ?";
            $result = $this->db->fetch($sql, [$email, $excludeId]);
            return $result['count'] > 0;
        }
        
        return $this->exists($conditions);
    }
    
    /**
     * Update password
     */
    public function updatePassword($userId, $newPassword) {
        $hashedPassword = hashPassword($newPassword);
        return $this->update($userId, ['password' => $hashedPassword]);
    }
    
    /**
     * Generate password reset token
     */
    public function generateResetToken($email) {
        $user = $this->findByEmail($email);
        
        if (!$user) {
            return false;
        }
        
        $token = generateRandomString();
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $updated = $this->update($user['id'], [
            'reset_token' => $token,
            'reset_token_expires' => $expires
        ]);
        
        return $updated ? $token : false;
    }
    
    /**
     * Verify reset token
     */
    public function verifyResetToken($token) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE reset_token = ? AND reset_token_expires > NOW() AND is_active = 1 
                LIMIT 1";
        return $this->db->fetch($sql, [$token]);
    }
    
    /**
     * Reset password using token
     */
    public function resetPassword($token, $newPassword) {
        $user = $this->verifyResetToken($token);
        
        if (!$user) {
            return false;
        }
        
        $hashedPassword = hashPassword($newPassword);
        
        return $this->update($user['id'], [
            'password' => $hashedPassword,
            'reset_token' => null,
            'reset_token_expires' => null
        ]);
    }
    
    /**
     * Verify email address
     */
    public function verifyEmail($token) {
        $user = $this->findBy(['verification_token' => $token, 'email_verified' => 0]);
        
        if (!$user) {
            return false;
        }
        
        return $this->update($user['id'], [
            'email_verified' => 1,
            'verification_token' => null
        ]);
    }
    
    /**
     * Get all customers
     */
    public function getCustomers($limit = null) {
        return $this->findAll(['role' => 'customer', 'is_active' => 1], 'first_name ASC', $limit);
    }
    
    /**
     * Get all staff members
     */
    public function getStaff($limit = null) {
        $conditions = "role IN ('staff', 'admin') AND is_active = 1";
        $sql = "SELECT * FROM {$this->table} WHERE {$conditions} ORDER BY role DESC, first_name ASC";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Search users
     */
    public function searchUsers($searchTerm, $role = null, $limit = 50) {
        $searchFields = ['first_name', 'last_name', 'email', 'phone'];
        $conditions = ['is_active' => 1];
        
        if ($role) {
            $conditions['role'] = $role;
        }
        
        return $this->search($searchTerm, $searchFields, $conditions, 'first_name ASC', $limit);
    }
    
    /**
     * Get user statistics
     */
    public function getUserStats() {
        $stats = [];
        
        // Total customers
        $stats['total_customers'] = $this->count(['role' => 'customer', 'is_active' => 1]);
        
        // New customers this month
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE role = 'customer' AND is_active = 1 
                AND DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')";
        $result = $this->db->fetch($sql);
        $stats['new_customers_this_month'] = $result['count'];
        
        // Active customers (logged in within last 30 days)
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE role = 'customer' AND is_active = 1 
                AND last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $result = $this->db->fetch($sql);
        $stats['active_customers'] = $result['count'];
        
        // Verified emails
        $stats['verified_emails'] = $this->count(['email_verified' => 1, 'is_active' => 1]);
        
        return $stats;
    }
    
    /**
     * Get user's full name
     */
    public function getFullName($user) {
        if (is_array($user)) {
            return trim($user['first_name'] . ' ' . $user['last_name']);
        } elseif (is_numeric($user)) {
            $userData = $this->find($user);
            return $userData ? trim($userData['first_name'] . ' ' . $userData['last_name']) : '';
        }
        return '';
    }
    
    /**
     * Activate/Deactivate user
     */
    public function toggleUserStatus($userId) {
        $user = $this->find($userId);
        if (!$user) {
            return false;
        }
        
        $newStatus = $user['is_active'] ? 0 : 1;
        return $this->update($userId, ['is_active' => $newStatus]);
    }
    
    /**
     * Update user profile
     */
    public function updateProfile($userId, $data) {
        // Remove sensitive fields that shouldn't be updated through profile
        unset($data['password'], $data['role'], $data['email_verified'], $data['verification_token']);
        
        // If email is being updated, require reverification
        if (isset($data['email'])) {
            $data['email_verified'] = 0;
            $data['verification_token'] = generateRandomString();
        }
        
        return $this->update($userId, $data);
    }
    
    /**
     * Get recent registrations
     */
    public function getRecentRegistrations($days = 7, $limit = 10) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) 
                AND is_active = 1 
                ORDER BY created_at DESC 
                LIMIT ?";
        return $this->db->fetchAll($sql, [$days, $limit]);
    }
}
