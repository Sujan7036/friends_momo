<?php
/**
 * About Page
 * Friends and Momos Restaurant Management System
 */

// Page configuration
$pageTitle = "About Us - Friends and Momos | Authentic Himalayan Cuisine";
$bodyClass = "about-page";

// Load required files
require_once dirname(__DIR__, 2) . '/config/config.php';

// Include header
include_once dirname(__DIR__, 2) . '/includes/header.php';
?>

<!-- About Hero Section -->
<section class="about-hero">
    <div class="container">
        <div class="about-hero-content">
            <h1 class="page-title">About Friends and Momos</h1>
            <p class="page-subtitle">
                Bringing the authentic taste of the Himalayas to your table since 2019
            </p>
        </div>
    </div>
    <div class="hero-background">
        <img src="<?= ASSETS_URL ?>/images/resturant.png" alt="Restaurant Interior" class="hero-image">
        <div class="hero-overlay"></div>
    </div>
</section>

<!-- Our Story Section -->
<section class="our-story-section">
    <div class="container">
        <div class="story-content">
            <div class="story-text">
                <h2 class="section-title">Our Story</h2>
                <div class="story-description">
                    <p>
                        Friends and Momos was born from a simple dream - to share the rich, authentic flavors 
                        of Nepalese cuisine with the vibrant community of Gungahlin. Our journey began when 
                        a group of friends, passionate about their cultural heritage and love for food, decided 
                        to bring the taste of the Himalayas to Australia.
                    </p>
                    
                    <p>
                        What started as homemade meals shared among friends has grown into a beloved restaurant 
                        that serves as a bridge between cultures. Every dish we prepare tells a story of tradition, 
                        passed down through generations and perfected with love and dedication.
                    </p>
                    
                    <p>
                        Our name reflects our core belief - that food brings people together, turning strangers 
                        into friends over shared meals. Whether you're trying momos for the first time or 
                        you're a long-time lover of Nepalese cuisine, we welcome you to our table as family.
                    </p>
                </div>
                
                <div class="story-highlights">
                    <div class="highlight-item">
                        <div class="highlight-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="highlight-content">
                            <h4>Est. 2019</h4>
                            <p>Serving authentic flavors for over 5 years</p>
                        </div>
                    </div>
                    
                    <div class="highlight-item">
                        <div class="highlight-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="highlight-content">
                            <h4>Family Recipe</h4>
                            <p>Traditional recipes passed down generations</p>
                        </div>
                    </div>
                    
                    <div class="highlight-item">
                        <div class="highlight-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="highlight-content">
                            <h4>Community</h4>
                            <p>Bringing people together through food</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="story-images">
                <div class="image-grid">
                    <div class="image-item large">
                        <img src="<?= ASSETS_URL ?>/images/food.png" alt="Delicious Himalayan Food" class="story-image">
                    </div>
                    <div class="image-item">
                        <img src="<?= ASSETS_URL ?>/images/momo.png" alt="Fresh Momos" class="story-image">
                    </div>
                    <div class="image-item">
                        <img src="<?= ASSETS_URL ?>/images/chowmin.png" alt="Spicy Chowmein" class="story-image">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Our Mission Section -->
<section class="mission-section">
    <div class="container">
        <div class="mission-content">
            <div class="mission-text">
                <h2 class="section-title">Our Mission</h2>
                <div class="mission-grid">
                    <div class="mission-item">
                        <div class="mission-icon">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <h3>Authentic Cuisine</h3>
                        <p>
                            To preserve and share the authentic flavors of Nepalese cuisine, using traditional 
                            cooking methods and the finest ingredients sourced directly from the Himalayas.
                        </p>
                    </div>
                    
                    <div class="mission-item">
                        <div class="mission-icon">
                            <i class="fas fa-globe-asia"></i>
                        </div>
                        <h3>Cultural Bridge</h3>
                        <p>
                            To serve as a cultural bridge, introducing the Australian community to the rich 
                            traditions and warm hospitality of Nepal through our food and service.
                        </p>
                    </div>
                    
                    <div class="mission-item">
                        <div class="mission-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <h3>Fresh & Healthy</h3>
                        <p>
                            To provide fresh, healthy, and nutritious meals that nourish both body and soul, 
                            using locally sourced ingredients whenever possible.
                        </p>
                    </div>
                    
                    <div class="mission-item">
                        <div class="mission-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h3>Community Connection</h3>
                        <p>
                            To create a welcoming space where people from all backgrounds can come together, 
                            share meals, and build lasting friendships over great food.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Our Team Section -->
