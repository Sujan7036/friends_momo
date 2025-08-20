<?php
/**
 * User Reservations Page
 * Friends and Momos Restaurant Management System
 */

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/models/Reservation.php';
require_once dirname(__DIR__, 2) . '/models/User.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

// Start session and check user access
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/login.php');
    exit();
}

$reservationModel = new Reservation();
$userModel = new User();
$userId = $_SESSION['user_id'];

// Get filter parameters
$status = $_GET['status'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// Build filter conditions
$filters = [];
if ($status) {
    $filters['status'] = $status;
}
if ($dateFrom) {
    $filters['date_from'] = $dateFrom;
}
if ($dateTo) {
    $filters['date_to'] = $dateTo;
}

// Get reservations with pagination
$reservations = $reservationModel->getUserReservationsWithPagination($userId, $filters, $limit, $offset);
$totalReservations = $reservationModel->getUserReservationCount($userId, $filters);
$totalPages = ceil($totalReservations / $limit);

// Handle reservation actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $reservationId = intval($_POST['reservation_id'] ?? 0);
    
    if ($action === 'cancel_reservation' && $reservationId) {
        $reservation = $reservationModel->find($reservationId);
        
        if ($reservation && $reservation['user_id'] == $userId) {
            $reservationDate = strtotime($reservation['reservation_date'] . ' ' . $reservation['reservation_time']);
            $currentTime = time();
            $timeDiff = $reservationDate - $currentTime;
            
            // Can only cancel if reservation is at least 2 hours away
            if ($timeDiff >= 7200 && in_array($reservation['status'], ['pending', 'confirmed'])) {
                if ($reservationModel->updateStatus($reservationId, 'cancelled')) {
                    $success = "Reservation for " . date('M j, Y g:i A', $reservationDate) . " has been cancelled.";
                } else {
                    $error = "Failed to cancel reservation. Please try again.";
                }
            } else {
                $error = "Reservations can only be cancelled at least 2 hours before the scheduled time.";
            }
        } else {
            $error = "Reservation not found or access denied.";
        }
        
        // Refresh reservations after action
        $reservations = $reservationModel->getUserReservationsWithPagination($userId, $filters, $limit, $offset);
    }
}

