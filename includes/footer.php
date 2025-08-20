<?php
/**
 * Footer Include File
 * Friends and Momos Restaurant Management System
 */
?>
    </main>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <!-- Restaurant Info -->
                <div class="footer-section">
                    <h3 class="footer-title">
                        <img src="<?= ASSETS_URL ?>/images/logo.png" alt="Friends and Momos" class="footer-logo">
                        Friends and Momos
                    </h3>
                    <p class="footer-description">
                        Authentic taste of the Himalayas in the heart of Gungahlin. 
                        Experience traditional Nepalese cuisine with modern hospitality.
                    </p>
                    <div class="footer-rating">
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <span class="rating-text">4.8/5 from 250+ reviews</span>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="footer-section">
                    <h4 class="footer-heading">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="<?= BASE_URL ?>/views/public/index.php"><i class="fas fa-home"></i> Home</a></li>
                        <li><a href="<?= BASE_URL ?>/views/public/menu.php"><i class="fas fa-utensils"></i> Our Menu</a></li>
                        <li><a href="<?= BASE_URL ?>/views/public/about.php"><i class="fas fa-info-circle"></i> About Us</a></li>
                        <li><a href="<?= BASE_URL ?>/views/public/reservation.php"><i class="fas fa-calendar-alt"></i> Reservations</a></li>
                        <li><a href="<?= BASE_URL ?>/views/public/contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div class="footer-section">
                    <h4 class="footer-heading">Contact Us</h4>
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <strong>Address</strong>
                                <p><?= RESTAURANT_ADDRESS ?></p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <strong>Phone</strong>
                                <p><a href="tel:<?= RESTAURANT_PHONE ?>"><?= RESTAURANT_PHONE ?></a></p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <strong>Email</strong>
                                <p><a href="mailto:<?= RESTAURANT_EMAIL ?>"><?= RESTAURANT_EMAIL ?></a></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Business Hours -->
                <div class="footer-section">
                    <h4 class="footer-heading">Business Hours</h4>
                    <div class="business-hours">
                        <div class="hours-item">
                            <span class="day">Monday - Thursday</span>
                            <span class="time">11:00 AM - 10:00 PM</span>
                        </div>
                        <div class="hours-item">
                            <span class="day">Friday - Saturday</span>
                            <span class="time">11:00 AM - 11:00 PM</span>
                        </div>
                        <div class="hours-item">
                            <span class="day">Sunday</span>
                            <span class="time">11:00 AM - 10:00 PM</span>
                        </div>
                    </div>
                    
                    <!-- Current Status -->
                    <div class="current-status" id="restaurant-status">
                        <i class="fas fa-clock"></i>
                        <span id="status-text">Checking hours...</span>
                    </div>
                </div>
            </div>
            
            <!-- Social Media & Newsletter -->
            <div class="footer-secondary">
                <div class="social-newsletter">
                    <div class="social-section">
                        <h4 class="footer-heading">Follow Us</h4>
                        <div class="social-icons">
                            <a href="https://www.facebook.com/friendsandmomos/" class="social-icon facebook" title="Facebook" aria-label="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://www.instagram.com/friends_and_momos/" class="social-icon instagram" title="Instagram" aria-label="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="https://www.friendsandmomos.com/" class="social-icon google" title="Google Reviews" aria-label="Google Reviews">
                                <i class="fab fa-google"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="newsletter-section">
                        <h4 class="footer-heading">Stay Updated</h4>
                        <p class="newsletter-text">Get the latest news and special offers!</p>
                        <form class="newsletter-form" action="<?= BASE_URL ?>/api/newsletter.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <div class="newsletter-input">
                                <input type="email" name="email" placeholder="Enter your email" required>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i>
                                    Subscribe
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <div class="copyright">
                        <p>&copy; <?= date('Y') ?> Friends and Momos. All rights reserved.</p>
                        <p>Made with <i class="fas fa-heart heart"></i> in Gungahlin</p>
                    </div>
                    
                    <div class="footer-links-bottom">
                        <a href="<?= BASE_URL ?>/views/public/privacy.php">Privacy Policy</a>
                        <a href="<?= BASE_URL ?>/views/public/terms.php">Terms of Service</a>
                        <a href="<?= BASE_URL ?>/views/public/accessibility.php">Accessibility</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Back to Top Button -->
    <button class="back-to-top" id="backToTop" title="Back to Top" aria-label="Back to Top">
        <i class="fas fa-chevron-up"></i>
    </button>
    
    <!-- JavaScript Files -->
    <script src="<?= ASSETS_URL ?>/js/main.js"></script>
    
    <!-- Additional JavaScript if specified -->
    <?php if (isset($additionalJS) && is_array($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?= ASSETS_URL ?>/js/<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Custom scripts for specific pages -->
    <?php if (isset($customScripts)): ?>
        <script><?= $customScripts ?></script>
    <?php endif; ?>
    
    <!-- Google Analytics (if enabled) -->
    <?php if (defined('GA_TRACKING_ID') && GA_TRACKING_ID): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= GA_TRACKING_ID ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?= GA_TRACKING_ID ?>');
    </script>
    <?php endif; ?>
    
</body>
</html>

<style>
/* Footer Styles */
.footer {
    background: linear-gradient(135deg, var(--gray-900) 0%, var(--gray-800) 100%);
    color: var(--white);
    margin-top: var(--space-20);
}

.footer-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--space-8);
    padding: var(--space-16) 0 var(--space-12);
}

