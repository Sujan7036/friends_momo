<!-- Navigation -->
<nav class="navbar">
    <div class="nav-container">
        <a href="../../index.php" class="nav-logo">
            <img src="<?= ASSETS_URL ?>/images/logo.png" alt="Friends & Momos">
            Friends & Momos
        </a>
        
        <div class="nav-menu">
            <a href="../../index.php" class="nav-link">Home</a>
            <a href="../../menu.php" class="nav-link">Menu</a>
            <a href="about.php" class="nav-link">About</a>
            <a href="reservation.php" class="nav-link">Reservations</a>
            <a href="contact.php" class="nav-link">Contact</a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="cart.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i>
                    Cart <span id="cart-count" class="cart-count">0</span>
                </a>
                <div class="nav-dropdown">
                    <a href="#" class="nav-link dropdown-toggle">
                        <i class="fas fa-user"></i>
                        <?= htmlspecialchars(isset($_SESSION['user']['first_name']) ? $_SESSION['user']['first_name'] . ' ' . ($_SESSION['user']['last_name'] ?? '') : 'User') ?>
                    </a>
                    <div class="dropdown-menu">
                        <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
                            <a href="../admin/dashboard.php">Admin Dashboard</a>
                        <?php elseif (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'staff'): ?>
                            <a href="../staff/dashboard.php">Staff Dashboard</a>
                        <?php endif; ?>
                        <a href="../../logout.php">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php" class="nav-link">Login</a>
                <a href="register.php" class="nav-link btn-primary">Register</a>
            <?php endif; ?>
        </div>
        
        <!-- Mobile Menu Toggle -->
        <div class="mobile-menu-toggle">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
</nav>