<section class="team-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Meet Our Team</h2>
            <p class="section-description">
                The passionate people behind the flavors you love
            </p>
        </div>
        
        <div class="team-grid">
            <div class="team-member">
                <div class="member-image">
                    <img src="<?= ASSETS_URL ?>/images/team/chef-ram.jpg" alt="Chef Ram Bahadur" 
                        >
                    <div class="member-overlay">
                        <div class="member-social">
                            <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                </div>
                <div class="member-info">
                    <h3 class="member-name">Ram Bahadur</h3>
                    <p class="member-role">Head Chef & Co-Founder</p>
                    <p class="member-description">
                        With over 15 years of culinary experience, Ram brings authentic Nepalese flavors 
                        to every dish, ensuring each meal is a journey to the Himalayas.
                    </p>
                </div>
            </div>
            
            <div class="team-member">
                <div class="member-image">
                    <img src="<?= ASSETS_URL ?>/images/team/sita-devi.jpg" alt="Sita Devi" 
                        >
                    <div class="member-overlay">
                        <div class="member-social">
                            <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                        </div>
                    </div>
                </div>
                <div class="member-info">
                    <h3 class="member-name">Sita Devi</h3>
                    <p class="member-role">Restaurant Manager & Co-Founder</p>
                    <p class="member-description">
                        Sita ensures every guest feels at home with her warm hospitality and attention 
                        to detail, making every dining experience memorable.
                    </p>
                </div>
            </div>
            
            <div class="team-member">
                <div class="member-image">
                    <img src="<?= ASSETS_URL ?>/images/team/bikash-thapa.jpg" alt="Bikash Thapa" 
                        >
                    <div class="member-overlay">
                        <div class="member-social">
                            <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                </div>
                <div class="member-info">
                    <h3 class="member-name">Bikash Thapa</h3>
                    <p class="member-role">Operations Manager</p>
                    <p class="member-description">
                        Bikash oversees daily operations and ensures smooth service delivery, 
                        from kitchen to table, maintaining our high standards of quality.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Our Values Section -->
<section class="values-section">
    <div class="container">
        <div class="values-content">
            <div class="values-text">
                <h2 class="section-title">Our Values</h2>
                <p class="section-description">
                    These core values guide everything we do, from sourcing ingredients to serving our guests
                </p>
            </div>
            
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3>Quality</h3>
                    <p>
                        We never compromise on quality, from the freshest ingredients to the 
                        finest service, ensuring excellence in every aspect.
                    </p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Authenticity</h3>
                    <p>
                        Every recipe is authentic, preserving the traditional flavors and 
                        cooking methods passed down through generations.
                    </p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3>Hospitality</h3>
                    <p>
                        We treat every guest as family, providing warm, genuine hospitality 
                        that makes everyone feel welcome and valued.
                    </p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-recycle"></i>
                    </div>
                    <h3>Sustainability</h3>
                    <p>
                        We're committed to sustainable practices, supporting local suppliers 
                        and minimizing our environmental impact.
                    </p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Community</h3>
                    <p>
                        We believe in giving back to our community and supporting local 
                        initiatives that make a positive difference.
                    </p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h3>Innovation</h3>
                    <p>
                        While honoring tradition, we continuously innovate to enhance 
                        the dining experience and better serve our guests.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Awards & Recognition Section -->
