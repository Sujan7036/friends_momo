<?php
/**
 * Menu Page
 * Friends and Momos Restaurant Management System
 */

// Page configuration
$pageTitle = "Menu - Friends and Momos | Authentic Himalayan Cuisine";
$bodyClass = "menu-page";

// Load required files
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/models/MenuItem.php';
require_once dirname(__DIR__, 2) . '/models/Category.php';
require_once dirname(__DIR__, 2) . '/helpers/image_helper.php';

// Initialize models
$menuItemModel = new MenuItem();
$categoryModel = new Category();

// Get query parameters
$selectedCategory = $_GET['category'] ?? null;
$searchQuery = $_GET['search'] ?? '';
$dietaryFilter = $_GET['dietary'] ?? '';
$spiceLevel = $_GET['spice'] ?? '';

// Get categories for filter
$categories = $categoryModel->getActiveCategories();

// Helper function to map item names to available images
function getItemImage($itemName) {
    $imageMappings = [
        'momo' => 'momo.png',
        'momos' => 'momo.png',
        'chicken momo' => 'momo.png',
        'pork momo' => 'momo.png',
        'veg momo' => 'momo.png',
        'chowmein' => 'chowmin.png',
        'chow mein' => 'chowmin.png',
        'chicken chowmein' => 'chowmin.png',
        'pork chowmein' => 'chowmin.png',
        'laphing' => 'laphing.png',
        'pani puri' => 'panipuri.png',
        'panipuri' => 'panipuri.png',
        'aloo nimkin' => 'allonimkin.png',
        'aloonimkin' => 'allonimkin.png',
    ];
    
    $itemNameLower = strtolower($itemName);
    
    // Check for exact matches first
    if (isset($imageMappings[$itemNameLower])) {
        return $imageMappings[$itemNameLower];
    }
    
    // Check for partial matches
    foreach ($imageMappings as $key => $image) {
        if (strpos($itemNameLower, $key) !== false) {
            return $image;
        }
    }
    
    return 'food.png'; // Default fallback image
}

// Prepare filters for menu items
$filters = [];
if ($selectedCategory) {
    $filters['category_id'] = $selectedCategory;
}
if ($searchQuery) {
    $filters['search'] = $searchQuery;
}
if ($dietaryFilter) {
    $filters['dietary'] = $dietaryFilter;
}
if ($spiceLevel) {
    $filters['spice_level'] = $spiceLevel;
}

// Get menu items
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

// Include header
include_once dirname(__DIR__, 2) . '/includes/header.php';
?>

<!-- Menu Hero Section -->
<section class="menu-hero">
    <div class="container">
        <div class="menu-hero-content">
            <h1 class="page-title">Our Menu</h1>
            <p class="page-description">
                Discover the authentic flavors of the Himalayas with our carefully crafted dishes, 
                made from traditional recipes and the finest ingredients.
            </p>
        </div>
    </div>
</section>

