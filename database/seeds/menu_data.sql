-- Seed data for Categories
INSERT INTO `categories` (`name`, `description`, `image`, `sort_order`, `is_active`) VALUES
('Main Dishes', 'Our signature main dishes featuring traditional Himalayan cuisine', 'main-dishes.jpg', 1, 1),
('Momos', 'Traditional Nepalese dumplings steamed or fried to perfection', 'momos.jpg', 2, 1),
('Noodles', 'Authentic Himalayan noodle dishes with rich flavors', 'noodles.jpg', 3, 1),
('Street Food', 'Popular street food favorites from the Himalayas', 'street-food.jpg', 4, 1),
('Beverages', 'Traditional and modern beverages to complement your meal', 'beverages.jpg', 5, 1);

-- Seed data for Menu Items
INSERT INTO `menu_items` (`category_id`, `name`, `description`, `price`, `image`, `is_available`, `preparation_time`, `calories`, `spice_level`, `is_vegetarian`, `is_vegan`, `is_gluten_free`, `ingredients`, `allergens`, `sort_order`) VALUES

-- Momos Category (ID: 2)
(2, 'Steam Momo (Chicken)', 'Traditional steamed chicken dumplings served with spicy tomato chutney', 12.99, 'momo-chicken-steam.jpg', 1, 20, 280, 'medium', 0, 0, 0, 'Chicken mince, flour, onion, ginger, garlic, spices', 'Gluten, may contain traces of soy', 1),
(2, 'Steam Momo (Vegetable)', 'Fresh vegetables wrapped in delicate dumpling skin, steamed to perfection', 11.99, 'momo-veg-steam.jpg', 1, 18, 220, 'mild', 1, 1, 0, 'Mixed vegetables, flour, onion, ginger, garlic, spices', 'Gluten', 2),
(2, 'Fried Momo (Chicken)', 'Crispy golden fried chicken dumplings with a crunchy exterior', 13.99, 'momo-chicken-fried.jpg', 1, 25, 320, 'medium', 0, 0, 0, 'Chicken mince, flour, onion, ginger, garlic, spices', 'Gluten, may contain traces of soy', 3),
(2, 'Fried Momo (Vegetable)', 'Crispy fried vegetable dumplings with a perfect golden crunch', 12.99, 'momo-veg-fried.jpg', 1, 23, 260, 'mild', 1, 1, 0, 'Mixed vegetables, flour, onion, ginger, garlic, spices', 'Gluten', 4),
(2, 'Chilly Momo (Chicken)', 'Spicy stir-fried chicken momos tossed in our signature chili sauce', 14.99, 'momo-chicken-chilly.jpg', 1, 30, 380, 'spicy', 0, 0, 0, 'Chicken momo, bell peppers, onions, chili sauce, spices', 'Gluten, soy, may contain traces of nuts', 5),
(2, 'Chilly Momo (Vegetable)', 'Spicy stir-fried vegetable momos in aromatic chili sauce', 13.99, 'momo-veg-chilly.jpg', 1, 28, 320, 'spicy', 1, 0, 0, 'Vegetable momo, bell peppers, onions, chili sauce, spices', 'Gluten, soy', 6),

-- Noodles Category (ID: 3)
(3, 'Chicken Chowmein', 'Stir-fried noodles with tender chicken and fresh vegetables', 15.99, 'chowmein-chicken.jpg', 1, 25, 450, 'medium', 0, 0, 0, 'Noodles, chicken, mixed vegetables, soy sauce, spices', 'Gluten, soy, may contain eggs', 1),
(3, 'Vegetable Chowmein', 'Colorful mixed vegetables stir-fried with authentic Himalayan noodles', 13.99, 'chowmein-veg.jpg', 1, 20, 380, 'mild', 1, 1, 0, 'Noodles, mixed vegetables, soy sauce, spices', 'Gluten, soy', 2),
(3, 'Buff Chowmein', 'Traditional buffalo meat noodles with rich, savory flavors', 14.99, 'chowmein-buff.jpg', 1, 30, 480, 'medium', 0, 0, 0, 'Noodles, buffalo meat, vegetables, soy sauce, spices', 'Gluten, soy', 3),