<section class="awards-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Awards & Recognition</h2>
            <p class="section-description">
                We're honored to be recognized for our commitment to authentic cuisine and excellent service
            </p>
        </div>
        
        <div class="awards-grid">
            <div class="award-item">
                <div class="award-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <h3>Best Nepalese Restaurant</h3>
                <p class="award-year">2023</p>
                <p class="award-description">
                    Canberra Food Awards - Recognized for outstanding authentic Nepalese cuisine
                </p>
            </div>
            
            <div class="award-item">
                <div class="award-icon">
                    <i class="fas fa-medal"></i>
                </div>
                <h3>Excellence in Service</h3>
                <p class="award-year">2022</p>
                <p class="award-description">
                    Gungahlin Business Awards - For exceptional customer service and community engagement
                </p>
            </div>
            
            <div class="award-item">
                <div class="award-icon">
                    <i class="fas fa-certificate"></i>
                </div>
                <h3>Cultural Ambassador</h3>
                <p class="award-year">2021</p>
                <p class="award-description">
                    ACT Multicultural Council - For promoting Nepalese culture through cuisine
                </p>
            </div>
            
            <div class="award-item">
                <div class="award-icon">
                    <i class="fas fa-star"></i>
                </div>
                <h3>5-Star Rating</h3>
                <p class="award-year">Ongoing</p>
                <p class="award-description">
                    Consistently rated 5 stars across all major review platforms by our valued customers
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Location & Contact Section -->
<section class="location-section">
    <div class="container">
        <div class="location-content">
            <div class="location-info">
                <h2 class="section-title">Visit Us</h2>
                <p class="section-description">
                    Come experience the flavors of the Himalayas in the heart of Gungahlin
                </p>
                
                <div class="contact-details">
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-content">
                            <h4>Address</h4>
                            <p>
                                Shop 15, Gungahlin Market Place<br>
                                33 Hibberson Street, Gungahlin ACT 2912<br>
                                Australia
                            </p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="contact-content">
                            <h4>Phone</h4>
                            <p>
                                <a href="tel:+61262424567">(02) 6242 4567</a><br>
                                <a href="tel:+61401234567">0401 234 567</a>
                            </p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-content">
                            <h4>Email</h4>
                            <p>
                                <a href="mailto:info@friendsandmomos.com.au">info@friendsandmomos.com.au</a><br>
                                <a href="mailto:orders@friendsandmomos.com.au">orders@friendsandmomos.com.au</a>
                            </p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="contact-content">
                            <h4>Opening Hours</h4>
                            <div class="hours-list">
                                <div class="hour-item">
                                    <span class="day">Monday - Thursday</span>
                                    <span class="time">11:00 AM - 9:30 PM</span>
                                </div>
                                <div class="hour-item">
                                    <span class="day">Friday - Saturday</span>
                                    <span class="time">11:00 AM - 10:00 PM</span>
                                </div>
                                <div class="hour-item">
                                    <span class="day">Sunday</span>
                                    <span class="time">11:00 AM - 9:00 PM</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="<?= BASE_URL ?>/views/public/reservation.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-calendar-check"></i>
                        Make a Reservation
                    </a>
                    <a href="tel:+61262424567" class="btn btn-secondary btn-lg">
                        <i class="fas fa-phone"></i>
                        Call Now
                    </a>
                </div>
            </div>
            
            <div class="location-map">
                    <div class="map-overlay">
                        <a href="https://maps.google.com/?q=Gungahlin+Market+Place,+Gungahlin+ACT" 
                           target="_blank" 
                           class="btn btn-primary">
                            <i class="fas fa-directions"></i>
                            Get Directions
                        </a>
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
                Join our family of food lovers and discover why Friends and Momos is 
                Gungahlin's favorite destination for authentic Himalayan cuisine.
            </p>
            <div class="cta-buttons">
                <a href="<?= BASE_URL ?>/views/public/menu.php" class="btn btn-primary btn-xl">
                    <i class="fas fa-utensils"></i>
                    Explore Our Menu
                </a>
                <a href="<?= BASE_URL ?>/views/public/reservation.php" class="btn btn-secondary btn-xl">
                    <i class="fas fa-calendar-alt"></i>
                    Book a Table
                </a>
            </div>
        </div>
    </div>
</section>

<style>
/* About Page Specific Styles */
.about-hero {
    position: relative;
    min-height: 60vh;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: var(--white);
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

.hero-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(139, 125, 107, 0.8) 0%, rgba(74, 85, 104, 0.9) 100%);
}

