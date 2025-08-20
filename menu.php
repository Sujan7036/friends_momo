<?php
/**
 * Menu Page
 * Friends and Momos Restaurant Management System
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/MenuItem.php';
require_once __DIR__ . '/models/Category.php';
require_once __DIR__ . '/includes/functions.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize models
$menuItemModel = new MenuItem();
$categoryModel = new Category();

// Get filters from query parameters
$selectedCategory = $_GET['category'] ?? '';
$searchQuery = $_GET['search'] ?? '';
$dietaryFilter = $_GET['dietary'] ?? '';
$spiceLevel = $_GET['spice'] ?? '';

// Get all categories for filter
$categories = $categoryModel->getActiveCategories();

// Get menu items based on filters
if ($searchQuery || $selectedCategory || $dietaryFilter || $spiceLevel) {
    // Use search method with filters
    $dietaryFilters = [];
    if ($dietaryFilter) {
        $dietaryFilters[$dietaryFilter] = true;
    }
    if ($spiceLevel) {
        $dietaryFilters['spice_level'] = $spiceLevel;
    }
    $menuItems = $menuItemModel->searchItems($searchQuery, $selectedCategory, $dietaryFilters);
} else {
    // Get all available items
    $menuItems = $menuItemModel->getAvailableItems();
}

// Group items by category for better display
$itemsByCategory = [];
foreach ($menuItems as $item) {
    $categoryName = $item['category_name'];
    if (!isset($itemsByCategory[$categoryName])) {
        $itemsByCategory[$categoryName] = [];
    }
    $itemsByCategory[$categoryName][] = $item;
}

// Spice level icons
$spiceIcons = [
    'mild' => '<i class="fas fa-pepper-hot" style="color: #28a745;"></i>',
    'medium' => '<i class="fas fa-pepper-hot" style="color: #ffc107;"></i>',
    'hot' => '<i class="fas fa-pepper-hot" style="color: #fd7e14;"></i>',
    'very_hot' => '<i class="fas fa-pepper-hot" style="color: #dc3545;"></i>'
];
?>
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Friends & Momos</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= ASSETS_URL ?>/css/main.css" rel="stylesheet">
    <link href="<?= ASSETS_URL ?>/css/menu.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-logo">
                <img src="<?= ASSETS_URL ?>/images/logo.png" alt="Friends & Momos">
                Friends & Momos
            </a>
            
            <div class="nav-menu">
                <a href="index.php" class="nav-link">Home</a>
                <a href="menu.php" class="nav-link active">Menu</a>
                <a href="about.php" class="nav-link">About</a>
                <a href="reservation.php" class="nav-link">Reservations</a>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="cart.php" class="nav-link">
                        <i class="fas fa-shopping-cart"></i>
                        Cart <span id="cart-count" class="cart-count">0</span>
                    </a>
                    <div class="nav-dropdown">
                        <a href="#" class="nav-link dropdown-toggle">
                            <i class="fas fa-user"></i>
                            <?= htmlspecialchars((isset($_SESSION['user_name']) ? $_SESSION['user_name'] : ($_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name']))) ?>
                        </a>
                        <div class="dropdown-menu">
                            <a href="dashboard.php">Dashboard</a>
                            <a href="logout.php">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="nav-link">Login</a>
                    <a href="register.php" class="nav-link btn-primary">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="menu-hero">
        <div class="container">
            <h1>Our Menu</h1>
            <p>Delicious Nepalese cuisine made with love</p>
        </div>
    </section>

    <!-- Menu Filters -->
    <section class="menu-filters">
        <div class="container">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <input type="text" name="search" placeholder="Search menu items..." 
                           value="<?= htmlspecialchars($searchQuery) ?>" class="search-input">
                </div>
                
                <div class="filter-group">
                    <select name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" 
                                    <?= $selectedCategory == $category['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <select name="dietary">
                        <option value="">Dietary Preferences</option>
                        <option value="vegetarian" <?= $dietaryFilter === 'vegetarian' ? 'selected' : '' ?>>Vegetarian</option>
                        <option value="vegan" <?= $dietaryFilter === 'vegan' ? 'selected' : '' ?>>Vegan</option>
                        <option value="gluten_free" <?= $dietaryFilter === 'gluten_free' ? 'selected' : '' ?>>Gluten Free</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <select name="spice">
                        <option value="">Spice Level</option>
                        <option value="mild" <?= $spiceLevel === 'mild' ? 'selected' : '' ?>>Mild</option>
                        <option value="medium" <?= $spiceLevel === 'medium' ? 'selected' : '' ?>>Medium</option>
                        <option value="hot" <?= $spiceLevel === 'hot' ? 'selected' : '' ?>>Hot</option>
                        <option value="very_hot" <?= $spiceLevel === 'very_hot' ? 'selected' : '' ?>>Very Hot</option>
                    </select>
                </div>
                
                <button type="submit" class="filter-btn">
                    <i class="fas fa-search"></i>
                    Filter
                </button>
                
                <a href="menu.php" class="clear-filters">Clear Filters</a>
            </form>
        </div>
    </section>

    <!-- Menu Items -->
    <section class="menu-section">
        <div class="container">
            <?php if (empty($menuItems)): ?>
                <div class="no-results">
                    <i class="fas fa-utensils"></i>
                    <h3>No menu items found</h3>
                    <p>Try adjusting your search or filters</p>
                </div>
            <?php else: ?>
                <?php foreach ($itemsByCategory as $categoryName => $items): ?>
                    <div class="menu-category">
                        <h2 class="category-title"><?= htmlspecialchars($categoryName) ?></h2>
                        <div class="menu-grid">
                            <?php foreach ($items as $item): ?>
                                <div class="menu-item" data-item-id="<?= $item['id'] ?>" data-quantity="1">
                                    <div class="menu-item-image-container">
                                        <img src="<?= ASSETS_URL ?>/images/menu/<?= htmlspecialchars($item['image_url'] ?? 'default.jpg') ?>" 
                                             alt="<?= htmlspecialchars($item['name']) ?>"
                                             class="menu-item-image"
                                             onerror="this.src='<?= ASSETS_URL ?>/images/food.png'">
                                        
                                        <?php if ($item['order_count'] > 10): ?>
                                            <div class="popularity-badge">
                                                <i class="fas fa-fire"></i>
                                                Popular
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($item['discount_percentage'] > 0): ?>
                                            <div class="discount-badge">
                                                <?= $item['discount_percentage'] ?>% OFF
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="menu-item-content">
                                        <h3 class="menu-item-name"><?= htmlspecialchars($item['name']) ?></h3>
                                        <p class="menu-item-description"><?= htmlspecialchars($item['description']) ?></p>
                                        
                                        <div class="menu-item-details">
                                            <div class="dietary-info">
                                                <?php if ($item['is_vegetarian']): ?>
                                                    <span class="dietary-badge vegetarian">
                                                        <i class="fas fa-leaf"></i>
                                                        Vegetarian
                                                    </span>
                                                <?php endif; ?>
                                                
                                                <?php if ($item['is_vegan']): ?>
                                                    <span class="dietary-badge vegan">
                                                        <i class="fas fa-seedling"></i>
                                                        Vegan
                                                    </span>
                                                <?php endif; ?>
                                                
                                                <?php if ($item['is_gluten_free']): ?>
                                                    <span class="dietary-badge gluten-free">
                                                        <i class="fas fa-wheat"></i>
                                                        Gluten Free
                                                    </span>
                                                <?php endif; ?>
                                                
                                                <?php if ($item['spice_level']): ?>
                                                    <span class="spice-level <?= $item['spice_level'] ?>">
                                                        <?php 
                                                        echo $spiceIcons[$item['spice_level']] ?? '<i class="fas fa-pepper-hot"></i>';
                                                        ?>
                                                        <?= ucfirst($item['spice_level']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php if ($item['preparation_time']): ?>
                                                <div class="prep-time">
                                                    <i class="fas fa-clock"></i>
                                                    <?= $item['preparation_time'] ?> mins
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="menu-item-footer">
                                            <div class="price-section">
                                                <?php if ($item['discount_percentage'] > 0): ?>
                                                    <span class="original-price"><?= formatCurrency($item['price']) ?></span>
                                                    <span class="menu-item-price"><?= formatCurrency($item['discounted_price']) ?></span>
                                                <?php else: ?>
                                                    <span class="menu-item-price"><?= formatCurrency($item['price']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="quantity-controls">
                                                <button type="button" class="quantity-btn minus" data-action="decrease">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <span class="quantity">1</span>
                                                <button type="button" class="quantity-btn plus" data-action="increase">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                            
                                            <button type="button" class="add-to-cart-btn" data-item-id="<?= $item['id'] ?>">
                                                <i class="fas fa-shopping-cart"></i>
                                                Add to Cart
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Friends & Momos</h3>
                    <p>Authentic Nepalese cuisine in the heart of the city</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="menu.php">Menu</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <p><i class="fas fa-phone"></i> (555) 123-4567</p>
                    <p><i class="fas fa-envelope"></i> info@friendsmomos.com</p>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Restaurant St, City</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Friends & Momos. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="<?= ASSETS_URL ?>/js/menu.js"></script>
</body>
</html>