<!-- Menu Filters -->
<section class="menu-filters">
    <div class="container">
        <div class="filter-container">
            <!-- Search and Quick Filters -->
            <div class="filter-row">
                <div class="search-section">
                    <div class="search-input-container">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" 
                               id="menu-search" 
                               placeholder="Search for dishes..." 
                               value="<?= htmlspecialchars($searchQuery) ?>"
                               class="search-input">
                        <button type="button" id="clear-search" class="clear-search-btn" style="display: <?= $searchQuery ? 'block' : 'none' ?>">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                
                <div class="quick-filters">
                    <button type="button" class="filter-btn <?= $selectedCategory === null ? 'active' : '' ?>" data-category="">
                        All Items
                    </button>
                    <?php foreach ($categories as $category): ?>
                        <button type="button" 
                                class="filter-btn <?= $selectedCategory == $category['id'] ? 'active' : '' ?>" 
                                data-category="<?= $category['id'] ?>">
                            <?= htmlspecialchars($category['name']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Advanced Filters -->
            <div class="advanced-filters">
                <div class="filter-group">
                    <label for="dietary-filter" class="filter-label">
                        <i class="fas fa-leaf"></i>
                        Dietary Preferences
                    </label>
                    <select id="dietary-filter" class="filter-select">
                        <option value="">All Options</option>
                        <option value="vegetarian" <?= $dietaryFilter === 'vegetarian' ? 'selected' : '' ?>>Vegetarian</option>
                        <option value="vegan" <?= $dietaryFilter === 'vegan' ? 'selected' : '' ?>>Vegan</option>
                        <option value="gluten-free" <?= $dietaryFilter === 'gluten-free' ? 'selected' : '' ?>>Gluten-Free</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="spice-filter" class="filter-label">
                        <i class="fas fa-pepper-hot"></i>
                        Spice Level
                    </label>
                    <select id="spice-filter" class="filter-select">
                        <option value="">All Levels</option>
                        <option value="mild" <?= $spiceLevel === 'mild' ? 'selected' : '' ?>>Mild</option>
                        <option value="medium" <?= $spiceLevel === 'medium' ? 'selected' : '' ?>>Medium</option>
                        <option value="hot" <?= $spiceLevel === 'hot' ? 'selected' : '' ?>>Hot</option>
                        <option value="extra-hot" <?= $spiceLevel === 'extra-hot' ? 'selected' : '' ?>>Extra Hot</option>
                    </select>
                </div>
                
                <button type="button" id="clear-filters" class="btn btn-secondary">
                    <i class="fas fa-undo"></i>
                    Clear Filters
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Menu Items -->
<section class="menu-content">
    <div class="container">
        <?php if (empty($menuItems)): ?>
            <!-- No Results -->
            <div class="no-results">
                <div class="no-results-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>No dishes found</h3>
                <p>We couldn't find any dishes matching your search criteria. Please try different filters or search terms.</p>
                <button type="button" id="reset-search" class="btn btn-primary">
                    <i class="fas fa-undo"></i>
                    Reset Search
                </button>
            </div>
        <?php else: ?>
            <!-- Results Summary -->
            <div class="results-summary">
                <h2>Found <?= count($menuItems) ?> dishes</h2>
                <?php if ($searchQuery || $selectedCategory || $dietaryFilter || $spiceLevel): ?>
                    <div class="active-filters">
                        <span>Active filters:</span>
                        <?php if ($searchQuery): ?>
                            <span class="filter-tag">
                                Search: "<?= htmlspecialchars($searchQuery) ?>"
                                <button type="button" class="remove-filter" data-filter="search">×</button>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($selectedCategory): ?>
                            <?php 
                            $categoryName = '';
                            foreach ($categories as $cat) {
                                if ($cat['id'] == $selectedCategory) {
                                    $categoryName = $cat['name'];
                                    break;
                                }
                            }
                            ?>
                            <span class="filter-tag">
                                Category: <?= htmlspecialchars($categoryName) ?>
                                <button type="button" class="remove-filter" data-filter="category">×</button>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($dietaryFilter): ?>
                            <span class="filter-tag">
                                Dietary: <?= ucfirst(htmlspecialchars($dietaryFilter)) ?>
                                <button type="button" class="remove-filter" data-filter="dietary">×</button>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($spiceLevel): ?>
                            <span class="filter-tag">
                                Spice: <?= ucfirst(htmlspecialchars($spiceLevel)) ?>
                                <button type="button" class="remove-filter" data-filter="spice">×</button>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Menu Items by Category -->
            <?php foreach ($itemsByCategory as $categoryName => $items): ?>
                <div class="menu-category-section">
                    <h3 class="category-title">
                        <i class="fas fa-utensils"></i>
                        <?= htmlspecialchars($categoryName) ?>
                        <span class="item-count">(<?= count($items) ?> items)</span>
                    </h3>
                    
                    <div class="menu-grid">
                        <?php foreach ($items as $item): ?>
                            <div class="menu-item" data-item-id="<?= $item['id'] ?>" data-quantity="1">
                                <div class="menu-item-image-container">
                                    <?php if (!empty($item['image_url'])): ?>
                                        <img src="<?= ASSETS_URL ?>/<?= htmlspecialchars($item['image_url']) ?>" 
                                             alt="<?= htmlspecialchars($item['name']) ?>"
                                             class="menu-item-image"
                                             onerror="this.src='<?= ASSETS_URL ?>/images/<?= getItemImage($item['name']) ?>'">
                                    <?php else: ?>
                                        <img src="<?= ASSETS_URL ?>/images/<?= getItemImage($item['name']) ?>" 
                                             alt="<?= htmlspecialchars($item['name']) ?>"
                                             class="menu-item-image"
                                             onerror="this.src='<?= ASSETS_URL ?>/images/food.png'">
                                    <?php endif; ?>
                                    
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
                                    
                                    <button type="button" class="quick-view-btn" data-item-id="<?= $item['id'] ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                
                                <div class="menu-item-content">
                                    <h4 class="menu-item-title"><?= htmlspecialchars($item['name']) ?></h4>
                                    <p class="menu-item-description">
                                        <?= htmlspecialchars($item['description']) ?>
                                    </p>
                                    
                                    <div class="menu-item-meta">
                                        <div class="badges">
                                            <?php if ($item['is_vegetarian']): ?>
                                                <span class="menu-badge vegetarian">
                                                    <i class="fas fa-leaf"></i> Vegetarian
                                                </span>
                                            <?php endif; ?>
                                            
                                            <?php if ($item['is_vegan']): ?>
                                                <span class="menu-badge vegan">
                                                    <i class="fas fa-seedling"></i> Vegan
                                                </span>
                                            <?php endif; ?>
                                            
                                            <?php if ($item['is_gluten_free']): ?>
                                                <span class="menu-badge gluten-free">
                                                    <i class="fas fa-wheat"></i> Gluten-Free
                                                </span>
                                            <?php endif; ?>
                                            
                                            <?php if ($item['spice_level'] !== 'mild'): ?>
                                                <span class="menu-badge spicy spice-<?= $item['spice_level'] ?>">
                                                    <?php
                                                    $spiceIcons = [
                                                        'mild' => '<i class="fas fa-pepper-hot"></i>',
                                                        'medium' => '<i class="fas fa-pepper-hot"></i><i class="fas fa-pepper-hot"></i>',
                                                        'hot' => '<i class="fas fa-pepper-hot"></i><i class="fas fa-pepper-hot"></i><i class="fas fa-pepper-hot"></i>',
                                                        'extra-hot' => '<i class="fas fa-fire"></i><i class="fas fa-fire"></i>'
                                                    ];
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
                                        
                                        <div class="item-actions">
                                            <div class="quantity-controls">
                                                <button type="button" class="quantity-btn" data-action="decrease">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <span class="quantity-display">1</span>
                                                <button type="button" class="quantity-btn" data-action="increase">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                            
                                            <button type="button" class="btn btn-primary add-to-cart-btn">
                                                <i class="fas fa-cart-plus"></i>
                                                Add to Cart
                                            </button>
                                        </div>
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

<!-- Quick View Modal -->
<div id="quick-view-modal" class="modal">
    <div class="modal-overlay"></div>
    <div class="modal-content">
        <button type="button" class="modal-close">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="modal-body">
            <div class="quick-view-content">
                <div class="quick-view-image">
                    <img id="quick-view-img" src="" alt="" class="modal-image">
                </div>
                
                <div class="quick-view-details">
                    <h3 id="quick-view-title" class="modal-title"></h3>
                    <p id="quick-view-description" class="modal-description"></p>
                    
                    <div id="quick-view-badges" class="modal-badges"></div>
                    
                    <div class="modal-pricing">
                        <span id="quick-view-price" class="modal-price"></span>
                        <span id="quick-view-prep-time" class="modal-prep-time"></span>
                    </div>
                    
                    <div class="modal-actions">
                        <div class="quantity-controls">
                            <button type="button" class="quantity-btn" data-action="decrease">
                                <i class="fas fa-minus"></i>
                            </button>
                            <span class="quantity-display">1</span>
                            <button type="button" class="quantity-btn" data-action="increase">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        
                        <button type="button" class="btn btn-primary add-to-cart-btn">
                            <i class="fas fa-cart-plus"></i>
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Menu Page Specific Styles */
.menu-hero {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
    color: var(--white);
    padding: var(--space-16) 0 var(--space-12);
    text-align: center;
}

.menu-hero-content {
    max-width: 600px;
    margin: 0 auto;
}

.page-title {
    font-size: var(--text-4xl);
    font-weight: 700;
    margin-bottom: var(--space-4);
    color: var(--white);
}

.page-description {
    font-size: var(--text-lg);
    color: var(--gray-100);
    line-height: 1.6;
}

.menu-filters {
    padding: var(--space-8) 0;
    background-color: var(--white);
    border-bottom: 1px solid var(--gray-200);
    position: sticky;
    top: var(--header-height);
    z-index: 100;
}

.filter-container {
    display: flex;
    flex-direction: column;
    gap: var(--space-6);
}

.filter-row {
    display: flex;
    gap: var(--space-6);
    align-items: center;
    flex-wrap: wrap;
}

.search-section {
    flex: 1;
    min-width: 300px;
}

.search-input-container {
    position: relative;
    max-width: 400px;
}

.search-icon {
    position: absolute;
    left: var(--space-4);
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray-500);
}