-- Street Food Category (ID: 4)
(4, 'Laphing (Jhol)', 'Cold, spicy noodles in tangy broth - a Tibetan street food favorite', 11.99, 'laphing-jhol.jpg', 1, 15, 280, 'spicy', 1, 1, 0, 'Mung bean noodles, tomato, cucumber, spicy broth', 'May contain traces of gluten', 1),
(4, 'Laphing (Dry)', 'Spicy cold noodles mixed with vegetables and aromatic spices', 10.99, 'laphing-dry.jpg', 1, 12, 250, 'very_spicy', 1, 1, 0, 'Mung bean noodles, tomato, cucumber, dry spices', 'May contain traces of gluten', 2),
(4, 'Aaloo Nimki', 'Crispy potato snack seasoned with traditional Himalayan spices', 8.99, 'aaloo-nimki.jpg', 1, 15, 220, 'mild', 1, 1, 1, 'Potato, spices, oil', 'None', 3),
(4, 'Pani Puri (6 pieces)', 'Crispy hollow shells filled with spiced water and tangy chutneys', 9.99, 'pani-puri.jpg', 1, 10, 180, 'medium', 1, 1, 0, 'Semolina shells, spiced water, chickpeas, chutneys', 'Gluten', 4),
(4, 'Samosa (2 pieces)', 'Golden fried pastries filled with spiced potato and peas', 7.99, 'samosa.jpg', 1, 20, 280, 'mild', 1, 1, 0, 'Flour, potato, peas, spices, oil', 'Gluten', 5),

-- Main Dishes Category (ID: 1)
(1, 'Dal Bhat Set', 'Traditional Nepalese meal with lentil curry, rice, and accompaniments', 18.99, 'dal-bhat.jpg', 1, 35, 520, 'mild', 1, 1, 1, 'Lentils, rice, vegetables, pickle, papad', 'None', 1),
(1, 'Chicken Curry', 'Tender chicken cooked in aromatic Himalayan spices and herbs', 19.99, 'chicken-curry.jpg', 1, 40, 480, 'medium', 0, 0, 1, 'Chicken, onion, tomato, spices, herbs', 'None', 2),
(1, 'Mutton Curry', 'Slow-cooked goat meat in rich, flavorful traditional curry', 22.99, 'mutton-curry.jpg', 1, 50, 580, 'medium', 0, 0, 1, 'Goat meat, onion, tomato, spices, herbs', 'None', 3),

-- Beverages Category (ID: 5)
(5, 'Masala Chai', 'Traditional spiced tea brewed with aromatic herbs and spices', 4.99, 'masala-chai.jpg', 1, 8, 80, 'mild', 1, 0, 1, 'Tea, milk, sugar, cardamom, ginger, cinnamon', 'Dairy', 1),
(5, 'Himalayan Tea', 'Premium high-altitude tea with a unique mountain flavor', 5.99, 'himalayan-tea.jpg', 1, 5, 5, 'mild', 1, 1, 1, 'High-altitude tea leaves', 'None', 2),
(5, 'Lassi (Sweet)', 'Refreshing yogurt-based drink with a touch of sweetness', 6.99, 'lassi-sweet.jpg', 1, 5, 150, 'mild', 1, 0, 1, 'Yogurt, sugar, cardamom', 'Dairy', 3),
(5, 'Mango Lassi', 'Creamy mango-flavored yogurt drink - perfect for summer', 7.99, 'mango-lassi.jpg', 1, 5, 180, 'mild', 1, 0, 1, 'Yogurt, mango, sugar', 'Dairy', 4),
(5, 'Fresh Lime Soda', 'Zesty lime drink with soda water and a hint of salt', 4.99, 'lime-soda.jpg', 1, 3, 60, 'mild', 1, 1, 1, 'Lime, soda water, salt, sugar', 'None', 5);
