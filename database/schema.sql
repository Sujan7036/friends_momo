-- Friends and Momos Restaurant Management System
-- Database Schema

-- Create database
CREATE DATABASE IF NOT EXISTS friends_momo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE friends_momo;

-- Users table for authentication and user management
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin', 'staff') DEFAULT 'customer',
    is_active BOOLEAN DEFAULT TRUE,
    email_verified BOOLEAN DEFAULT FALSE,
    email_verification_token VARCHAR(255),
    password_reset_token VARCHAR(255),
    password_reset_expires DATETIME,
    remember_token VARCHAR(255),
    login_attempts INT DEFAULT 0,
    last_login_attempt DATETIME,
    last_login_ip VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_created_at (created_at)
);

-- Categories table for menu organization
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    display_order INT DEFAULT 999,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_display_order (display_order),
    INDEX idx_is_active (is_active)
);

-- Menu items table
CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255),
    ingredients TEXT,
    allergens VARCHAR(255),
    nutritional_info JSON,
    prep_time INT DEFAULT 15, -- in minutes
    is_available BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    display_order INT DEFAULT 999,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_category (category_id),
    INDEX idx_is_available (is_available),
    INDEX idx_is_featured (is_featured),
    INDEX idx_price (price)
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    status ENUM('pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled') DEFAULT 'pending',
    order_type ENUM('dine_in', 'takeaway', 'delivery') DEFAULT 'dine_in',
    subtotal DECIMAL(10, 2) NOT NULL,
    tax_amount DECIMAL(10, 2) DEFAULT 0,
    delivery_fee DECIMAL(10, 2) DEFAULT 0,
    discount_amount DECIMAL(10, 2) DEFAULT 0,
    total_amount DECIMAL(10, 2) NOT NULL,
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_reference VARCHAR(100),
    customer_name VARCHAR(100),
    customer_email VARCHAR(100),
    customer_phone VARCHAR(20),
    delivery_address TEXT,
    special_instructions TEXT,
    estimated_ready_time DATETIME,
    completed_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_order_type (order_type),
    INDEX idx_payment_status (payment_status),
    INDEX idx_created_at (created_at),
    INDEX idx_order_number (order_number)
);

-- Order items table (items within an order)
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_item_id INT,
    item_name VARCHAR(100) NOT NULL, -- Store name in case menu item is deleted
    item_price DECIMAL(10, 2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    special_instructions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE SET NULL,
    INDEX idx_order (order_id),
    INDEX idx_menu_item (menu_item_id)
);

-- Reservations table
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    reservation_number VARCHAR(20) UNIQUE NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100),
    customer_phone VARCHAR(20) NOT NULL,
    party_size INT NOT NULL,
    reservation_date DATE NOT NULL,
    reservation_time TIME NOT NULL,
    status ENUM('pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show') DEFAULT 'pending',
    table_number VARCHAR(10),
    special_requests TEXT,
    notes TEXT, -- Staff notes
    confirmed_at DATETIME,
    seated_at DATETIME,
    completed_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_date_time (reservation_date, reservation_time),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Tables management
CREATE TABLE IF NOT EXISTS restaurant_tables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_number VARCHAR(10) UNIQUE NOT NULL,
    capacity INT NOT NULL,
    location VARCHAR(50), -- e.g., 'window', 'center', 'private'
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_capacity (capacity),
    INDEX idx_is_available (is_available)
);

-- Inventory management
CREATE TABLE IF NOT EXISTS inventory_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50),
    unit VARCHAR(20) NOT NULL, -- kg, liter, piece, etc.
    current_stock DECIMAL(10, 2) DEFAULT 0,
    minimum_stock DECIMAL(10, 2) DEFAULT 0,
    cost_per_unit DECIMAL(10, 2),
    supplier VARCHAR(100),
    last_restocked DATETIME,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_category (category),
    INDEX idx_current_stock (current_stock),
    INDEX idx_is_active (is_active)
);

-- Staff management
CREATE TABLE IF NOT EXISTS staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE,
    employee_id VARCHAR(20) UNIQUE,
    position VARCHAR(50),
    department VARCHAR(50),
    hire_date DATE,
    salary DECIMAL(10, 2),
    is_active BOOLEAN DEFAULT TRUE,
    emergency_contact_name VARCHAR(100),
    emergency_contact_phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_position (position),
    INDEX idx_department (department),
    INDEX idx_is_active (is_active)
);

