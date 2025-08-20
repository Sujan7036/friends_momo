<?php
/**
 * Reservation Page
 * Friends and Momos Restaurant Management System
 */

// Page configuration
$pageTitle = "Make Reservation - Friends and Momos | Authentic Himalayan Cuisine";
$bodyClass = "reservation-page";

// Load required files
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/models/Reservation.php';

// Initialize models
$reservationModel = new Reservation();

// Handle form submission
$formSubmitted = false;
$formSuccess = false;
$formErrors = [];
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formSubmitted = true;
    
    // Get form data
    $formData = [
        'name' => trim($_POST['name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'date' => $_POST['date'] ?? '',
        'time' => $_POST['time'] ?? '',
        'guests' => (int)($_POST['guests'] ?? 1),
        'special_requests' => trim($_POST['special_requests'] ?? ''),
        'occasion' => $_POST['occasion'] ?? ''
    ];
    
    // Validate form data
    if (empty($formData['name'])) {
        $formErrors['name'] = 'Full name is required.';
    }
    
    if (empty($formData['email'])) {
        $formErrors['email'] = 'Email address is required.';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $formErrors['email'] = 'Please enter a valid email address.';
    }
    
    if (empty($formData['phone'])) {
        $formErrors['phone'] = 'Phone number is required.';
    } elseif (!preg_match('/^[\d\s\-\+\(\)]{10,15}$/', $formData['phone'])) {
        $formErrors['phone'] = 'Please enter a valid phone number.';
    }
    
    if (empty($formData['date'])) {
        $formErrors['date'] = 'Reservation date is required.';
    } else {
        $reservationDate = new DateTime($formData['date']);
        $today = new DateTime();
        $maxDate = (new DateTime())->add(new DateInterval('P90D')); // 90 days from today
        
        if ($reservationDate < $today) {
            $formErrors['date'] = 'Please select a future date.';
        } elseif ($reservationDate > $maxDate) {
            $formErrors['date'] = 'Reservations can only be made up to 90 days in advance.';
        }
    }
    
    if (empty($formData['time'])) {
        $formErrors['time'] = 'Reservation time is required.';
    }
    
    if ($formData['guests'] < 1 || $formData['guests'] > 12) {
        $formErrors['guests'] = 'Number of guests must be between 1 and 12.';
    }
    
    // Skip availability check - allow all reservations
    // Note: Slot availability checking has been disabled
    
    // Create reservation if no errors
    if (empty($formErrors)) {
        // Map form fields to database columns
        $reservationData = [
            'customer_name' => $formData['name'],
            'customer_email' => $formData['email'],
            'customer_phone' => $formData['phone'],
            'party_size' => $formData['guests'],
            'reservation_date' => $formData['date'],
            'reservation_time' => $formData['time'],
            'special_requests' => $formData['special_requests'] ?? '',
            'status' => 'pending'
        ];
        
        // Add user_id if logged in
        if (isset($_SESSION['user_id'])) {
            $reservationData['user_id'] = $_SESSION['user_id'];
        }
        
        $reservationId = $reservationModel->create($reservationData);
        
        if ($reservationId) {
            $formSuccess = true;
            
            // Send confirmation email (in a real app)
            // sendReservationConfirmation($formData, $reservationId);
            
            // Clear form data on success
            $formData = [];
        } else {
            $formErrors['general'] = 'Sorry, there was an error processing your reservation. Please try again.';
        }
    }
}

// Get today's date for date picker minimum
$todayDate = date('Y-m-d');
$maxDate = date('Y-m-d', strtotime('+90 days'));

// Include header
include_once dirname(__DIR__, 2) . '/includes/header.php';
?>

<!-- Reservation Hero Section -->
<section class="reservation-hero">
    <div class="container">
        <div class="reservation-hero-content">
            <h1 class="page-title">Make a Reservation</h1>
            <p class="page-description">
                Reserve your table for an unforgettable dining experience with authentic Himalayan flavors. 
                Book now to ensure your spot at Friends and Momos.
            </p>
        </div>
    </div>
</section>

<!-- Reservation Form Section -->
<section class="reservation-form-section">
    <div class="container">
        <div class="reservation-content">
            <!-- Reservation Form -->
            <div class="reservation-form-container">
                <?php if ($formSuccess): ?>
                    <!-- Success Message -->
                    <div class="success-message">
                        <div class="success-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3>Reservation Confirmed!</h3>
                        <p>
                            Thank you for choosing Friends and Momos! Your reservation has been successfully submitted. 
                            We'll call you within 24 hours to confirm your booking details.
                        </p>
                        <div class="success-actions">
                            <a href="<?= BASE_URL ?>/views/public/index.php" class="btn btn-primary">
                                <i class="fas fa-home"></i>
                                Back to Home
                            </a>
                            <a href="<?= BASE_URL ?>/views/public/menu.php" class="btn btn-secondary">
                                <i class="fas fa-utensils"></i>
                                View Menu
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Reservation Form -->
                    <div class="form-header">
                        <h2>Book Your Table</h2>
                        <p>Fill in the details below to reserve your table. All fields marked with * are required.</p>
                    </div>
                    
                    <?php if (!empty($formErrors['general'])): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?= htmlspecialchars($formErrors['general']) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" id="reservation-form" class="reservation-form" novalidate>
                        <div class="form-grid">
                            <!-- Personal Information -->
                            <div class="form-section">
                                <h3 class="section-title">
                                    <i class="fas fa-user"></i>
                                    Personal Information
                                </h3>
                                
                                <div class="form-group">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" 
                                           id="name" 
                                           name="name" 
                                           class="form-input <?= isset($formErrors['name']) ? 'error' : '' ?>"
                                           value="<?= htmlspecialchars($formData['name'] ?? '') ?>"
                                           placeholder="Enter your full name"
                                           required>
                                    <?php if (isset($formErrors['name'])): ?>
                                        <span class="error-message">
                                            <i class="fas fa-exclamation-circle"></i>
                                            <?= htmlspecialchars($formErrors['name']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" 
                                           id="email" 
                                           name="email" 
                                           class="form-input <?= isset($formErrors['email']) ? 'error' : '' ?>"
                                           value="<?= htmlspecialchars($formData['email'] ?? '') ?>"
                                           placeholder="Enter your email address"
                                           required>
                                    <?php if (isset($formErrors['email'])): ?>
                                        <span class="error-message">
                                            <i class="fas fa-exclamation-circle"></i>
                                            <?= htmlspecialchars($formErrors['email']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone" class="form-label">Phone Number *</label>
                                    <input type="tel" 
                                           id="phone" 
                                           name="phone" 
                                           class="form-input <?= isset($formErrors['phone']) ? 'error' : '' ?>"
                                           value="<?= htmlspecialchars($formData['phone'] ?? '') ?>"
                                           placeholder="Enter your phone number"
                                           required>
                                    <?php if (isset($formErrors['phone'])): ?>
                                        <span class="error-message">
                                            <i class="fas fa-exclamation-circle"></i>
                                            <?= htmlspecialchars($formErrors['phone']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Reservation Details -->
                            <div class="form-section">
                                <h3 class="section-title">
                                    <i class="fas fa-calendar-alt"></i>
                                    Reservation Details
                                </h3>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="date" class="form-label">Date *</label>
                                        <input type="date" 
                                               id="date" 
                                               name="date" 
                                               class="form-input <?= isset($formErrors['date']) ? 'error' : '' ?>"
                                               value="<?= htmlspecialchars($formData['date'] ?? '') ?>"
                                               min="<?= $todayDate ?>"
                                               max="<?= $maxDate ?>"
                                               required>
                                        <?php if (isset($formErrors['date'])): ?>
                                            <span class="error-message">
                                                <i class="fas fa-exclamation-circle"></i>
                                                <?= htmlspecialchars($formErrors['date']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="time" class="form-label">Time *</label>
                                        <select id="time" 
                                                name="time" 
                                                class="form-input <?= isset($formErrors['time']) ? 'error' : '' ?>"
                                                required>
                                            <option value="">Select time</option>
                                            <optgroup label="Lunch">
                                                <option value="11:00" <?= ($formData['time'] ?? '') === '11:00' ? 'selected' : '' ?>>11:00 AM</option>
                                                <option value="11:30" <?= ($formData['time'] ?? '') === '11:30' ? 'selected' : '' ?>>11:30 AM</option>
                                                <option value="12:00" <?= ($formData['time'] ?? '') === '12:00' ? 'selected' : '' ?>>12:00 PM</option>
                                                <option value="12:30" <?= ($formData['time'] ?? '') === '12:30' ? 'selected' : '' ?>>12:30 PM</option>
                                                <option value="13:00" <?= ($formData['time'] ?? '') === '13:00' ? 'selected' : '' ?>>1:00 PM</option>
                                                <option value="13:30" <?= ($formData['time'] ?? '') === '13:30' ? 'selected' : '' ?>>1:30 PM</option>
                                                <option value="14:00" <?= ($formData['time'] ?? '') === '14:00' ? 'selected' : '' ?>>2:00 PM</option>
                                                <option value="14:30" <?= ($formData['time'] ?? '') === '14:30' ? 'selected' : '' ?>>2:30 PM</option>
                                            </optgroup>
                                            <optgroup label="Dinner">
                                                <option value="17:00" <?= ($formData['time'] ?? '') === '17:00' ? 'selected' : '' ?>>5:00 PM</option>
                                                <option value="17:30" <?= ($formData['time'] ?? '') === '17:30' ? 'selected' : '' ?>>5:30 PM</option>
                                                <option value="18:00" <?= ($formData['time'] ?? '') === '18:00' ? 'selected' : '' ?>>6:00 PM</option>
                                                <option value="18:30" <?= ($formData['time'] ?? '') === '18:30' ? 'selected' : '' ?>>6:30 PM</option>
                                                <option value="19:00" <?= ($formData['time'] ?? '') === '19:00' ? 'selected' : '' ?>>7:00 PM</option>
                                                <option value="19:30" <?= ($formData['time'] ?? '') === '19:30' ? 'selected' : '' ?>>7:30 PM</option>
                                                <option value="20:00" <?= ($formData['time'] ?? '') === '20:00' ? 'selected' : '' ?>>8:00 PM</option>
                                                <option value="20:30" <?= ($formData['time'] ?? '') === '20:30' ? 'selected' : '' ?>>8:30 PM</option>
                                                <option value="21:00" <?= ($formData['time'] ?? '') === '21:00' ? 'selected' : '' ?>>9:00 PM</option>
                                            </optgroup>
                                        </select>
                                        <?php if (isset($formErrors['time'])): ?>
                                            <span class="error-message">
                                                <i class="fas fa-exclamation-circle"></i>
                                                <?= htmlspecialchars($formErrors['time']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="guests" class="form-label">Number of Guests *</label>
                                    <select id="guests" 
                                            name="guests" 
                                            class="form-input <?= isset($formErrors['guests']) ? 'error' : '' ?>"
                                            required>
                                        <?php for ($i = 1; $i <= 12; $i++): ?>
                                            <option value="<?= $i ?>" <?= ($formData['guests'] ?? 1) == $i ? 'selected' : '' ?>>
                                                <?= $i ?> <?= $i === 1 ? 'Person' : 'People' ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                    <?php if (isset($formErrors['guests'])): ?>
                                        <span class="error-message">
                                            <i class="fas fa-exclamation-circle"></i>
                                            <?= htmlspecialchars($formErrors['guests']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <small class="form-help">For parties larger than 12, please call us directly.</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="occasion" class="form-label">Special Occasion (Optional)</label>
                                    <select id="occasion" name="occasion" class="form-input">
                                        <option value="">Select occasion</option>
                                        <option value="birthday" <?= ($formData['occasion'] ?? '') === 'birthday' ? 'selected' : '' ?>>Birthday</option>
                                        <option value="anniversary" <?= ($formData['occasion'] ?? '') === 'anniversary' ? 'selected' : '' ?>>Anniversary</option>
                                        <option value="graduation" <?= ($formData['occasion'] ?? '') === 'graduation' ? 'selected' : '' ?>>Graduation</option>
                                        <option value="business" <?= ($formData['occasion'] ?? '') === 'business' ? 'selected' : '' ?>>Business Meeting</option>
                                        <option value="date" <?= ($formData['occasion'] ?? '') === 'date' ? 'selected' : '' ?>>Date Night</option>
                                        <option value="family" <?= ($formData['occasion'] ?? '') === 'family' ? 'selected' : '' ?>>Family Gathering</option>
                                        <option value="other" <?= ($formData['occasion'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="special_requests" class="form-label">Special Requests (Optional)</label>
                                    <textarea id="special_requests" 
                                              name="special_requests" 
                                              class="form-input"
                                              rows="4"
                                              placeholder="Any dietary restrictions, allergies, or special requests..."><?= htmlspecialchars($formData['special_requests'] ?? '') ?></textarea>
                                    <small class="form-help">Please let us know about any allergies or special dietary needs.</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-xl submit-btn">
                                <i class="fas fa-calendar-check"></i>
                                <span class="btn-text">Make Reservation</span>
                                <span class="btn-loading" style="display: none;">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    Processing...
                                </span>
                            </button>
                            
                            <div class="form-note">
                                <p>
                                    <i class="fas fa-info-circle"></i>
                                    We'll call you within 24 hours to confirm your reservation. 
                                    For immediate confirmation, please call us at 
                                    <a href="tel:+61262424567">(02) 6242 4567</a>.
                                </p>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
            
            <!-- Restaurant Information -->
            <div class="restaurant-info">
                <div class="info-card">
                    <div class="info-header">
                        <h3>Restaurant Information</h3>
                    </div>
                    
                    <div class="info-content">
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="info-details">
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
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="info-details">
                                <h4>Location</h4>
                                <p>
                                    Shop 15, Gungahlin Market Place<br>
                                    33 Hibberson Street<br>
                                    Gungahlin ACT 2912, Australia
                                </p>
                                <a href="https://maps.google.com/?q=Gungahlin+Market+Place,+Gungahlin+ACT" 
                                   target="_blank" 
                                   class="location-link">
                                    <i class="fas fa-directions"></i>
                                    Get Directions
                                </a>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="info-details">
                                <h4>Contact</h4>
                                <p>
                                    Phone: <a href="tel:+61262424567">(02) 6242 4567</a><br>
                                    Mobile: <a href="tel:+61401234567">0401 234 567</a><br>
                                    Email: <a href="mailto:info@friendsandmomos.com.au">info@friendsandmomos.com.au</a>
                                </p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="info-details">
                                <h4>Group Bookings</h4>
                                <p>
                                    For parties of 13 or more, please call us directly 
                                    to discuss special arrangements and group menus.
                                </p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-calendar-times"></i>
                            </div>
                            <div class="info-details">
                                <h4>Cancellation Policy</h4>
                                <p>
                                    Please notify us at least 2 hours in advance 
                                    if you need to cancel or modify your reservation.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="quick-actions">
                    <a href="<?= BASE_URL ?>/views/public/menu.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <div class="action-content">
                            <h4>View Menu</h4>
                            <p>Explore our delicious Himalayan dishes</p>
                        </div>
                    </a>
                    
                    <a href="tel:+61262424567" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="action-content">
                            <h4>Call Now</h4>
                            <p>Immediate reservation assistance</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Reservation Page Specific Styles */
.reservation-hero {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
    color: var(--white);
    padding: var(--space-16) 0 var(--space-12);
    text-align: center;
}

.reservation-hero-content {
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

.reservation-form-section {
    padding: var(--space-16) 0;
    background-color: var(--gray-50);
}

.reservation-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: var(--space-12);
    align-items: start;
}

.reservation-form-container {
    background-color: var(--white);
    border-radius: var(--radius-2xl);
    padding: var(--space-8);
    box-shadow: var(--shadow-xl);
}

.form-header {
    text-align: center;
    margin-bottom: var(--space-8);
}

.form-header h2 {
    font-size: var(--text-3xl);
    color: var(--gray-900);
    margin-bottom: var(--space-4);
}

.form-header p {
    color: var(--gray-600);
    font-size: var(--text-lg);
}

.reservation-form {
    display: flex;
    flex-direction: column;
    gap: var(--space-8);
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-8);
}

.form-section {
    display: flex;
    flex-direction: column;
    gap: var(--space-6);
}

.section-title {
    font-size: var(--text-xl);
    font-weight: 600;
    color: var(--gray-900);
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding-bottom: var(--space-3);
    border-bottom: 2px solid var(--gray-200);
    margin-bottom: var(--space-3);
}

.section-title i {
    color: var(--primary-color);
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.form-label {
    font-weight: 600;
    color: var(--gray-700);
    font-size: var(--text-base);
}

.form-input {
    padding: var(--space-4);
    border: 2px solid var(--gray-300);
    border-radius: var(--radius-lg);
    font-size: var(--text-base);
    transition: var(--transition);
    background-color: var(--white);
}

.form-input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
}

.form-input.error {
    border-color: var(--error-color);
    background-color: var(--error-light);
}

.form-input::placeholder {
    color: var(--gray-500);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-4);
}

.error-message {
    color: var(--error-color);
    font-size: var(--text-sm);
    display: flex;
    align-items: center;
    gap: var(--space-2);
    margin-top: var(--space-1);
}

.form-help {
    color: var(--gray-500);
    font-size: var(--text-sm);
    margin-top: var(--space-1);
}

.form-actions {
    text-align: center;
    margin-top: var(--space-8);
}

.submit-btn {
    position: relative;
    overflow: hidden;
    min-width: 200px;
}

.btn-text, .btn-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-2);
}

.form-note {
    margin-top: var(--space-6);
    padding: var(--space-4);
    background-color: var(--info-light);
    border-radius: var(--radius-lg);
    border-left: 4px solid var(--info-color);
}

.form-note p {
    margin: 0;
    color: var(--info-dark);
    font-size: var(--text-sm);
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.form-note a {
    color: var(--primary-color);
    font-weight: 600;
    text-decoration: none;
}

.form-note a:hover {
    text-decoration: underline;
}

.success-message {
    text-align: center;
    padding: var(--space-12) var(--space-6);
}

.success-icon {
    font-size: var(--text-6xl);
    color: var(--success-color);
    margin-bottom: var(--space-6);
}

.success-message h3 {
    font-size: var(--text-3xl);
    color: var(--gray-900);
    margin-bottom: var(--space-4);
}

.success-message p {
    font-size: var(--text-lg);
    color: var(--gray-600);
    line-height: 1.6;
    margin-bottom: var(--space-8);
}

.success-actions {
    display: flex;
    gap: var(--space-4);
    justify-content: center;
    flex-wrap: wrap;
}

.restaurant-info {
    display: flex;
    flex-direction: column;
    gap: var(--space-6);
}

.info-card {
    background-color: var(--white);
    border-radius: var(--radius-2xl);
    overflow: hidden;
    box-shadow: var(--shadow);
}

.info-header {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    color: var(--white);
    padding: var(--space-6);
    text-align: center;
}

.info-header h3 {
    font-size: var(--text-xl);
    font-weight: 600;
    margin: 0;
}

.info-content {
    padding: var(--space-6);
}

.info-item {
    display: flex;
    gap: var(--space-4);
    margin-bottom: var(--space-6);
    padding-bottom: var(--space-6);
    border-bottom: 1px solid var(--gray-200);
}

.info-item:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.info-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-size: var(--text-lg);
    flex-shrink: 0;
}

.info-details h4 {
    font-size: var(--text-lg);
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: var(--space-3);
}

.info-details p {
    color: var(--gray-600);
    line-height: 1.6;
    margin: 0;
}

.info-details a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
}

.info-details a:hover {
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
}

.day {
    font-weight: 500;
    color: var(--gray-700);
}

.time {
    color: var(--primary-color);
    font-weight: 600;
}

.location-link {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    margin-top: var(--space-3);
    padding: var(--space-2) var(--space-4);
    background-color: var(--primary-light);
    color: var(--primary-dark);
    border-radius: var(--radius);
    text-decoration: none;
    font-size: var(--text-sm);
    font-weight: 500;
    transition: var(--transition);
}

.location-link:hover {
    background-color: var(--primary-color);
    color: var(--white);
}

.quick-actions {
    display: grid;
    gap: var(--space-4);
}

.action-card {
    display: flex;
    align-items: center;
    gap: var(--space-4);
    padding: var(--space-4);
    background-color: var(--white);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow);
    text-decoration: none;
    transition: var(--transition);
}

.action-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.action-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, var(--accent-color), var(--secondary-color));
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-size: var(--text-lg);
    flex-shrink: 0;
}

.action-content h4 {
    font-size: var(--text-base);
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: var(--space-1);
}

.action-content p {
    color: var(--gray-600);
    font-size: var(--text-sm);
    margin: 0;
}

.alert {
    padding: var(--space-4);
    border-radius: var(--radius-lg);
    margin-bottom: var(--space-6);
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.alert-error {
    background-color: var(--error-light);
    color: var(--error-dark);
    border-left: 4px solid var(--error-color);
}

/* Responsive Design */
@media (max-width: 968px) {
    .reservation-content {
        grid-template-columns: 1fr;
        gap: var(--space-8);
    }
    
    .form-grid {
        grid-template-columns: 1fr;
        gap: var(--space-6);
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: var(--space-4);
    }
}

@media (max-width: 768px) {
    .page-title {
        font-size: var(--text-3xl);
    }
    
    .reservation-form-container {
        padding: var(--space-6);
    }
    
    .form-header h2 {
        font-size: var(--text-2xl);
    }
    
    .success-actions {
        flex-direction: column;
    }
    
    .action-card {
        flex-direction: column;
        text-align: center;
        gap: var(--space-3);
    }
    
    .hour-item {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--space-1);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('reservation-form');
    const submitBtn = document.querySelector('.submit-btn');
    const btnText = document.querySelector('.btn-text');
    const btnLoading = document.querySelector('.btn-loading');
    
    // Form validation
    const inputs = form.querySelectorAll('input, select, textarea');
    
    inputs.forEach(input => {
        input.addEventListener('blur', validateField);
        input.addEventListener('input', clearErrors);
    });
    
    // Date and time validation
    const dateInput = document.getElementById('date');
    const timeInput = document.getElementById('time');
    
    dateInput.addEventListener('change', function() {
        const selectedDate = new Date(this.value);
        const today = new Date();
        const dayOfWeek = selectedDate.getDay(); // 0 = Sunday, 6 = Saturday
        
        // Clear existing time validation
        timeInput.classList.remove('error');
        const existingError = timeInput.parentNode.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
        
        // Update time options based on day
        updateTimeOptions(dayOfWeek);
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validate all fields
        inputs.forEach(input => {
            if (!validateField({ target: input })) {
                isValid = false;
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            return;
        }
        
        // Show loading state
        submitBtn.disabled = true;
        btnText.style.display = 'none';
        btnLoading.style.display = 'flex';
    });
    
    function validateField(e) {
        const field = e.target;
        const value = field.value.trim();
        let isValid = true;
        
        // Clear existing errors
        field.classList.remove('error');
        const existingError = field.parentNode.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
        
        // Required field validation
        if (field.hasAttribute('required') && !value) {
            showError(field, `${getFieldLabel(field)} is required.`);
            isValid = false;
        }
        
        // Specific field validations
        if (value) {
            switch (field.type) {
                case 'email':
                    if (!isValidEmail(value)) {
                        showError(field, 'Please enter a valid email address.');
                        isValid = false;
                    }
                    break;
                    
                case 'tel':
                    if (!isValidPhone(value)) {
                        showError(field, 'Please enter a valid phone number.');
                        isValid = false;
                    }
                    break;
                    
                case 'date':
                    if (!isValidDate(value)) {
                        showError(field, 'Please select a valid future date.');
                        isValid = false;
                    }
                    break;
            }
        }
        
        return isValid;
    }
    
    function clearErrors(e) {
        const field = e.target;
        field.classList.remove('error');
        const existingError = field.parentNode.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
    }
    
    function showError(field, message) {
        field.classList.add('error');
        
        const errorElement = document.createElement('span');
        errorElement.className = 'error-message';
        errorElement.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
        
        field.parentNode.appendChild(errorElement);
    }
    
    function getFieldLabel(field) {
        const label = field.parentNode.querySelector('label');
        return label ? label.textContent.replace('*', '').trim() : field.name;
    }
    
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    function isValidPhone(phone) {
        const phoneRegex = /^[\d\s\-\+\(\)]{10,15}$/;
        return phoneRegex.test(phone);
    }
    
    function isValidDate(dateString) {
        const selectedDate = new Date(dateString);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        return selectedDate >= today;
    }
    
    function updateTimeOptions(dayOfWeek) {
        // This would be more complex in a real app
        // For now, we keep all options available
        console.log('Day of week:', dayOfWeek);
    }
    
    // Auto-format phone number
    const phoneInput = document.getElementById('phone');
    phoneInput.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        
        if (value.length >= 10) {
            if (value.startsWith('61')) {
                // Australian international format
                value = value.replace(/(\d{2})(\d{1})(\d{4})(\d{4})/, '+$1 $2 $3 $4');
            } else if (value.startsWith('0')) {
                // Australian domestic format
                value = value.replace(/(\d{2})(\d{4})(\d{4})/, '$1 $2 $3');
            } else {
                // Default format
                value = value.replace(/(\d{4})(\d{3})(\d{3})/, '$1 $2 $3');
            }
        }
        
        this.value = value;
    });
});
</script>

<?php include_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
