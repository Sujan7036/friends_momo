<!-- Admin Sidebar -->
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <img src="<?= ASSETS_URL ?>/images/logo.png" alt="Friends & Momos" class="sidebar-logo">
        <h3>Friends & Momos</h3>
        <span class="admin-label">Admin Panel</span>
    </div>
    
    <nav class="sidebar-nav">
        <ul class="nav-list">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="orders.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Orders</span>
                    <span class="badge">5</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="menu.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'menu.php' ? 'active' : '' ?>">
                    <i class="fas fa-utensils"></i>
                    <span>Menu Management</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="categories.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : '' ?>">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="reservations.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'reservations.php' ? 'active' : '' ?>">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Reservations</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="customers.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : '' ?>">
                    <i class="fas fa-users"></i>
                    <span>Customers</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="staff.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'staff.php' ? 'active' : '' ?>">
                    <i class="fas fa-user-tie"></i>
                    <span>Staff Management</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="inventory.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'inventory.php' ? 'active' : '' ?>">
                    <i class="fas fa-boxes"></i>
                    <span>Inventory</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="reports.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="coupons.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'coupons.php' ? 'active' : '' ?>">
                    <i class="fas fa-percent"></i>
                    <span>Coupons</span>
                </a>
            </li>
            
            
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="user-details">
                <span class="user-name"><?= htmlspecialchars((isset($_SESSION['user_name']) ? $_SESSION['user_name'] : ($_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name']))) ?></span>
                <span class="user-role">Administrator</span>
            </div>
        </div>
        
        <div class="sidebar-actions">
            <a href="../../index.php" class="sidebar-link" title="View Site">
                <i class="fas fa-external-link-alt"></i>
            </a>
            <a href="../../logout.php" class="sidebar-link" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
</aside>