-- Customer loyalty program
CREATE TABLE IF NOT EXISTS loyalty_points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_id INT,
    points_earned INT DEFAULT 0,
    points_used INT DEFAULT 0,
    transaction_type ENUM('earned', 'redeemed', 'expired', 'bonus') DEFAULT 'earned',
    description VARCHAR(255),
    expires_at DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_expires_at (expires_at),
    INDEX idx_transaction_type (transaction_type)
);

-- Reviews and ratings
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_id INT,
    menu_item_id INT,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(100),
    comment TEXT,
    is_approved BOOLEAN DEFAULT FALSE,
    response TEXT, -- Restaurant response
    responded_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_order (order_id),
    INDEX idx_menu_item (menu_item_id),
    INDEX idx_rating (rating),
    INDEX idx_is_approved (is_approved)
);

-- System settings
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    is_editable BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_setting_key (setting_key)
);

-- Activity logs for audit trail
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50), -- orders, menu_items, users, etc.
    entity_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created_at (created_at)
);

-- Notifications system
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    data JSON, -- Additional data
    is_read BOOLEAN DEFAULT FALSE,
    read_at DATETIME,
    expires_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_type (type),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
);

-- Coupons and discounts
CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    type ENUM('percentage', 'fixed_amount', 'free_delivery') NOT NULL,
    value DECIMAL(10, 2) NOT NULL,
    minimum_order_amount DECIMAL(10, 2) DEFAULT 0,
    max_uses INT,
    used_count INT DEFAULT 0,
    user_limit INT DEFAULT 1, -- How many times one user can use it
    starts_at DATETIME,
    expires_at DATETIME,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_code (code),
    INDEX idx_is_active (is_active),
    INDEX idx_expires_at (expires_at)
);

-- Coupon usage tracking
CREATE TABLE IF NOT EXISTS coupon_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    coupon_id INT NOT NULL,
    user_id INT,
    order_id INT,
    discount_amount DECIMAL(10, 2) NOT NULL,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    INDEX idx_coupon (coupon_id),
    INDEX idx_user (user_id)
);

-- Insert default categories
INSERT INTO categories (name, description, display_order) VALUES
('Momos', 'Traditional Nepalese dumplings with various fillings', 1),
('Chinese', 'Popular Chinese dishes and noodles', 2),
('Snacks', 'Light bites and appetizers', 3),
('Beverages', 'Drinks and refreshments', 4),
('Desserts', 'Sweet treats to end your meal', 5);

-- Insert sample menu items
INSERT INTO menu_items (category_id, name, description, price, prep_time, is_featured) VALUES
(1, 'Steam Momo', 'Traditional steamed momos filled with seasoned meat and vegetables', 12.99, 20, TRUE),
(1, 'Fried Momo', 'Crispy fried momos with your choice of filling', 14.99, 15, TRUE),
(1, 'Jhol Momo', 'Momos served in spicy tomato-based soup', 16.99, 25, FALSE),
(2, 'Chicken Chowmein', 'Stir-fried noodles with chicken and vegetables', 15.99, 12, TRUE),
(2, 'Laphing', 'Cold noodles with spicy sauce and vegetables', 11.99, 8, FALSE),
(3, 'Pani Puri', 'Crispy shells filled with spiced water and chutneys', 8.99, 5, FALSE),
(3, 'Aloo Nimki', 'Spiced potato with crispy rice flakes', 7.99, 10, FALSE),
(4, 'Mango Lassi', 'Creamy yogurt drink with fresh mango', 5.99, 3, FALSE),
(4, 'Masala Chai', 'Spiced tea with milk and aromatic spices', 3.99, 5, FALSE),
(5, 'Gulab Jamun', 'Sweet milk dumplings in sugar syrup', 6.99, 5, FALSE);

-- Insert restaurant tables
INSERT INTO restaurant_tables (table_number, capacity, location) VALUES
('T1', 2, 'window'),
('T2', 2, 'window'),
('T3', 4, 'center'),
('T4', 4, 'center'),
('T5', 6, 'center'),
('T6', 8, 'private'),
('T7', 2, 'bar'),
('T8', 2, 'bar');

