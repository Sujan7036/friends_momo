<?php
/**
 * Restaurant Configuration
 * Friends and Momos Restaurant Management System
 */

class RestaurantConfig {
    private $db;
    private $settings = [];
    
    public function __construct($database) {
        $this->db = $database;
        $this->loadSettings();
    }
    
    /**
     * Load all settings from database
     */
    private function loadSettings() {
        try {
            $stmt = $this->db->prepare("SELECT setting_key, setting_value, setting_type FROM settings");
            $stmt->execute();
            $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($settings as $setting) {
                $value = $setting['setting_value'];
                
                // Convert value based on type
                switch ($setting['setting_type']) {
                    case 'number':
                        $value = is_numeric($value) ? (float) $value : 0;
                        break;
                    case 'boolean':
                        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                        break;
                    case 'json':
                        $value = json_decode($value, true) ?: [];
                        break;
                    default:
                        // string type, keep as is
                        break;
                }
                
                $this->settings[$setting['setting_key']] = $value;
            }
        } catch (PDOException $e) {
            error_log("Failed to load settings: " . $e->getMessage());
        }
    }
    
    /**
     * Get a setting value
     */
    public function get($key, $default = null) {
        return isset($this->settings[$key]) ? $this->settings[$key] : $default;
    }
    
    /**
     * Set a setting value
     */
    public function set($key, $value, $type = 'string') {
        try {
            // Convert value for storage
            $storageValue = $value;
            if ($type === 'json') {
                $storageValue = json_encode($value);
            } elseif ($type === 'boolean') {
                $storageValue = $value ? '1' : '0';
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO settings (setting_key, setting_value, setting_type) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                setting_value = VALUES(setting_value), 
                setting_type = VALUES(setting_type),
                updated_at = CURRENT_TIMESTAMP
            ");
            
            $result = $stmt->execute([$key, $storageValue, $type]);
            
            if ($result) {
                $this->settings[$key] = $value;
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Failed to set setting: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all settings
     */
    public function all() {
        return $this->settings;
    }
    
    /**
     * Restaurant Information
     */
    public function getRestaurantName() {
        return $this->get('restaurant_name', 'Friends and Momos');
    }
    
    public function getRestaurantPhone() {
        return $this->get('restaurant_phone', '+61 2 1234 5678');
    }
    
    public function getRestaurantEmail() {
        return $this->get('restaurant_email', 'info@friendsmomos.com');
    }
    
    public function getRestaurantAddress() {
        return $this->get('restaurant_address', '123 Food Street, Sydney NSW 2000');
    }
    
    /**
     * Operating Hours
     */
    public function getOpeningHours() {
        return $this->get('opening_hours', [
            'monday' => '10:00-22:00',
            'tuesday' => '10:00-22:00',
            'wednesday' => '10:00-22:00',
            'thursday' => '10:00-22:00',
            'friday' => '10:00-23:00',
            'saturday' => '10:00-23:00',
            'sunday' => '10:00-21:00'
        ]);
    }
    
    public function isOpenNow() {
        $openingHours = $this->getOpeningHours();
        $currentDay = strtolower(date('l'));
        $currentTime = date('H:i');
        
        if (!isset($openingHours[$currentDay])) {
            return false;
        }
        
        $dayHours = $openingHours[$currentDay];
        if ($dayHours === 'closed') {
            return false;
        }
        
        list($openTime, $closeTime) = explode('-', $dayHours);
        
        return $currentTime >= $openTime && $currentTime <= $closeTime;
    }
    
    /**
     * Pricing Configuration
     */
    public function getTaxRate() {
        return $this->get('tax_rate', 0.10);
    }
    
    public function getDeliveryFee() {
        return $this->get('delivery_fee', 5.00);
    }
    
    public function getFreeDeliveryMinimum() {
        return $this->get('free_delivery_minimum', 50.00);
    }
    
    public function getCurrencySymbol() {
        return $this->get('currency_symbol', '$');
    }
    
    /**
     * Loyalty Program
     */
    public function getLoyaltyPointsRate() {
        return $this->get('loyalty_points_rate', 1); // Points per dollar spent
    }
    
    public function getLoyaltyRedemptionRate() {
        return $this->get('loyalty_redemption_rate', 100); // Points needed for $1
    }
    
    /**
     * Reservation Settings
     */
    public function getMaxPartySize() {
        return $this->get('max_party_size', 12);
    }
    
    public function getAdvanceBookingDays() {
        return $this->get('advance_booking_days', 30);
    }
    
    /**
     * Notification Settings
     */
    public function getNotificationEmail() {
        return $this->get('notification_email', 'admin@friendsmomos.com');
    }
    
    /**
     * System Settings
     */
    public function getTimezone() {
        return $this->get('timezone', 'Australia/Sydney');
    }
    
    /**
     * Calculate tax amount
     */
    public function calculateTax($amount) {
        return round($amount * $this->getTaxRate(), 2);
    }
    
    /**
     * Calculate delivery fee
     */
    public function calculateDeliveryFee($orderAmount) {
        $freeDeliveryMin = $this->getFreeDeliveryMinimum();
        return $orderAmount >= $freeDeliveryMin ? 0 : $this->getDeliveryFee();
    }
    
    /**
     * Calculate loyalty points earned
     */
    public function calculateLoyaltyPoints($amount) {
        return floor($amount * $this->getLoyaltyPointsRate());
    }
    
    /**
     * Calculate discount from loyalty points
     */
    public function calculateLoyaltyDiscount($points) {
        $redemptionRate = $this->getLoyaltyRedemptionRate();
        return floor($points / $redemptionRate);
    }
    
    /**
     * Format currency
     */
    public function formatCurrency($amount) {
        $symbol = $this->getCurrencySymbol();
        return $symbol . number_format($amount, 2);
    }
    
    /**
     * Get available time slots for reservations
     */
    public function getAvailableTimeSlots() {
        return [
            '10:00', '10:30', '11:00', '11:30',
            '12:00', '12:30', '13:00', '13:30',
            '14:00', '14:30', '15:00', '15:30',
            '16:00', '16:30', '17:00', '17:30',
            '18:00', '18:30', '19:00', '19:30',
            '20:00', '20:30', '21:00', '21:30'
        ];
    }
    
    /**
     * Check if time slot is within operating hours
     */
    public function isTimeSlotValid($date, $time) {
        $dayOfWeek = strtolower(date('l', strtotime($date)));
        $openingHours = $this->getOpeningHours();
        
        if (!isset($openingHours[$dayOfWeek]) || $openingHours[$dayOfWeek] === 'closed') {
            return false;
        }
        
        list($openTime, $closeTime) = explode('-', $openingHours[$dayOfWeek]);
        
        return $time >= $openTime && $time <= $closeTime;
    }
    
    /**
     * Get restaurant capacity per time slot
     */
    public function getRestaurantCapacity() {
        return $this->get('restaurant_capacity', 50);
    }
    
    /**
     * Business rule validations
     */
    public function validateOrderMinimum($amount, $orderType = 'dine_in') {
        $minimumOrders = $this->get('minimum_orders', [
            'dine_in' => 0,
            'takeaway' => 0,
            'delivery' => 20.00
        ]);
        
        $minimum = isset($minimumOrders[$orderType]) ? $minimumOrders[$orderType] : 0;
        
        return $amount >= $minimum;
    }
    
    /**
     * Get prep time buffer (additional time added to estimated prep time)
     */
    public function getPrepTimeBuffer() {
        return $this->get('prep_time_buffer', 5); // minutes
    }
    
    /**
     * Check if online ordering is enabled
     */
    public function isOnlineOrderingEnabled() {
        return $this->get('online_ordering_enabled', true);
    }
    
    /**
     * Check if reservations are enabled
     */
    public function isReservationsEnabled() {
        return $this->get('reservations_enabled', true);
    }
    
    /**
     * Check if delivery is available
     */
    public function isDeliveryAvailable() {
        return $this->get('delivery_available', true);
    }
    
    /**
     * Get delivery radius (in kilometers)
     */
    public function getDeliveryRadius() {
        return $this->get('delivery_radius', 10);
    }
    
    /**
     * Get social media links
     */
    public function getSocialMediaLinks() {
        return $this->get('social_media', [
            'facebook' => '',
            'instagram' => '',
            'twitter' => '',
            'tiktok' => ''
        ]);
    }
    
    /**
     * Update multiple settings at once
     */
    public function updateSettings($settings) {
        $updated = 0;
        
        foreach ($settings as $key => $data) {
            $value = $data['value'] ?? $data;
            $type = $data['type'] ?? 'string';
            
            if ($this->set($key, $value, $type)) {
                $updated++;
            }
        }
        
        return $updated;
    }
    
    /**
     * Get settings for admin panel
     */
    public function getAdminSettings() {
        $adminSettings = [];
        
        try {
            $stmt = $this->db->prepare("
                SELECT setting_key, setting_value, setting_type, description, is_editable 
                FROM settings 
                WHERE is_editable = 1 
                ORDER BY setting_key
            ");
            $stmt->execute();
            $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($settings as $setting) {
                $value = $setting['setting_value'];
                
                // Convert value for display
                switch ($setting['setting_type']) {
                    case 'number':
                        $value = is_numeric($value) ? (float) $value : 0;
                        break;
                    case 'boolean':
                        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                        break;
                    case 'json':
                        $value = json_decode($value, true) ?: [];
                        break;
                }
                
                $adminSettings[] = [
                    'key' => $setting['setting_key'],
                    'value' => $value,
                    'type' => $setting['setting_type'],
                    'description' => $setting['description']
                ];
            }
        } catch (PDOException $e) {
            error_log("Failed to get admin settings: " . $e->getMessage());
        }
        
        return $adminSettings;
    }
    
    /**
     * Backup settings to file
     */
    public function backupSettings($filePath) {
        try {
            $settings = $this->all();
            $backup = [
                'timestamp' => date('Y-m-d H:i:s'),
                'settings' => $settings
            ];
            
            return file_put_contents($filePath, json_encode($backup, JSON_PRETTY_PRINT)) !== false;
        } catch (Exception $e) {
            error_log("Failed to backup settings: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Restore settings from file
     */
    public function restoreSettings($filePath) {
        try {
            if (!file_exists($filePath)) {
                return false;
            }
            
            $content = file_get_contents($filePath);
            $backup = json_decode($content, true);
            
            if (!isset($backup['settings'])) {
                return false;
            }
            
            $restored = 0;
            foreach ($backup['settings'] as $key => $value) {
                // Determine type based on value
                $type = 'string';
                if (is_bool($value)) {
                    $type = 'boolean';
                } elseif (is_numeric($value)) {
                    $type = 'number';
                } elseif (is_array($value)) {
                    $type = 'json';
                }
                
                if ($this->set($key, $value, $type)) {
                    $restored++;
                }
            }
            
            return $restored;
        } catch (Exception $e) {
            error_log("Failed to restore settings: " . $e->getMessage());
            return false;
        }
    }
}
?>
