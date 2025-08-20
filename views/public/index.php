<?php
/**
 * Home Page
 * Friends and Momos Restaurant Management System
 */

// Page configuration
$pageTitle = "Home - Friends and Momos | Authentic Himalayan Cuisine";
$bodyClass = "home-page";

// Load required files
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/models/MenuItem.php';
require_once dirname(__DIR__, 2) . '/models/Category.php';

// Initialize models
$menuItemModel = new MenuItem();
$categoryModel = new Category();

// Get featured items for homepage
$featuredItems = $menuItemModel->getFeaturedItems(6);
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

// Include header
include_once dirname(__DIR__, 2) . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-background">
        <div class="hero-overlay"></div>
        <img src="<?= ASSETS_URL ?>/images/hero-bg.jpg" alt="Restaurant Interior" class="hero-image">
    </div>
    
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">
                    Authentic Taste of the
                    <span class="highlight">Himalayas</span>
                </h1>
                <p class="hero-description">
                    Experience the rich flavors and traditions of Nepalese cuisine, lovingly prepared 
                    with authentic spices and fresh ingredients. From steamed momos to spicy chowmein, 
                    every dish tells a story of the mountains.
                </p>
                <div class="hero-buttons">
                    <a href="<?= BASE_URL ?>/views/public/menu.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-utensils"></i>
                        Explore Menu
                    </a>
                    <a href="<?= BASE_URL ?>/views/public/reservation.php" class="btn btn-secondary btn-lg">
                        <i class="fas fa-calendar-alt"></i>
                        Book Table
                    </a>
                </div>
            </div>
            
            <div class="hero-image-section">
                <div class="hero-food-image">
                    <img src="<?= ASSETS_URL ?>/images/food.png" alt="Delicious Himalayan Food" class="floating-image">
                    <div class="food-badge">
                        <i class="fas fa-star"></i>
                        <span>4.8/5 Rating</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-leaf"></i>
                </div>
                <h3>Fresh Ingredients</h3>
                <p>We source the finest, freshest ingredients daily to ensure authentic flavors in every dish.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h3>Made with Love</h3>
                <p>Every meal is prepared with traditional recipes passed down through generations.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <h3>Fast Delivery</h3>
                <p>Quick delivery service to bring authentic Himalayan flavors straight to your door.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Family Friendly</h3>
                <p>A warm, welcoming atmosphere perfect for families and friends to gather and enjoy.</p>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="categories-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Our Menu Categories</h2>
            <p class="section-description">
                Explore our diverse range of authentic Himalayan dishes, each category 
                offering unique flavors and traditional preparations.
            </p>
        </div>
        
        <div class="categories-grid">
            <?php foreach ($categories as $category): ?>
                <div class="category-card">
                    <div class="category-image">
                        <img src="<?= ASSETS_URL ?>/images/food.png" 
                             alt="<?= htmlspecialchars($category['name']) ?>"
                             onerror="this.src='<?= ASSETS_URL ?>/images/food.png'">
                        <div class="category-overlay">
                            <a href="<?= BASE_URL ?>/views/public/menu.php?category=<?= $category['id'] ?>" 
                               class="btn btn-primary">
                                View Items
                            </a>
                        </div>
                    </div>
                    <div class="category-content">
                        <h3 class="category-name"><?= htmlspecialchars($category['name']) ?></h3>
                        <p class="category-description">
                            <?= htmlspecialchars($category['description'] ?? 'Delicious ' . $category['name'] . ' dishes') ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Items Section -->