.about-hero-content {
    position: relative;
    z-index: 2;
    max-width: 600px;
    margin: 0 auto;
}

.page-title {
    font-size: var(--text-5xl);
    font-weight: 700;
    margin-bottom: var(--space-6);
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

.page-subtitle {
    font-size: var(--text-xl);
    color: var(--gray-100);
    line-height: 1.6;
}

.our-story-section {
    padding: var(--space-20) 0;
}

.story-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-16);
    align-items: start;
}

.section-title {
    font-size: var(--text-4xl);
    color: var(--gray-900);
    margin-bottom: var(--space-8);
    position: relative;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -var(--space-3);
    left: 0;
    width: 80px;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    border-radius: var(--radius);
}

.story-description p {
    font-size: var(--text-lg);
    line-height: 1.7;
    color: var(--gray-700);
    margin-bottom: var(--space-6);
}

.story-highlights {
    margin-top: var(--space-8);
    display: flex;
    flex-direction: column;
    gap: var(--space-6);
}

.highlight-item {
    display: flex;
    align-items: center;
    gap: var(--space-4);
    padding: var(--space-4);
    background-color: var(--gray-50);
    border-radius: var(--radius-xl);
    border-left: 4px solid var(--primary-color);
}

.highlight-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-size: var(--text-xl);
    flex-shrink: 0;
}

.highlight-content h4 {
    font-size: var(--text-lg);
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: var(--space-2);
}

.highlight-content p {
    color: var(--gray-600);
    margin: 0;
}

.story-images {
    position: relative;
}

.image-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-template-rows: 1fr 1fr;
    gap: var(--space-4);
    height: 500px;
}

.image-item {
    border-radius: var(--radius-2xl);
    overflow: hidden;
    box-shadow: var(--shadow-lg);
    transition: var(--transition);
}

.image-item:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-2xl);
}

.image-item.large {
    grid-row: 1 / 3;
}

.story-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.mission-section {
    padding: var(--space-20) 0;
    background-color: var(--gray-50);
}

.mission-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: var(--space-8);
    margin-top: var(--space-12);
}

.mission-item {
    background-color: var(--white);
    padding: var(--space-8);
    border-radius: var(--radius-2xl);
    box-shadow: var(--shadow);
    text-align: center;
    transition: var(--transition);
}

.mission-item:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-xl);
}

.mission-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, var(--accent-color), var(--primary-color));
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto var(--space-6);
    color: var(--white);
    font-size: var(--text-2xl);
}

.mission-item h3 {
    font-size: var(--text-xl);
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: var(--space-4);
}

.mission-item p {
    color: var(--gray-600);
    line-height: 1.6;
}

.team-section {
    padding: var(--space-20) 0;
}

.section-header {
    text-align: center;
    margin-bottom: var(--space-16);
}

.section-description {
    font-size: var(--text-lg);
    color: var(--gray-600);
    max-width: 600px;
    margin: var(--space-4) auto 0;
}

.team-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: var(--space-8);
}

.team-member {
    background-color: var(--white);
    border-radius: var(--radius-2xl);
    overflow: hidden;
    box-shadow: var(--shadow);
    transition: var(--transition);
}

.team-member:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-xl);
}

.member-image {
    position: relative;
    height: 300px;
    overflow: hidden;
}

.member-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition);
}

.member-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: var(--transition);
}

.team-member:hover .member-overlay {
    opacity: 1;
}

.team-member:hover .member-image img {
    transform: scale(1.1);
}

.member-social {
    display: flex;
    gap: var(--space-4);
}

.social-link {
    width: 50px;
    height: 50px;
    background-color: var(--white);
    color: var(--primary-color);
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: var(--text-xl);
    text-decoration: none;
    transition: var(--transition);
}

.social-link:hover {
    background-color: var(--primary-color);
    color: var(--white);
    transform: scale(1.1);
}

.member-info {
    padding: var(--space-6);
}

.member-name {
    font-size: var(--text-xl);
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: var(--space-2);
}

.member-role {
    color: var(--primary-color);
    font-weight: 500;
    margin-bottom: var(--space-4);
}

.member-description {
    color: var(--gray-600);
    line-height: 1.6;
}