// Get reservation statistics
$reservationStats = $reservationModel->getUserReservationStats($userId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reservations - Friends & Momos</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= ASSETS_URL ?>/css/user.css" rel="stylesheet">
</head>
<body>
    <div class="user-container">
        <!-- Navigation -->
        <nav class="user-nav">
            <div class="nav-brand">
                <img src="<?= ASSETS_URL ?>/images/logo.png" alt="Friends & Momos">
                <span>Friends & Momos</span>
            </div>
            
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="../../index.php" class="nav-link">
                    <i class="fas fa-home"></i> Home
                </a>
                <a href="../public/reservation.php" class="nav-link">
                    <i class="fas fa-plus"></i> New Reservation
                </a>
                <a href="../../logout.php" class="nav-link logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </nav>
        
        <!-- Main Content -->
        <div class="reservations-main">
            <!-- Header -->
            <header class="page-header">
                <div class="header-content">
                    <h1><i class="fas fa-calendar-alt"></i> My Reservations</h1>
                    <p>Manage your table reservations</p>
                </div>
                
                <div class="header-actions">
                    <a href="../public/reservation.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Reservation
                    </a>
                </div>
            </header>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <!-- Reservation Statistics -->
            <div class="reservation-stats">
                <div class="stat-card">
                    <div class="stat-icon total">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $reservationStats['total_reservations'] ?? 0 ?></h3>
                        <p>Total Reservations</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon upcoming">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $reservationStats['upcoming_reservations'] ?? 0 ?></h3>
                        <p>Upcoming</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon completed">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $reservationStats['completed_reservations'] ?? 0 ?></h3>
                        <p>Completed</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon guests">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $reservationStats['average_guests'] ?? 0 ?></h3>
                        <p>Avg. Guests</p>
                    </div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="filters-section">
                <form method="GET" class="filters-form">
                    <div class="filter-group">
                        <label for="status">Status:</label>
                        <select name="status" id="status">
                            <option value="">All Status</option>
                            <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="confirmed" <?= $status === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                            <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            <option value="no_show" <?= $status === 'no_show' ? 'selected' : '' ?>>No Show</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="date_from">From Date:</label>
                        <input type="date" name="date_from" id="date_from" value="<?= htmlspecialchars($dateFrom) ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label for="date_to">To Date:</label>
                        <input type="date" name="date_to" id="date_to" value="<?= htmlspecialchars($dateTo) ?>">
                    </div>
                    
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="reservations.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Reservations List -->
            <div class="reservations-section">
                <?php if (!empty($reservations)): ?>
                    <div class="reservations-grid">
                        <?php foreach ($reservations as $reservation): ?>
                            <?php
                            $reservationDateTime = strtotime($reservation['reservation_date'] . ' ' . $reservation['reservation_time']);
                            $canCancel = ($reservationDateTime - time()) >= 7200 && in_array($reservation['status'], ['pending', 'confirmed']);
                            $isPast = $reservationDateTime < time();
                            ?>
                            <div class="reservation-card <?= $isPast ? 'past-reservation' : '' ?>">
                                <div class="reservation-header">
                                    <div class="reservation-date-time">
                                        <div class="date">
                                            <i class="fas fa-calendar"></i>
                                            <?= date('M j, Y', $reservationDateTime) ?>
                                        </div>
                                        <div class="time">
                                            <i class="fas fa-clock"></i>
                                            <?= date('g:i A', $reservationDateTime) ?>
                                        </div>
                                    </div>
                                    
                                    <div class="reservation-status">
                                        <span class="status-badge <?= $reservation['status'] ?>">
                                            <i class="fas fa-circle"></i>
                                            <?= ucfirst($reservation['status']) ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="reservation-details">
                                    <div class="detail-row">
                                        <div class="detail-item">
                                            <span class="label">Guests:</span>
                                            <span class="value">
                                                <i class="fas fa-users"></i>
                                                <?= $reservation['guests'] ?>
                                            </span>
                                        </div>
                                        
                                        <?php if (!empty($reservation['table_number'])): ?>
                                            <div class="detail-item">
                                                <span class="label">Table:</span>
                                                <span class="value">
                                                    <i class="fas fa-chair"></i>
                                                    Table <?= $reservation['table_number'] ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (!empty($reservation['special_requests'])): ?>
                                        <div class="detail-row">
                                            <div class="detail-item full-width">
                                                <span class="label">Special Requests:</span>
                                                <span class="value"><?= htmlspecialchars($reservation['special_requests']) ?></span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="detail-row">
                                        <div class="detail-item">
                                            <span class="label">Created:</span>
                                            <span class="value"><?= date('M j, Y g:i A', strtotime($reservation['created_at'])) ?></span>
                                        </div>
                                        
                                        <?php if ($reservation['status'] === 'confirmed' && !empty($reservation['updated_at'])): ?>
                                            <div class="detail-item">
                                                <span class="label">Confirmed:</span>
                                                <span class="value"><?= date('M j, Y g:i A', strtotime($reservation['updated_at'])) ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="reservation-actions">
                                    <button class="btn btn-small btn-outline" onclick="viewReservationDetails(<?= $reservation['id'] ?>)">
                                        <i class="fas fa-eye"></i> View Details
                                    </button>
                                    
                                    <?php if ($canCancel): ?>
                                        <button class="btn btn-small btn-danger" onclick="cancelReservation(<?= $reservation['id'] ?>)">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($reservation['status'] === 'completed'): ?>
                                        <button class="btn btn-small btn-primary" onclick="makeNewReservation(<?= $reservation['guests'] ?>)">
                                            <i class="fas fa-redo"></i> Book Again
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($reservation['status'] === 'confirmed' && !$isPast): ?>
                                        <button class="btn btn-small btn-secondary" onclick="modifyReservation(<?= $reservation['id'] ?>)">
                                            <i class="fas fa-edit"></i> Modify
                                        </button>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($canCancel): ?>
                                    <div class="cancellation-notice">
                                        <i class="fas fa-info-circle"></i>
                                        Can be cancelled until <?= date('M j, Y g:i A', $reservationDateTime - 7200) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?= $page - 1 ?>&status=<?= urlencode($status) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>" 
                                   class="pagination-link">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <a href="?page=<?= $i ?>&status=<?= urlencode($status) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>" 
                                   class="pagination-link <?= $i === $page ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?= $page + 1 ?>&status=<?= urlencode($status) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>" 
                                   class="pagination-link">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="no-reservations">
                        <div class="no-reservations-icon">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <h3>No reservations found</h3>
                        <p>You haven't made any reservations yet or no reservations match your filters.</p>
                        <div class="no-reservations-actions">
                            <a href="../public/reservation.php" class="btn btn-primary">
                                <i class="fas fa-calendar-plus"></i> Make Reservation
                            </a>
                            <?php if ($status || $dateFrom || $dateTo): ?>
                                <a href="reservations.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear Filters
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Reservation Details Modal -->
    <div id="reservationDetailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Reservation Details</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body" id="reservationDetailsContent">
                <!-- Reservation details will be loaded here -->
            </div>
        </div>
    </div>
    
    <!-- Cancel Reservation Modal -->
    <div id="cancelReservationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Cancel Reservation</h3>
                <span class="close" onclick="closeCancelModal()">&times;</span>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this reservation?</p>
                <p class="warning-text">
                    <i class="fas fa-exclamation-triangle"></i>
                    This action cannot be undone.
                </p>
                <form method="POST" id="cancelReservationForm">
                    <input type="hidden" name="action" value="cancel_reservation">
                    <input type="hidden" name="reservation_id" id="cancelReservationId">
                    
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeCancelModal()">
                            <i class="fas fa-times"></i> No, Keep Reservation
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-check"></i> Yes, Cancel Reservation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // View reservation details
        function viewReservationDetails(reservationId) {
            fetch(`../api/get-reservation-details.php?id=${reservationId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('reservationDetailsContent').innerHTML = data.html;
                        document.getElementById('reservationDetailsModal').style.display = 'block';
                    } else {
                        alert('Failed to load reservation details');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load reservation details');
                });
        }
        
        // Cancel reservation
        function cancelReservation(reservationId) {
            document.getElementById('cancelReservationId').value = reservationId;
            document.getElementById('cancelReservationModal').style.display = 'block';
        }
        
        // Make new reservation with same guest count
        function makeNewReservation(guests) {
            window.location.href = `../public/reservation.php?guests=${guests}`;
        }
        
        // Modify reservation
        function modifyReservation(reservationId) {
            window.location.href = `modify-reservation.php?id=${reservationId}`;
        }
        
        // Modal functions
        function closeModal() {
            document.getElementById('reservationDetailsModal').style.display = 'none';
        }
        
        function closeCancelModal() {
            document.getElementById('cancelReservationModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const detailsModal = document.getElementById('reservationDetailsModal');
            const cancelModal = document.getElementById('cancelReservationModal');
            
            if (event.target === detailsModal) {
                detailsModal.style.display = 'none';
            }
            if (event.target === cancelModal) {
                cancelModal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
