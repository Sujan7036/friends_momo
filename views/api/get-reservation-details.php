<?php
/**
 * Get Reservation Details API
 * Returns detailed reservation information for user interface
 */

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/models/Reservation.php';
require_once dirname(__DIR__, 2) . '/models/User.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

// Start session and check authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

$reservationId = intval($_GET['id'] ?? 0);
$userId = $_SESSION['user_id'];

if (!$reservationId) {
    echo json_encode(['success' => false, 'message' => 'Invalid reservation ID']);
    exit();
}

try {
    $reservationModel = new Reservation();
    $userModel = new User();
    
    // Get reservation details
    $reservation = $reservationModel->find($reservationId);
    
    if (!$reservation || $reservation['user_id'] != $userId) {
        echo json_encode(['success' => false, 'message' => 'Reservation not found']);
        exit();
    }
    
    // Get user details
    $user = $userModel->find($userId);
    
    $reservationDateTime = strtotime($reservation['reservation_date'] . ' ' . $reservation['reservation_time']);
    $canCancel = ($reservationDateTime - time()) >= 7200 && in_array($reservation['status'], ['pending', 'confirmed']);
    $isPast = $reservationDateTime < time();
    $canModify = !$isPast && in_array($reservation['status'], ['pending', 'confirmed']);
    
    // Build HTML content
    ob_start();
    ?>
    <div class="reservation-details">
        <div class="reservation-header-info">
            <div class="reservation-summary">
                <h4>Reservation Details</h4>
                <div class="reservation-status-display">
                    <span class="status-badge <?= $reservation['status'] ?>">
                        <i class="fas fa-circle"></i>
                        <?= ucfirst($reservation['status']) ?>
                    </span>
                </div>
            </div>
            
            <div class="reservation-datetime-display">
                <div class="datetime-item">
                    <i class="fas fa-calendar-alt"></i>
                    <div>
                        <strong><?= date('l, F j, Y', $reservationDateTime) ?></strong>
                        <span><?= date('g:i A', $reservationDateTime) ?></span>
                    </div>
                </div>
                
                <div class="datetime-item">
                    <i class="fas fa-users"></i>
                    <div>
                        <strong><?= $reservation['guests'] ?> Guests</strong>
                        <span>Party size</span>
                    </div>
                </div>
                
                <?php if (!empty($reservation['table_number'])): ?>
                    <div class="datetime-item">
                        <i class="fas fa-chair"></i>
                        <div>
                            <strong>Table <?= $reservation['table_number'] ?></strong>
                            <span>Assigned table</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="reservation-info-section">
            <h5>Contact Information</h5>
            <div class="contact-info">
                <div class="contact-item">
                    <label><i class="fas fa-user"></i> Name:</label>
                    <span><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></span>
                </div>
                
                <div class="contact-item">
                    <label><i class="fas fa-envelope"></i> Email:</label>
                    <span><?= htmlspecialchars($user['email']) ?></span>
                </div>
                
                <?php if (!empty($reservation['phone'])): ?>
                    <div class="contact-item">
                        <label><i class="fas fa-phone"></i> Phone:</label>
                        <span><?= htmlspecialchars($reservation['phone']) ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($reservation['special_requests'])): ?>
            <div class="reservation-info-section">
                <h5>Special Requests</h5>
                <div class="special-requests">
                    <p><?= nl2br(htmlspecialchars($reservation['special_requests'])) ?></p>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="reservation-info-section">
            <h5>Reservation Timeline</h5>
            <div class="timeline">
                <div class="timeline-item completed">
                    <div class="timeline-marker">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="timeline-content">
                        <strong>Reservation Created</strong>
                        <span><?= date('M j, Y g:i A', strtotime($reservation['created_at'])) ?></span>
                    </div>
                </div>
                
                <?php if ($reservation['status'] === 'confirmed' && !empty($reservation['updated_at'])): ?>
                    <div class="timeline-item completed">
                        <div class="timeline-marker">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="timeline-content">
                            <strong>Reservation Confirmed</strong>
                            <span><?= date('M j, Y g:i A', strtotime($reservation['updated_at'])) ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!$isPast && in_array($reservation['status'], ['pending', 'confirmed'])): ?>
                    <div class="timeline-item upcoming">
                        <div class="timeline-marker">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="timeline-content">
                            <strong>Scheduled Visit</strong>
                            <span><?= date('M j, Y g:i A', $reservationDateTime) ?></span>
                        </div>
                    </div>
                <?php elseif ($reservation['status'] === 'completed'): ?>
                    <div class="timeline-item completed">
                        <div class="timeline-marker">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <div class="timeline-content">
                            <strong>Visit Completed</strong>
                            <span><?= date('M j, Y g:i A', $reservationDateTime) ?></span>
                        </div>
                    </div>
                <?php elseif ($reservation['status'] === 'cancelled'): ?>
                    <div class="timeline-item cancelled">
                        <div class="timeline-marker">
                            <i class="fas fa-times"></i>
                        </div>
                        <div class="timeline-content">
                            <strong>Reservation Cancelled</strong>
                            <span><?= !empty($reservation['updated_at']) ? date('M j, Y g:i A', strtotime($reservation['updated_at'])) : 'Date unknown' ?></span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($canCancel): ?>
            <div class="cancellation-policy">
                <h5><i class="fas fa-info-circle"></i> Cancellation Policy</h5>
                <p>Reservations can be cancelled up to 2 hours before the scheduled time.</p>
                <p><strong>Cancellation deadline:</strong> <?= date('M j, Y g:i A', $reservationDateTime - 7200) ?></p>
            </div>
        <?php endif; ?>
        
        <div class="reservation-actions-modal">
            <?php if ($canModify): ?>
                <button type="button" class="btn btn-primary" onclick="modifyReservation(<?= $reservation['id'] ?>)">
                    <i class="fas fa-edit"></i> Modify Reservation
                </button>
            <?php endif; ?>
            
            <?php if ($canCancel): ?>
                <button type="button" class="btn btn-danger" onclick="cancelReservationFromModal(<?= $reservation['id'] ?>)">
                    <i class="fas fa-times"></i> Cancel Reservation
                </button>
            <?php endif; ?>
            
            <?php if ($reservation['status'] === 'completed'): ?>
                <button type="button" class="btn btn-secondary" onclick="makeNewReservation(<?= $reservation['guests'] ?>)">
                    <i class="fas fa-redo"></i> Book Again
                </button>
            <?php endif; ?>
        </div>
    </div>
    
    <style>
    .reservation-details {
        max-height: 70vh;
        overflow-y: auto;
    }
    
    .reservation-header-info {
        margin-bottom: 2rem;
    }
    
    .reservation-summary {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    
    .reservation-summary h4 {
        color: #2c3e50;
        margin: 0;
    }
    
    .reservation-status-display .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .reservation-datetime-display {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }
    
    .datetime-item {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .datetime-item i {
        font-size: 1.5rem;
        color: #007bff;
        width: 24px;
        text-align: center;
    }
    
    .datetime-item div strong {
        display: block;
        color: #2c3e50;
        font-size: 1.1rem;
        margin-bottom: 0.25rem;
    }
    
    .datetime-item div span {
        color: #6c757d;
        font-size: 0.9rem;
    }
    
    .reservation-info-section {
        margin-bottom: 2rem;
    }
    
    .reservation-info-section h5 {
        color: #2c3e50;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e9ecef;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .contact-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
    }
    
    .contact-item {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .contact-item label {
        font-weight: 500;
        color: #6c757d;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .contact-item span {
        color: #2c3e50;
        font-weight: 500;
        padding-left: 1.25rem;
    }
    
    .special-requests {
        padding: 1rem;
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        border-radius: 8px;
        color: #856404;
    }
    
    .special-requests p {
        margin: 0;
        line-height: 1.6;
    }
    
    .timeline {
        position: relative;
        padding-left: 2rem;
    }
    
    .timeline::before {
        content: '';
        position: absolute;
        left: 12px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 2rem;
        padding-left: 2rem;
    }
    
    .timeline-marker {
        position: absolute;
        left: -2rem;
        top: 0;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        color: white;
        z-index: 1;
    }
    
    .timeline-item.completed .timeline-marker {
        background: #28a745;
    }
    
    .timeline-item.upcoming .timeline-marker {
        background: #007bff;
    }
    
    .timeline-item.cancelled .timeline-marker {
        background: #dc3545;
    }
    
    .timeline-content strong {
        display: block;
        color: #2c3e50;
        margin-bottom: 0.25rem;
    }
    
    .timeline-content span {
        color: #6c757d;
        font-size: 0.9rem;
    }
    
    .cancellation-policy {
        background: #e7f3ff;
        border: 1px solid #b8daff;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .cancellation-policy h5 {
        color: #004085;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .cancellation-policy p {
        color: #004085;
        margin-bottom: 0.5rem;
    }
    
    .reservation-actions-modal {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 2px solid #e9ecef;
    }
    
    /* Status badge colors */
    .status-badge.pending {
        background-color: #fff3cd;
        color: #856404;
    }
    
    .status-badge.confirmed {
        background-color: #cce7ff;
        color: #004085;
    }
    
    .status-badge.completed {
        background-color: #d4edda;
        color: #155724;
    }
    
    .status-badge.cancelled {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    .status-badge.no_show {
        background-color: #f0f0f0;
        color: #5a5a5a;
    }
    
    @media (max-width: 768px) {
        .reservation-summary {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }
        
        .reservation-datetime-display {
            grid-template-columns: 1fr;
        }
        
        .datetime-item {
            justify-content: center;
        }
        
        .contact-info {
            grid-template-columns: 1fr;
        }
        
        .timeline {
            padding-left: 1.5rem;
        }
        
        .timeline-item {
            padding-left: 1.5rem;
        }
        
        .timeline-marker {
            left: -1.5rem;
        }
        
        .reservation-actions-modal {
            flex-direction: column;
        }
    }
    </style>
    <?php
    
    $html = ob_get_clean();
    
    echo json_encode([
        'success' => true,
        'html' => $html,
        'reservation' => $reservation,
        'can_cancel' => $canCancel,
        'can_modify' => $canModify
    ]);
    
} catch (Exception $e) {
    error_log("Error in get-reservation-details.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while loading reservation details']);
}