<section class="featured-items-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Featured Dishes</h2>
            <p class="section-description">
                Try our most popular dishes that keep customers coming back for more.
            </p>
        </div>
        
        <div class="menu-grid">
            <?php foreach ($featuredItems as $item): ?>
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
                        
                        <?php if ($item['order_count'] > 0): ?>
                            <div class="popularity-badge">
                                <i class="fas fa-fire"></i>
                                Popular
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="menu-item-content">
                        <h3 class="menu-item-title"><?= htmlspecialchars($item['name']) ?></h3>
                        <p class="menu-item-description">
                            <?= htmlspecialchars($item['description']) ?>
                        </p>
                        
                        <div class="menu-item-meta">
                            <span class="menu-badge category"><?= htmlspecialchars($item['category_name']) ?></span>
                            
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
                            
                            <?php if ($item['spice_level'] !== 'mild'): ?>
                                <span class="menu-badge spicy">
                                    <i class="fas fa-pepper-hot"></i> <?= ucfirst($item['spice_level']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="menu-item-footer">
                            <div class="price-section">
                                <span class="menu-item-price"><?= formatCurrency($item['price']) ?></span>
                                <?php if ($item['preparation_time']): ?>
                                    <span class="prep-time">
                                        <i class="fas fa-clock"></i>
                                        <?= $item['preparation_time'] ?> min
                                    </span>
                                <?php endif; ?>
                            </div>
                            
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
            <?php endforeach; ?>
        </div>
        
        <div class="section-footer">
            <a href="<?= BASE_URL ?>/views/public/menu.php" class="btn btn-secondary btn-lg">
                <i class="fas fa-utensils"></i>
                View Full Menu
            </a>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="about-section">
    <div class="container">
        <div class="about-content">
            <div class="about-text">
                <h2 class="section-title">Our Story</h2>
                <p class="about-description">
                    Friends and Momos began as a dream to share the authentic flavors of the Himalayas 
                    with the Gungahlin community. Our founders, passionate about Nepalese cuisine, 
                    brought traditional recipes and cooking techniques to create an authentic dining experience.
                </p>
                <p class="about-description">
                    Every dish is prepared with love, using traditional spices imported directly from Nepal 
                    and fresh local ingredients. We believe food brings people together, and our restaurant 
                    is a place where friends become family over shared meals.
                </p>
                
                <div class="about-features">
                    <div class="about-feature">
                        <i class="fas fa-award"></i>
                        <span>Authentic Recipes</span>
                    </div>
                    <div class="about-feature">
                        <i class="fas fa-globe-asia"></i>
                        <span>Traditional Spices</span>
                    </div>
                    <div class="about-feature">
                        <i class="fas fa-handshake"></i>
                        <span>Family Values</span>
                    </div>
                </div>
                
                <a href="<?= BASE_URL ?>/views/public/about.php" class="btn btn-primary">
                    <i class="fas fa-info-circle"></i>
                    Learn More About Us
                </a>
            </div>
            
            <div class="about-image">
                <img src="<?= ASSETS_URL ?>/images/resturant.png" alt="Restaurant Interior" class="restaurant-image">
                <div class="about-stats">
                    <div class="stat">
                        <span class="stat-number">5+</span>
                        <span class="stat-label">Years Serving</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">1000+</span>
                        <span class="stat-label">Happy Customers</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">20+</span>
                        <span class="stat-label">Signature Dishes</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2 class="cta-title">Ready to Experience Authentic Flavors?</h2>
            <p class="cta-description">
                Join us for an unforgettable dining experience or order online for delivery to your door.
            </p>
            <div class="cta-buttons">
                <a href="<?= BASE_URL ?>/views/public/reservation.php" class="btn btn-primary btn-xl">
                    <i class="fas fa-calendar-check"></i>
                    Make a Reservation
                </a>
                <a href="<?= BASE_URL ?>/views/public/menu.php" class="btn btn-secondary btn-xl">
                    <i class="fas fa-shopping-cart"></i>
                    Order Online
                </a>
            </div>
        </div>
    </div>
</section>

<style>
/* Home Page Specific Styles */
.hero-section {
    position: relative;
    min-height: 90vh;
    display: flex;
    align-items: center;
    overflow: hidden;
}

.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -1;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(139, 125, 107, 0.9) 0%, rgba(74, 85, 104, 0.8) 100%);
    z-index: 1;
}

.hero-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.hero-content {
    position: relative;
    z-index: 2;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-16);
    align-items: center;
    min-height: 90vh;
    padding: var(--space-8) 0;
}

.hero-title {
    font-size: var(--text-5xl);
    font-weight: 700;
    color: var(--white);
    margin-bottom: var(--space-6);
    line-height: 1.2;
}

.highlight {
    color: var(--secondary-color);
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

.hero-description {
    font-size: var(--text-lg);
    color: var(--gray-100);
    margin-bottom: var(--space-8);
    line-height: 1.6;
}

.hero-buttons {
    display: flex;
    gap: var(--space-4);
    flex-wrap: wrap;
}

.hero-image-section {
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
}

.hero-food-image {
    position: relative;
    max-width: 400px;
}

.floating-image {
    width: 100%;
    height: auto;
    animation: float 6s ease-in-out infinite;
    filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.3));
}