.search-input {
    width: 100%;
    padding: var(--space-3) var(--space-4) var(--space-3) var(--space-12);
    border: 2px solid var(--gray-300);
    border-radius: var(--radius-lg);
    font-size: var(--text-base);
    transition: var(--transition);
}

.search-input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
}

.clear-search-btn {
    position: absolute;
    right: var(--space-3);
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--gray-500);
    cursor: pointer;
    padding: var(--space-1);
    border-radius: var(--radius);
}

.clear-search-btn:hover {
    color: var(--error-color);
    background-color: var(--error-light);
}

.quick-filters {
    display: flex;
    gap: var(--space-2);
    flex-wrap: wrap;
}

.filter-btn {
    padding: var(--space-2) var(--space-4);
    border: 2px solid var(--gray-300);
    background-color: var(--white);
    color: var(--gray-700);
    border-radius: var(--radius-full);
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
    white-space: nowrap;
}

.filter-btn:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
}

.filter-btn.active {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
    color: var(--white);
}

.advanced-filters {
    display: flex;
    gap: var(--space-4);
    align-items: center;
    flex-wrap: wrap;
    padding-top: var(--space-4);
    border-top: 1px solid var(--gray-200);
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.filter-label {
    font-size: var(--text-sm);
    font-weight: 600;
    color: var(--gray-700);
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.filter-select {
    padding: var(--space-2) var(--space-4);
    border: 2px solid var(--gray-300);
    border-radius: var(--radius);
    background-color: var(--white);
    min-width: 150px;
    cursor: pointer;
}

.filter-select:focus {
    outline: none;
    border-color: var(--primary-color);
}

.menu-content {
    padding: var(--space-12) 0;
}

.no-results {
    text-align: center;
    padding: var(--space-20) var(--space-8);
    color: var(--gray-600);
}

.no-results-icon {
    font-size: var(--text-6xl);
    color: var(--gray-400);
    margin-bottom: var(--space-6);
}

.results-summary {
    margin-bottom: var(--space-8);
}

.results-summary h2 {
    font-size: var(--text-2xl);
    color: var(--gray-900);
    margin-bottom: var(--space-4);
}

.active-filters {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    flex-wrap: wrap;
    color: var(--gray-600);
}

.filter-tag {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    background-color: var(--primary-light);
    color: var(--primary-dark);
    padding: var(--space-1) var(--space-3);
    border-radius: var(--radius-full);
    font-size: var(--text-sm);
    font-weight: 500;
}

.remove-filter {
    background: none;
    border: none;
    color: var(--primary-dark);
    cursor: pointer;
    margin-left: var(--space-1);
    font-weight: 700;
}

.remove-filter:hover {
    color: var(--error-color);
}

.menu-category-section {
    margin-bottom: var(--space-12);
}

.category-title {
    font-size: var(--text-2xl);
    color: var(--gray-900);
    margin-bottom: var(--space-6);
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding-bottom: var(--space-3);
    border-bottom: 2px solid var(--primary-color);
}

.item-count {
    color: var(--gray-500);
    font-size: var(--text-lg);
    font-weight: 400;
}

.quick-view-btn {
    position: absolute;
    top: var(--space-3);
    right: var(--space-3);
    width: 40px;
    height: 40px;
    border-radius: var(--radius-full);
    background-color: rgba(255, 255, 255, 0.9);
    border: none;
    color: var(--gray-700);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transform: scale(0.8);
    transition: var(--transition);
}

.menu-item:hover .quick-view-btn {
    opacity: 1;
    transform: scale(1);
}

.quick-view-btn:hover {
    background-color: var(--primary-color);
    color: var(--white);
}

.discount-badge {
    position: absolute;
    top: var(--space-3);
    left: var(--space-3);
    background: linear-gradient(135deg, var(--success-color), var(--success-light));
    color: var(--white);
    padding: var(--space-1) var(--space-3);
    border-radius: var(--radius-full);
    font-size: var(--text-xs);
    font-weight: 700;
}

.original-price {
    text-decoration: line-through;
    color: var(--gray-500);
    font-size: var(--text-sm);
    margin-right: var(--space-2);
}

.badges {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-2);
    margin-bottom: var(--space-3);
}

.menu-badge.spice-mild { background-color: var(--warning-light); color: var(--warning-dark); }
.menu-badge.spice-medium { background-color: var(--warning-color); color: var(--white); }
.menu-badge.spice-hot { background-color: var(--error-color); color: var(--white); }
.menu-badge.spice-extra-hot { background-color: var(--error-dark); color: var(--white); }

.item-actions {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

/* Quick View Modal */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
    display: none;
}

.modal.active {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(5px);
}

.modal-content {
    position: relative;
    background-color: var(--white);
    border-radius: var(--radius-2xl);
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: var(--shadow-2xl);
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: scale(0.9) translateY(-20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.modal-close {
    position: absolute;
    top: var(--space-4);
    right: var(--space-4);
    width: 40px;
    height: 40px;
    border-radius: var(--radius-full);
    background-color: rgba(255, 255, 255, 0.9);
    border: none;
    color: var(--gray-700);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    transition: var(--transition);
}

.modal-close:hover {
    background-color: var(--error-color);
    color: var(--white);
}

.modal-body {
    padding: var(--space-6);
}

.quick-view-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-6);
    align-items: start;
}

.quick-view-image {
    position: relative;
}

.modal-image {
    width: 100%;
    height: 300px;
    object-fit: cover;
    border-radius: var(--radius-xl);
}

.modal-title {
    font-size: var(--text-2xl);
    font-weight: 700;
    color: var(--gray-900);
    margin-bottom: var(--space-4);
}

.modal-description {
    color: var(--gray-600);
    line-height: 1.6;
    margin-bottom: var(--space-4);
}

.modal-badges {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-2);
    margin-bottom: var(--space-6);
}