.footer-section h3,
.footer-section h4 {
    color: var(--white);
    margin-bottom: var(--space-4);
}

.footer-title {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    font-size: var(--text-xl);
    font-weight: 700;
}

.footer-logo {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-full);
}

.footer-description {
    color: var(--gray-300);
    line-height: 1.6;
    margin-bottom: var(--space-4);
}

.footer-rating {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.stars {
    color: #fbbf24;
}

.rating-text {
    color: var(--gray-300);
    font-size: var(--text-sm);
}

.footer-heading {
    font-size: var(--text-lg);
    font-weight: 600;
    color: var(--primary-color);
    border-bottom: 2px solid var(--primary-color);
    padding-bottom: var(--space-2);
    margin-bottom: var(--space-4);
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: var(--space-2);
}

.footer-links a {
    color: var(--gray-300);
    text-decoration: none;
    transition: var(--transition-fast);
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.footer-links a:hover {
    color: var(--primary-color);
    text-decoration: none;
}

.contact-info {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: var(--space-3);
}

.contact-item i {
    color: var(--primary-color);
    font-size: var(--text-lg);
    margin-top: var(--space-1);
    flex-shrink: 0;
}

.contact-item strong {
    color: var(--white);
    display: block;
    margin-bottom: var(--space-1);
}

.contact-item p {
    color: var(--gray-300);
    margin: 0;
}

.contact-item a {
    color: var(--gray-300);
    transition: var(--transition-fast);
}

.contact-item a:hover {
    color: var(--primary-color);
}

.business-hours {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
    margin-bottom: var(--space-4);
}

.hours-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-2) 0;
    border-bottom: 1px solid var(--gray-700);
}

.day {
    color: var(--gray-300);
    font-weight: 500;
}

.time {
    color: var(--white);
    font-weight: 600;
}

.current-status {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-3);
    background-color: var(--gray-800);
    border-radius: var(--radius-lg);
    border-left: 4px solid var(--success-color);
}

.current-status.closed {
    border-left-color: var(--error-color);
}

.footer-secondary {
    border-top: 1px solid var(--gray-700);
    padding: var(--space-8) 0;
}

.social-newsletter {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--space-8);
    align-items: start;
}

.social-icons {
    display: flex;
    gap: var(--space-3);
    flex-wrap: wrap;
}

.social-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border-radius: var(--radius-full);
    color: var(--white);
    transition: var(--transition);
    font-size: var(--text-lg);
}

.social-icon:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-lg);
}

.social-icon.facebook {
    background-color: #1877f2;
}

.social-icon.instagram {
    background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
}

.social-icon.tiktok {
    background-color: #000000;
}

.social-icon.google {
    background-color: #4285f4;
}

.newsletter-text {
    color: var(--gray-300);
    margin-bottom: var(--space-4);
}

.newsletter-input {
    display: flex;
    gap: var(--space-2);
    max-width: 400px;
}

