    </main>
    
    <!-- JavaScript Files -->
    <script src="<?= ASSETS_URL ?>/js/main.js"></script>
    <script src="<?= ASSETS_URL ?>/js/admin.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize admin components
        initializeAdminComponents();
        
        // Mobile menu toggle
        const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
        const mobileNav = document.getElementById('mobile-nav');
        const mobileNavOverlay = document.getElementById('mobile-nav-overlay');
        
        if (mobileMenuToggle) {
            mobileMenuToggle.addEventListener('click', function() {
                toggleMobileNav();
            });
        }
        
        if (mobileNavOverlay) {
            mobileNavOverlay.addEventListener('click', function() {
                closeMobileNav();
            });
        }
        
        // User menu dropdown
        const userMenuToggle = document.getElementById('user-menu-toggle');
        const userDropdown = document.getElementById('user-dropdown');
        
        if (userMenuToggle) {
            userMenuToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleUserDropdown();
            });
        }
        
        // Notification dropdown
        const notificationToggle = document.getElementById('notification-toggle');
        const notificationPanel = document.getElementById('notification-panel');
        
        if (notificationToggle) {
            notificationToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleNotificationPanel();
            });
        }
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (userDropdown && !userDropdown.contains(e.target)) {
                closeUserDropdown();
            }
            
            if (notificationPanel && !notificationPanel.contains(e.target)) {
                closeNotificationPanel();
            }
        });
        
        // Mark notifications as read
        const markAllReadBtn = document.querySelector('.mark-all-read');
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', function() {
                markAllNotificationsRead();
            });
        }
        
        // Auto-refresh data every 30 seconds
        setInterval(function() {
            refreshDashboardData();
        }, 30000);
    });
    
    function initializeAdminComponents() {
        // Initialize any admin-specific components
        console.log('Admin components initialized');
    }
    
    function toggleMobileNav() {
        const mobileNav = document.getElementById('mobile-nav');
        const overlay = document.getElementById('mobile-nav-overlay');
        const toggle = document.getElementById('mobile-menu-toggle');
        
        if (mobileNav.classList.contains('active')) {
            closeMobileNav();
        } else {
            mobileNav.classList.add('active');
            overlay.classList.add('active');
            toggle.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }
    
    function closeMobileNav() {
        const mobileNav = document.getElementById('mobile-nav');
        const overlay = document.getElementById('mobile-nav-overlay');
        const toggle = document.getElementById('mobile-menu-toggle');
        
        mobileNav.classList.remove('active');
        overlay.classList.remove('active');
        toggle.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    function toggleUserDropdown() {
        const dropdown = document.getElementById('user-dropdown');
        const isOpen = dropdown.classList.contains('active');
        
        // Close other dropdowns first
        closeNotificationPanel();
        
        if (isOpen) {
            closeUserDropdown();
        } else {
            dropdown.classList.add('active');
        }
    }
    
    function closeUserDropdown() {
        const dropdown = document.getElementById('user-dropdown');
        dropdown.classList.remove('active');
    }
    
    function toggleNotificationPanel() {
        const panel = document.getElementById('notification-panel');
        const isOpen = panel.classList.contains('active');
        
        // Close other dropdowns first
        closeUserDropdown();
        
        if (isOpen) {
            closeNotificationPanel();
        } else {
            panel.classList.add('active');
        }
    }
    
    function closeNotificationPanel() {
        const panel = document.getElementById('notification-panel');
        panel.classList.remove('active');
    }
    
    function markAllNotificationsRead() {
        // Mark all notifications as read
        const unreadItems = document.querySelectorAll('.notification-item.unread');
        unreadItems.forEach(item => {
            item.classList.remove('unread');
        });
        
        // Update notification count
        const notificationCount = document.querySelector('.notification-count');
        if (notificationCount) {
            notificationCount.style.display = 'none';
        }
        
        // In a real app, this would make an AJAX call to mark notifications as read
        showToast('All notifications marked as read', 'success');
    }
    
    function refreshDashboardData() {
        // In a real app, this would refresh dashboard data via AJAX
        // For now, we'll just update the timestamp if it exists
        const lastUpdated = document.querySelector('.last-updated');
        if (lastUpdated) {
            lastUpdated.textContent = 'Last updated: ' + new Date().toLocaleTimeString();
        }
    }
    
    // Global admin functions
    window.adminFunctions = {
        toggleMobileNav,
        closeMobileNav,
        toggleUserDropdown,
        closeUserDropdown,
        toggleNotificationPanel,
        closeNotificationPanel,
        markAllNotificationsRead,
        refreshDashboardData
    };
    </script>
</body>
</html>
