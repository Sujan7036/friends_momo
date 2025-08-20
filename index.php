<?php
/**
 * Main Homepage
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

// Get featured items for homepage
$featuredItems = $menuItemModel->getFeaturedItems(6);
$categories = $categoryModel->getActiveCategories();
?>
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friends & Momos - Authentic Nepalese Cuisine</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= ASSETS_URL ?>/css/main.css" rel="stylesheet">
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
                <a href="index.php" class="nav-link active">Home</a>
                <a href="menu.php" class="nav-link">Menu</a>
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
    <section class="hero">
        <div class="hero-content">
            <h1>Welcome to Friends & Momos</h1>
            <p>Experience authentic Nepalese cuisine with traditional flavors and modern presentation</p>
            <div class="hero-buttons">
                <a href="menu.php" class="btn btn-primary">View Menu</a>
                <a href="reservation.php" class="btn btn-secondary">Make Reservation</a>
            </div>
        </div>
        <div class="hero-image">
            <img src="<?= ASSETS_URL ?>/images/hero-momos.jpg" alt="Delicious Momos">
        </div>
    </section>

    <!-- Featured Items -->
    <section class="featured-items">
        <div class="container">
            <h2>Featured Items</h2>
            <p>Try our most popular dishes</p>
            
            <div class="featured-grid">
                <?php foreach ($featuredItems as $item): ?>
                    <div class="featured-item">
                        <div class="featured-item-image">
                            <img src="<?= ASSETS_URL ?>/images/menu/<?= htmlspecialchars($item['image_url'] ?? 'default.jpg') ?>" 
                                 alt="<?= htmlspecialchars($item['name']) ?>"
                                 onerror="this.src='<?= ASSETS_URL ?>/images/food.png'">
                            
                            <?php if ($item['order_count'] > 10): ?>
                                <div class="popularity-badge">
                                    <i class="fas fa-fire"></i>
                                    Popular
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="featured-item-content">
                            <h3><?= htmlspecialchars($item['name']) ?></h3>
                            <p><?= htmlspecialchars($item['description']) ?></p>
                            <div class="price"><?= formatCurrency($item['price']) ?></div>
                            <a href="menu.php" class="btn btn-primary">Order Now</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-preview">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2>About Friends & Momos</h2>
                    <p>We bring you the authentic taste of Nepal with our traditional recipes passed down through generations. Our momos are handcrafted with love and served fresh daily.</p>
                    <p>Experience the warmth of Nepalese hospitality in our cozy restaurant atmosphere.</p>
                    <a href="about.php" class="btn btn-secondary">Learn More</a>
                </div>
                <div class="about-image">
                    <img src="<?= ASSETS_URL ?>/images/restaurant-interior.jpg" alt="Restaurant Interior">
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-preview">
        <div class="container">
            <h2>Visit Us Today</h2>
            <div class="contact-info">
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <h3>Location</h3>
                    <p>123 Restaurant Street<br>City, State 12345</p>
                </div>
                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <h3>Phone</h3>
                    <p>(555) 123-4567</p>
                </div>
                <div class="contact-item">
                    <i class="fas fa-clock"></i>
                    <h3>Hours</h3>
                    <p>Mon-Sun: 11:00 AM - 10:00 PM</p>
                </div>
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <h3>Email</h3>
                    <p>info@friendsmomos.com</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Friends & Momos</h3>
                    <p>Authentic Nepalese cuisine in the heart of the city</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="menu.php">Menu</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="reservation.php">Reservations</a></li>
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

    <script src="<?= ASSETS_URL ?>/js/main.js"></script>
</body>
</html>
