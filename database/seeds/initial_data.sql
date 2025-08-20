-- Seed data for Restaurant Tables
INSERT INTO `restaurant_tables` (`table_number`, `capacity`, `location`, `is_available`, `description`) VALUES
('T01', 2, 'Window Side', 1, 'Intimate table for two by the window'),
('T02', 2, 'Window Side', 1, 'Cozy window table for couples'),
('T03', 4, 'Main Hall', 1, 'Family table in the main dining area'),
('T04', 4, 'Main Hall', 1, 'Central table perfect for families'),
('T05', 4, 'Main Hall', 1, 'Spacious table in main dining area'),
('T06', 6, 'Main Hall', 1, 'Large table for groups'),
('T07', 6, 'Corner', 1, 'Corner table with privacy for larger groups'),
('T08', 8, 'Private Area', 1, 'Large table in semi-private area'),
('T09', 4, 'Patio', 1, 'Outdoor patio table (weather permitting)'),
('T10', 4, 'Patio', 1, 'Outdoor dining with garden view'),
('T11', 2, 'Bar Area', 1, 'High table near the bar counter'),
('T12', 2, 'Bar Area', 1, 'Bar-style seating for quick meals');

-- Create default admin user
-- Password: admin123 (hashed with password_hash())
INSERT INTO `users` (`first_name`, `last_name`, `email`, `password`, `phone`, `address`, `role`, `email_verified`, `is_active`) VALUES
('Admin', 'User', 'admin@friendsandmomos.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+61 2 1234 5678', 'Gungahlin, ACT, Australia', 'admin', 1, 1);

-- Create sample staff user
-- Password: staff123
INSERT INTO `users` (`first_name`, `last_name`, `email`, `password`, `phone`, `role`, `email_verified`, `is_active`) VALUES
('Kitchen', 'Staff', 'kitchen@friendsandmomos.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+61 2 1234 5679', 'staff', 1, 1);

-- Create sample customer user
-- Password: customer123
INSERT INTO `users` (`first_name`, `last_name`, `email`, `password`, `phone`, `address`, `role`, `email_verified`, `is_active`) VALUES
('John', 'Doe', 'john.doe@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+61 400 123 456', '123 Sample Street, Gungahlin ACT 2912', 'customer', 1, 1);