.modal-pricing {
    display: flex;
    align-items: center;
    gap: var(--space-4);
    margin-bottom: var(--space-6);
}

.modal-price {
    font-size: var(--text-2xl);
    font-weight: 700;
    color: var(--primary-color);
}

.modal-prep-time {
    color: var(--gray-500);
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.modal-actions {
    display: flex;
    align-items: center;
    gap: var(--space-4);
}

/* Responsive Design */
@media (max-width: 768px) {
    .filter-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-section {
        min-width: auto;
    }
    
    .quick-filters {
        justify-content: center;
    }
    
    .advanced-filters {
        flex-direction: column;
        align-items: stretch;
        gap: var(--space-3);
    }
    
    .filter-group {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
    
    .quick-view-content {
        grid-template-columns: 1fr;
        gap: var(--space-4);
    }
    
    .modal-content {
        width: 95%;
        margin: var(--space-4);
    }
    
    .modal-actions {
        flex-direction: column;
        align-items: stretch;
        gap: var(--space-3);
    }
    
    .item-actions {
        flex-direction: column;
        align-items: stretch;
        gap: var(--space-3);
    }
    
    .category-title {
        font-size: var(--text-xl);
        flex-direction: column;
        align-items: flex-start;
        gap: var(--space-2);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Menu filtering functionality
    const searchInput = document.getElementById('menu-search');
    const clearSearchBtn = document.getElementById('clear-search');
    const filterBtns = document.querySelectorAll('.filter-btn');
    const dietaryFilter = document.getElementById('dietary-filter');
    const spiceFilter = document.getElementById('spice-filter');
    const clearFiltersBtn = document.getElementById('clear-filters');
    const resetSearchBtn = document.getElementById('reset-search');
    const quickViewBtns = document.querySelectorAll('.quick-view-btn');
    const quickViewModal = document.getElementById('quick-view-modal');
    const modalClose = document.querySelector('.modal-close');
    const modalOverlay = document.querySelector('.modal-overlay');
    
    // Search functionality
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            updateFilters();
        }, 500);
        
        clearSearchBtn.style.display = this.value ? 'block' : 'none';
    });
    
    clearSearchBtn.addEventListener('click', function() {
        searchInput.value = '';
        clearSearchBtn.style.display = 'none';
        updateFilters();
    });
    
    // Category filters
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            updateFilters();
        });
    });
    
    // Advanced filters
    dietaryFilter.addEventListener('change', updateFilters);
    spiceFilter.addEventListener('change', updateFilters);
    
    // Clear all filters
    clearFiltersBtn.addEventListener('click', function() {
        searchInput.value = '';
        clearSearchBtn.style.display = 'none';
        filterBtns.forEach(btn => btn.classList.remove('active'));
        filterBtns[0].classList.add('active'); // Activate "All Items"
        dietaryFilter.value = '';
        spiceFilter.value = '';
        updateFilters();
    });
    
    // Reset search
    if (resetSearchBtn) {
        resetSearchBtn.addEventListener('click', function() {
            window.location.href = window.location.pathname;
        });
    }
    
    // Remove individual filters
    document.querySelectorAll('.remove-filter').forEach(btn => {
        btn.addEventListener('click', function() {
            const filterType = this.dataset.filter;
            
            switch(filterType) {
                case 'search':
                    searchInput.value = '';
                    clearSearchBtn.style.display = 'none';
                    break;
                case 'category':
                    filterBtns.forEach(b => b.classList.remove('active'));
                    filterBtns[0].classList.add('active');
                    break;
                case 'dietary':
                    dietaryFilter.value = '';
                    break;
                case 'spice':
                    spiceFilter.value = '';
                    break;
            }
            
            updateFilters();
        });
    });
    
    // Quick view functionality
    quickViewBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const itemId = this.dataset.itemId;
            showQuickView(itemId);
        });
    });
    
    // Modal close events
    modalClose.addEventListener('click', closeQuickView);
    modalOverlay.addEventListener('click', closeQuickView);
    
    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && quickViewModal.classList.contains('active')) {
            closeQuickView();
        }
    });
    
    function updateFilters() {
        const params = new URLSearchParams();
        
        const searchValue = searchInput.value.trim();
        const activeCategory = document.querySelector('.filter-btn.active')?.dataset.category;
        const dietaryValue = dietaryFilter.value;
        const spiceValue = spiceFilter.value;
        
        if (searchValue) params.set('search', searchValue);
        if (activeCategory) params.set('category', activeCategory);
        if (dietaryValue) params.set('dietary', dietaryValue);
        if (spiceValue) params.set('spice', spiceValue);
        
        const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        window.history.pushState({}, '', newUrl);
        
        // Reload page with new filters
        window.location.reload();
    }
    
    function showQuickView(itemId) {
        // Get item data (in a real app, this would be an AJAX call)
        const menuItem = document.querySelector(`[data-item-id="${itemId}"]`);
        if (!menuItem) return;
        
        const img = menuItem.querySelector('.menu-item-image');
        const title = menuItem.querySelector('.menu-item-title');
        const description = menuItem.querySelector('.menu-item-description');
        const price = menuItem.querySelector('.menu-item-price');
        const badges = menuItem.querySelector('.badges');
        const prepTime = menuItem.querySelector('.prep-time');
        
        // Update modal content
        document.getElementById('quick-view-img').src = img.src;
        document.getElementById('quick-view-img').alt = img.alt;
        document.getElementById('quick-view-title').textContent = title.textContent;
        document.getElementById('quick-view-description').textContent = description.textContent;
        document.getElementById('quick-view-price').textContent = price.textContent;
        
        // Update badges
        const modalBadges = document.getElementById('quick-view-badges');
        modalBadges.innerHTML = badges ? badges.innerHTML : '';
        
        // Update prep time
        const modalPrepTime = document.getElementById('quick-view-prep-time');
        modalPrepTime.innerHTML = prepTime ? prepTime.innerHTML : '';
        
        // Set up quantity controls and add to cart for modal
        const modalQuantityControls = quickViewModal.querySelector('.quantity-controls');
        const modalAddToCartBtn = quickViewModal.querySelector('.add-to-cart-btn');
        
        modalQuantityControls.dataset.itemId = itemId;
        modalAddToCartBtn.dataset.itemId = itemId;
        
        // Show modal
        quickViewModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    function closeQuickView() {
        quickViewModal.classList.remove('active');
        document.body.style.overflow = '';
    }
});
</script>

<?php include_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