.values-section {
    padding: var(--space-20) 0;
    background-color: var(--gray-50);
}

.values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--space-6);
    margin-top: var(--space-12);
}

.value-card {
    background-color: var(--white);
    padding: var(--space-6);
    border-radius: var(--radius-xl);
    text-align: center;
    box-shadow: var(--shadow);
    transition: var(--transition);
}

.value-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.value-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto var(--space-4);
    color: var(--white);
    font-size: var(--text-xl);
}

.value-card h3 {
    font-size: var(--text-lg);
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: var(--space-3);
}

.value-card p {
    color: var(--gray-600);
    line-height: 1.6;
    font-size: var(--text-sm);
}

.awards-section {
    padding: var(--space-20) 0;
}

.awards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--space-8);
}

.award-item {
    text-align: center;
    padding: var(--space-6);
    border-radius: var(--radius-xl);
    background-color: var(--white);
    box-shadow: var(--shadow);
    transition: var(--transition);
}

.award-item:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.award-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, var(--warning-color), var(--warning-light));
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto var(--space-4);
    color: var(--white);
    font-size: var(--text-2xl);
}

.award-item h3 {
    font-size: var(--text-lg);
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: var(--space-2);
}

.award-year {
    color: var(--primary-color);
    font-weight: 700;
    font-size: var(--text-lg);
    margin-bottom: var(--space-3);
}

.award-description {
    color: var(--gray-600);
    line-height: 1.6;
    font-size: var(--text-sm);
}

.location-section {
    padding: var(--space-20) 0;
    background-color: var(--gray-50);
}

.location-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-16);
    align-items: start;
}

.contact-details {
    margin: var(--space-8) 0;
}

.contact-item {
    display: flex;
    gap: var(--space-4);
    margin-bottom: var(--space-6);
    padding: var(--space-4);
    background-color: var(--white);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow);
}

.contact-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-size: var(--text-lg);
    flex-shrink: 0;
}

.contact-content h4 {
    font-size: var(--text-lg);
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: var(--space-2);
}

.contact-content p {
    color: var(--gray-600);
    line-height: 1.5;
    margin: 0;
}

.contact-content a {
    color: var(--primary-color);
    text-decoration: none;
}

.contact-content a:hover {
    text-decoration: underline;
}

.hours-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.hour-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-2) 0;
    border-bottom: 1px solid var(--gray-200);
}

.hour-item:last-child {
    border-bottom: none;
}

.day {
    font-weight: 500;
    color: var(--gray-700);
}

.time {
    color: var(--primary-color);
    font-weight: 600;
}

.action-buttons {
    display: flex;
    gap: var(--space-4);
    margin-top: var(--space-8);
    flex-wrap: wrap;
}

.location-map {
    position: relative;
}

.map-container {
    position: relative;
    border-radius: var(--radius-2xl);
    overflow: hidden;
    box-shadow: var(--shadow-xl);
}

.location-image {
    width: 100%;
    height: 400px;
    object-fit: cover;
}

.map-overlay {
    position: absolute;
    bottom: var(--space-6);
    left: var(--space-6);
    right: var(--space-6);
    text-align: center;
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
    max-width: 700px;
    margin-left: auto;
    margin-right: auto;
}

.cta-buttons {
    display: flex;
    gap: var(--space-4);
    justify-content: center;
    flex-wrap: wrap;
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-title {
        font-size: var(--text-3xl);
    }
    
    .story-content {
        grid-template-columns: 1fr;
        gap: var(--space-8);
    }
    
    .image-grid {
        grid-template-columns: 1fr;
        grid-template-rows: repeat(3, 200px);
    }
    
    .image-item.large {
        grid-row: auto;
    }
    
    .location-content {
        grid-template-columns: 1fr;
        gap: var(--space-8);
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .cta-title {
        font-size: var(--text-3xl);
    }
    
    .cta-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .contact-item {
        flex-direction: column;
        text-align: center;
        gap: var(--space-3);
    }
    
    .hour-item {
        flex-direction: column;
        align-items: center;
        gap: var(--space-1);
    }
}
</style>

<?php include_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