.food-badge {
    position: absolute;
    top: var(--space-4);
    right: var(--space-4);
    background-color: var(--warning-color);
    color: var(--white);
    padding: var(--space-2) var(--space-4);
    border-radius: var(--radius-full);
    font-weight: 600;
    font-size: var(--text-sm);
    display: flex;
    align-items: center;
    gap: var(--space-2);
    box-shadow: var(--shadow-lg);
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

.features-section {
    padding: var(--space-20) 0;
    background-color: var(--gray-50);
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--space-8);
}

.feature-card {
    background-color: var(--white);
    padding: var(--space-8);
    border-radius: var(--radius-2xl);
    box-shadow: var(--shadow);
    text-align: center;
    transition: var(--transition);
}

.feature-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-xl);
}

.feature-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto var(--space-6);
    color: var(--white);
    font-size: var(--text-2xl);
}

.categories-section {
    padding: var(--space-20) 0;
}

.section-header {
    text-align: center;
    margin-bottom: var(--space-16);
}

.section-title {
    font-size: var(--text-4xl);
    color: var(--gray-900);
    margin-bottom: var(--space-4);
}

.section-description {
    font-size: var(--text-lg);
    color: var(--gray-600);
    max-width: 600px;
    margin: 0 auto;
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: var(--space-8);
}

.category-card {
    background-color: var(--white);
    border-radius: var(--radius-2xl);
    overflow: hidden;
    box-shadow: var(--shadow);
    transition: var(--transition);
}

.category-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-xl);
}

.category-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.category-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition);
}

.category-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: var(--transition);
}

.category-card:hover .category-overlay {
    opacity: 1;
}

.category-card:hover .category-image img {
    transform: scale(1.1);
}

.category-content {
    padding: var(--space-6);
}

.category-name {
    font-size: var(--text-xl);
    font-weight: 600;
    margin-bottom: var(--space-3);
    color: var(--gray-900);
}

.category-description {
    color: var(--gray-600);
}

.featured-items-section {
    padding: var(--space-20) 0;
    background-color: var(--gray-50);
}

.popularity-badge {
    position: absolute;
    top: var(--space-3);
    left: var(--space-3);
    background: linear-gradient(135deg, var(--warning-color), var(--warning-light));
    color: var(--white);
    padding: var(--space-1) var(--space-3);
    border-radius: var(--radius-full);
    font-size: var(--text-xs);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: var(--space-1);
}

.prep-time {
    color: var(--gray-500);
    font-size: var(--text-sm);
    display: flex;
    align-items: center;
    gap: var(--space-1);
}

.about-section {
    padding: var(--space-20) 0;
}

.about-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-16);
    align-items: center;
}

.about-description {
    font-size: var(--text-lg);
    line-height: 1.7;
    margin-bottom: var(--space-6);
    color: var(--gray-700);
}

.about-features {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-6);
    margin: var(--space-8) 0;
}

.about-feature {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    color: var(--primary-color);
    font-weight: 500;
}

.about-image {
    position: relative;
}

.restaurant-image {
    width: 100%;
    height: auto;
    border-radius: var(--radius-2xl);
    box-shadow: var(--shadow-xl);
}

.about-stats {
    position: absolute;
    bottom: var(--space-6);
    left: var(--space-6);
    right: var(--space-6);
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: var(--radius-xl);
    padding: var(--space-4);
    display: flex;
    justify-content: space-around;
    gap: var(--space-4);
}

.stat {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: var(--text-2xl);
    font-weight: 700;
    color: var(--primary-color);
}

.stat-label {
    font-size: var(--text-sm);
    color: var(--gray-600);
}

.cta-section {
    padding: var(--space-20) 0;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
    color: var(--white);
    text-align: center;
}

.cta-title {
    font-size: var(--text-4xl);
    font-weight: 700;
    margin-bottom: var(--space-6);
    color: var(--white);
}

.cta-description {
    font-size: var(--text-xl);
    margin-bottom: var(--space-8);
    color: var(--gray-100);
}

.cta-buttons {
    display: flex;
    gap: var(--space-4);
    justify-content: center;
    flex-wrap: wrap;
}

.section-footer {
    text-align: center;
    margin-top: var(--space-12);
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-content {
        grid-template-columns: 1fr;
        gap: var(--space-8);
        text-align: center;
    }
    
    .hero-title {
        font-size: var(--text-3xl);
    }
    
    .about-content {
        grid-template-columns: 1fr;
        gap: var(--space-8);
    }
    
    .about-stats {
        position: static;
        margin-top: var(--space-6);
    }
    
    .cta-title {
        font-size: var(--text-3xl);
    }
    
    .cta-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .features-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--space-6);
    }
}
</style>

<?php include_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