-- Insert default admin user (password: admin123)
INSERT INTO users (first_name, last_name, email, password_hash, role, is_active, email_verified) VALUES
('Admin', 'User', 'admin@friendsmomos.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', TRUE, TRUE);

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES
('restaurant_name', 'Friends and Momos', 'string', 'Restaurant name'),
('restaurant_phone', '+61 2 1234 5678', 'string', 'Restaurant contact phone'),
('restaurant_email', 'info@friendsmomos.com', 'string', 'Restaurant contact email'),
('restaurant_address', '123 Food Street, Sydney NSW 2000', 'string', 'Restaurant address'),
('opening_hours', '{"monday":"10:00-22:00","tuesday":"10:00-22:00","wednesday":"10:00-22:00","thursday":"10:00-22:00","friday":"10:00-23:00","saturday":"10:00-23:00","sunday":"10:00-21:00"}', 'json', 'Restaurant opening hours'),
('tax_rate', '0.10', 'number', 'Tax rate (GST)'),
('delivery_fee', '5.00', 'number', 'Standard delivery fee'),
('free_delivery_minimum', '50.00', 'number', 'Minimum order for free delivery'),
('loyalty_points_rate', '1', 'number', 'Points earned per dollar spent'),
('loyalty_redemption_rate', '100', 'number', 'Points needed for $1 discount'),
('max_party_size', '12', 'number', 'Maximum party size for reservations'),
('advance_booking_days', '30', 'number', 'How many days in advance can customers book'),
('notification_email', 'admin@friendsmomos.com', 'string', 'Email for notifications'),
('currency_symbol', '$', 'string', 'Currency symbol'),
('timezone', 'Australia/Sydney', 'string', 'Restaurant timezone');

-- Create views for common queries
CREATE VIEW order_summary AS
SELECT 
    o.id,
    o.order_number,
    o.status,
    o.order_type,
    o.total_amount,
    o.customer_name,
    o.customer_phone,
    o.created_at,
    COUNT(oi.id) as item_count
FROM orders o
LEFT JOIN order_items oi ON o.id = oi.order_id
GROUP BY o.id;

CREATE VIEW menu_items_with_reviews AS
SELECT 
    mi.*,
    COALESCE(AVG(r.rating), 0) as average_rating,
    COUNT(r.id) as review_count
FROM menu_items mi
LEFT JOIN reviews r ON mi.id = r.menu_item_id AND r.is_approved = TRUE
GROUP BY mi.id;

CREATE VIEW user_loyalty_summary AS
SELECT 
    u.id as user_id,
    u.first_name,
    u.last_name,
    u.email,
    COALESCE(SUM(CASE WHEN lp.transaction_type = 'earned' THEN lp.points_earned ELSE 0 END), 0) as total_points_earned,
    COALESCE(SUM(CASE WHEN lp.transaction_type = 'redeemed' THEN lp.points_used ELSE 0 END), 0) as total_points_used,
    COALESCE(SUM(CASE WHEN lp.transaction_type = 'earned' THEN lp.points_earned ELSE 0 END), 0) - 
    COALESCE(SUM(CASE WHEN lp.transaction_type = 'redeemed' THEN lp.points_used ELSE 0 END), 0) as current_points
FROM users u
LEFT JOIN loyalty_points lp ON u.id = lp.user_id
WHERE u.role = 'customer'
GROUP BY u.id;

-- Create indexes for performance
CREATE INDEX idx_orders_status_date ON orders(status, created_at);
CREATE INDEX idx_reservations_date_status ON reservations(reservation_date, status);
CREATE INDEX idx_menu_items_featured_available ON menu_items(is_featured, is_available);
CREATE INDEX idx_loyalty_points_user_type ON loyalty_points(user_id, transaction_type);

-- Create triggers for automatic operations
DELIMITER //

-- Trigger to generate order number
CREATE TRIGGER generate_order_number 
BEFORE INSERT ON orders 
FOR EACH ROW 
BEGIN 
    DECLARE next_num INT;
    SELECT COALESCE(MAX(CAST(SUBSTRING(order_number, 2) AS UNSIGNED)), 0) + 1 INTO next_num FROM orders;
    SET NEW.order_number = CONCAT('O', LPAD(next_num, 6, '0'));
END//

-- Trigger to generate reservation number
CREATE TRIGGER generate_reservation_number 
BEFORE INSERT ON reservations 
FOR EACH ROW 
BEGIN 
    DECLARE next_num INT;
    SELECT COALESCE(MAX(CAST(SUBSTRING(reservation_number, 2) AS UNSIGNED)), 0) + 1 INTO next_num FROM reservations;
    SET NEW.reservation_number = CONCAT('R', LPAD(next_num, 6, '0'));
END//