.newsletter-input input {
    flex: 1;
    padding: var(--space-3);
    border: 2px solid var(--gray-600);
    background-color: var(--gray-800);
    color: var(--white);
    border-radius: var(--radius-md);
    transition: var(--transition);
}

.newsletter-input input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(139, 125, 107, 0.1);
}

.newsletter-input input::placeholder {
    color: var(--gray-400);
}

.footer-bottom {
    border-top: 1px solid var(--gray-700);
    padding: var(--space-6) 0;
}

.footer-bottom-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: var(--space-4);
}

.copyright {
    color: var(--gray-400);
    font-size: var(--text-sm);
}

.heart {
    color: var(--error-color);
    animation: heartbeat 1.5s ease-in-out infinite;
}

@keyframes heartbeat {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.footer-links-bottom {
    display: flex;
    gap: var(--space-6);
}

.footer-links-bottom a {
    color: var(--gray-400);
    font-size: var(--text-sm);
    transition: var(--transition-fast);
}

.footer-links-bottom a:hover {
    color: var(--primary-color);
}

.back-to-top {
    position: fixed;
    bottom: var(--space-8);
    right: var(--space-8);
    width: 50px;
    height: 50px;
    background-color: var(--primary-color);
    color: var(--white);
    border: none;
    border-radius: var(--radius-full);
    cursor: pointer;
    transition: var(--transition);
    opacity: 0;
    visibility: hidden;
    z-index: 100;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: var(--text-lg);
    box-shadow: var(--shadow-lg);
}

.back-to-top.visible {
    opacity: 1;
    visibility: visible;
}

.back-to-top:hover {
    background-color: var(--primary-dark);
    transform: translateY(-3px);
    box-shadow: var(--shadow-xl);
}

/* Responsive Design */
@media (max-width: 768px) {
    .footer-content {
        grid-template-columns: 1fr;
        gap: var(--space-6);
        padding: var(--space-12) 0 var(--space-8);
    }
    
    .social-newsletter {
        grid-template-columns: 1fr;
        gap: var(--space-6);
    }
    
    .footer-bottom-content {
        flex-direction: column;
        text-align: center;
    }
    
    .newsletter-input {
        flex-direction: column;
        max-width: 100%;
    }
    
    .footer-links-bottom {
        flex-wrap: wrap;
        justify-content: center;
    }
}
</style>

<script>
// Restaurant status checker
function updateRestaurantStatus() {
    const now = new Date();
    const day = now.toLocaleLowerCase().substring(0, 3);
    const hour = now.getHours();
    const minute = now.getMinutes();
    const currentTime = hour * 60 + minute;
    
    const hours = {
        mon: {open: 11*60, close: 22*60},
        tue: {open: 11*60, close: 22*60},
        wed: {open: 11*60, close: 22*60},
        thu: {open: 11*60, close: 22*60},
        fri: {open: 11*60, close: 23*60},
        sat: {open: 11*60, close: 23*60},
        sun: {open: 11*60, close: 22*60}
    };
    
    const todayHours = hours[day];
    const statusElement = document.getElementById('restaurant-status');
    const statusText = document.getElementById('status-text');
    
    if (currentTime >= todayHours.open && currentTime <= todayHours.close) {
        statusElement.classList.remove('closed');
        statusText.textContent = 'Open Now';
    } else {
        statusElement.classList.add('closed');
        statusText.textContent = 'Closed';
    }
}

// Back to top functionality
function initBackToTop() {
    const backToTop = document.getElementById('backToTop');
    
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            backToTop.classList.add('visible');
        } else {
            backToTop.classList.remove('visible');
        }
    });
    
    backToTop.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// Newsletter form submission
document.addEventListener('DOMContentLoaded', function() {
    updateRestaurantStatus();
    initBackToTop();
    
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const button = this.querySelector('button[type="submit"]');
            const originalText = button.innerHTML;
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subscribing...';
            button.disabled = true;
            
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Thank you for subscribing to our newsletter!');
                    this.reset();
                } else {
                    alert(data.message || 'Subscription failed. Please try again.');
                }
            })
            .catch(error => {
                console.error('Newsletter subscription error:', error);
                alert('Subscription failed. Please try again.');
            })
            .finally(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            });
        });
    }
});
</script>