-- Trigger to award loyalty points on order completion
CREATE TRIGGER award_loyalty_points 
AFTER UPDATE ON orders 
FOR EACH ROW 
BEGIN 
    IF NEW.status = 'completed' AND OLD.status != 'completed' AND NEW.user_id IS NOT NULL THEN
        INSERT INTO loyalty_points (user_id, order_id, points_earned, transaction_type, description)
        VALUES (NEW.user_id, NEW.id, FLOOR(NEW.total_amount), 'earned', CONCAT('Order ', NEW.order_number));
    END IF;
END//

DELIMITER ;

-- Create stored procedures for common operations

DELIMITER //

-- Procedure to get available time slots for reservations
CREATE PROCEDURE GetAvailableTimeSlots(
    IN reservation_date DATE,
    IN party_size INT
)
BEGIN
    DECLARE restaurant_capacity INT DEFAULT 50;
    
    SELECT 
        time_slot,
        (restaurant_capacity - COALESCE(booked_guests, 0)) as available_capacity
    FROM (
        SELECT '10:00:00' as time_slot UNION ALL
        SELECT '10:30:00' UNION ALL
        SELECT '11:00:00' UNION ALL
        SELECT '11:30:00' UNION ALL
        SELECT '12:00:00' UNION ALL
        SELECT '12:30:00' UNION ALL
        SELECT '13:00:00' UNION ALL
        SELECT '13:30:00' UNION ALL
        SELECT '14:00:00' UNION ALL
        SELECT '14:30:00' UNION ALL
        SELECT '15:00:00' UNION ALL
        SELECT '15:30:00' UNION ALL
        SELECT '16:00:00' UNION ALL
        SELECT '16:30:00' UNION ALL
        SELECT '17:00:00' UNION ALL
        SELECT '17:30:00' UNION ALL
        SELECT '18:00:00' UNION ALL
        SELECT '18:30:00' UNION ALL
        SELECT '19:00:00' UNION ALL
        SELECT '19:30:00' UNION ALL
        SELECT '20:00:00' UNION ALL
        SELECT '20:30:00' UNION ALL
        SELECT '21:00:00' UNION ALL
        SELECT '21:30:00'
    ) time_slots
    LEFT JOIN (
        SELECT 
            reservation_time,
            SUM(party_size) as booked_guests
        FROM reservations 
        WHERE reservation_date = reservation_date 
        AND status IN ('confirmed', 'seated')
        GROUP BY reservation_time
    ) bookings ON time_slots.time_slot = bookings.reservation_time
    WHERE (restaurant_capacity - COALESCE(booked_guests, 0)) >= party_size
    ORDER BY time_slot;
END//

-- Procedure to get sales report
CREATE PROCEDURE GetSalesReport(
    IN start_date DATE,
    IN end_date DATE
)
BEGIN
    SELECT 
        DATE(created_at) as sale_date,
        COUNT(*) as total_orders,
        SUM(total_amount) as total_revenue,
        AVG(total_amount) as average_order_value,
        SUM(CASE WHEN order_type = 'dine_in' THEN 1 ELSE 0 END) as dine_in_orders,
        SUM(CASE WHEN order_type = 'takeaway' THEN 1 ELSE 0 END) as takeaway_orders,
        SUM(CASE WHEN order_type = 'delivery' THEN 1 ELSE 0 END) as delivery_orders
    FROM orders 
    WHERE DATE(created_at) BETWEEN start_date AND end_date
    AND status = 'completed'
    GROUP BY DATE(created_at)
    ORDER BY sale_date;
END//

-- Procedure to get popular menu items
CREATE PROCEDURE GetPopularMenuItems(
    IN days_back INT
)
BEGIN
    SELECT 
        mi.id,
        mi.name,
        mi.price,
        COUNT(oi.id) as order_count,
        SUM(oi.quantity) as total_quantity,
        SUM(oi.quantity * oi.item_price) as total_revenue
    FROM menu_items mi
    JOIN order_items oi ON mi.id = oi.menu_item_id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.created_at >= DATE_SUB(CURDATE(), INTERVAL days_back DAY)
    AND o.status = 'completed'
    GROUP BY mi.id, mi.name, mi.price
    ORDER BY total_quantity DESC
    LIMIT 10;
END//

DELIMITER ;

-- Grant permissions (adjust as needed for your setup)
-- GRANT ALL PRIVILEGES ON friends_momo.* TO 'web_user'@'localhost' IDENTIFIED BY 'secure_password';
-- FLUSH PRIVILEGES;
