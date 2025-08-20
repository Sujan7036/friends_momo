<?php
/**
 * Reservation Model
 * Friends and Momos Restaurant Management System
 */

class Reservation extends BaseModel {
    protected $table = 'reservations';
    protected $fillable = [
        'user_id', 'table_id', 'customer_name', 'customer_email', 'customer_phone', 
        'party_size', 'reservation_date', 'reservation_time', 'status',
        'special_requests', 'notes', 'confirmation_sent', 'reminder_sent'
    ];
    
    /**
     * Create a new reservation
     */
    public function createReservation($data) {
        try {
            // Check availability first (always returns true - slot checking disabled)
            if (!$this->checkAvailability($data['reservation_date'], $data['reservation_time'], $data['party_size'])) {
                return ['success' => false, 'message' => 'No tables available for the selected time'];
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO reservations (
                    user_id, customer_name, customer_email, customer_phone,
                    reservation_date, reservation_time, party_size,
                    special_requests, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
            ");
            
            $success = $stmt->execute([
                $data['user_id'] ?? null,
                $data['customer_name'],
                $data['customer_email'],
                $data['customer_phone'],
                $data['reservation_date'],
                $data['reservation_time'],
                $data['party_size'],
                $data['special_requests'] ?? null
            ]);
            
            if ($success) {
                $reservationId = $this->db->lastInsertId();
                return ['success' => true, 'reservation_id' => $reservationId];
            } else {
                return ['success' => false, 'message' => 'Failed to create reservation'];
            }
            
        } catch (Exception $e) {
            error_log("Reservation creation failed: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while creating the reservation'];
        }
    }
    
    /**
     * Check table availability - DISABLED
     * Always returns true to allow all reservations
     */
    public function checkAvailability($date, $time, $partySize) {
        // Slot checking has been disabled - always return true
        return true;
    }

    /**
     * Get available time slots for a date
     */
    public function getAvailableTimeSlots($date, $partySize) {
        $timeSlots = [];
        $openTime = new DateTime($date . ' 10:00:00'); // Restaurant opens at 10 AM
        $closeTime = new DateTime($date . ' 22:00:00'); // Last reservation at 10 PM
        $interval = new DateInterval('PT30M'); // 30-minute intervals
        
        $currentTime = clone $openTime;
        
        while ($currentTime < $closeTime) {
            $timeString = $currentTime->format('H:i:s');
            
            if ($this->checkAvailability($date, $timeString, $partySize)) {
                $timeSlots[] = [
                    'time' => $timeString,
                    'formatted' => $currentTime->format('g:i A'),
                    'available' => true
                ];
            } else {
                $timeSlots[] = [
                    'time' => $timeString,
                    'formatted' => $currentTime->format('g:i A'),
                    'available' => false
                ];
            }
            
            $currentTime->add($interval);
        }
        
        return $timeSlots;
    }
    
    /**
     * Get user's upcoming reservations
     */
    public function getUserUpcomingReservations($userId) {
        $stmt = $this->db->prepare("
            SELECT *
            FROM reservations
            WHERE user_id = ?
            AND CONCAT(reservation_date, ' ', reservation_time) >= NOW()
            AND status IN ('pending', 'confirmed')
            ORDER BY reservation_date ASC, reservation_time ASC
        ");
        
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get user's recent reservations
     */
    public function getUserRecentReservations($userId, $limit = 10) {
        $stmt = $this->db->prepare("
            SELECT *
            FROM reservations
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ?
        ");
        
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get reservation by ID
     */
    public function getReservationById($id, $userId = null) {
        $whereClause = $userId ? "WHERE id = ? AND user_id = ?" : "WHERE id = ?";
        $params = $userId ? [$id, $userId] : [$id];
        
        $stmt = $this->db->prepare("
            SELECT *
            FROM reservations
            $whereClause
        ");
        
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Update reservation status
     */
    public function updateReservationStatus($id, $status, $notes = null) {
        try {
            $stmt = $this->db->prepare("
                UPDATE reservations
                SET status = ?,
                    admin_notes = CASE 
                        WHEN ? IS NOT NULL THEN CONCAT(COALESCE(admin_notes, ''), '\n', ?)
                        ELSE admin_notes 
                    END,
                    updated_at = NOW()
                WHERE id = ?
            ");
            
            return $stmt->execute([$status, $notes, $notes, $id]);
            
        } catch (Exception $e) {
            error_log("Reservation status update failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cancel reservation
     */
    public function cancelReservation($id, $userId = null, $reason = null) {
        try {
            $whereClause = $userId ? "WHERE id = ? AND user_id = ?" : "WHERE id = ?";
            $params = $userId ? [$id, $userId] : [$id];
            
            // Check if reservation exists and can be cancelled
            $stmt = $this->db->prepare("
                SELECT status, CONCAT(reservation_date, ' ', reservation_time) as reservation_datetime
                FROM reservations
                $whereClause
            ");
            $stmt->execute($params);
            $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$reservation) {
                return ['success' => false, 'message' => 'Reservation not found'];
            }
            
            if ($reservation['status'] === 'cancelled') {
                return ['success' => false, 'message' => 'Reservation is already cancelled'];
            }
            
            if ($reservation['status'] === 'completed') {
                return ['success' => false, 'message' => 'Cannot cancel completed reservation'];
            }
            
            // Check if reservation is in the past
            if (strtotime($reservation['reservation_datetime']) < time()) {
                return ['success' => false, 'message' => 'Cannot cancel past reservations'];
            }
            
            // Cancel the reservation
            $updateStmt = $this->db->prepare("
                UPDATE reservations
                SET status = 'cancelled',
                    cancellation_reason = ?,
                    cancelled_at = NOW()
                $whereClause
            ");
            
            $updateParams = [$reason];
            if ($userId) {
                $updateParams[] = $id;
                $updateParams[] = $userId;
            } else {
                $updateParams[] = $id;
            }
            
            $success = $updateStmt->execute($updateParams);
            
            return [
                'success' => $success,
                'message' => $success ? 'Reservation cancelled successfully' : 'Failed to cancel reservation'
            ];
            
        } catch (Exception $e) {
            error_log("Reservation cancellation failed: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while cancelling the reservation'];
        }
    }
    
    /**
     * Get all reservations with pagination and filters
     */
    public function getAllReservations($page = 1, $limit = 20, $filters = []) {
        $offset = ($page - 1) * $limit;
        $whereConditions = [];
        $params = [];
        
        // Build where conditions
        if (!empty($filters['status'])) {
            $whereConditions[] = "status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereConditions[] = "reservation_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereConditions[] = "reservation_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $whereConditions[] = "(customer_name LIKE ? OR customer_email LIKE ? OR customer_phone LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereClause = empty($whereConditions) ? '' : 'WHERE ' . implode(' AND ', $whereConditions);
        
        // Get total count
        $countStmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM reservations
            $whereClause
        ");
        $countStmt->execute($params);
        $totalCount = $countStmt->fetchColumn();
        
        // Get reservations
        $stmt = $this->db->prepare("
            SELECT *
            FROM reservations
            $whereClause
            ORDER BY reservation_date DESC, reservation_time DESC
            LIMIT ? OFFSET ?
        ");
        
        $params[] = $limit;
        $params[] = $offset;
        $stmt->execute($params);
        
        return [
            'reservations' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $totalCount,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($totalCount / $limit)
        ];
    }
    
    /**
     * Get reservation statistics
     */
    public function getReservationStatistics($dateFrom = null, $dateTo = null) {
        $whereClause = '';
        $params = [];
        
        if ($dateFrom && $dateTo) {
            $whereClause = "WHERE reservation_date BETWEEN ? AND ?";
            $params = [$dateFrom, $dateTo];
        } elseif ($dateFrom) {
            $whereClause = "WHERE reservation_date >= ?";
            $params = [$dateFrom];
        } elseif ($dateTo) {
            $whereClause = "WHERE reservation_date <= ?";
            $params = [$dateTo];
        }
        
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_reservations,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_reservations,
                COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed_reservations,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_reservations,
                COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_reservations,
                COALESCE(SUM(party_size), 0) as total_guests,
                COALESCE(AVG(party_size), 0) as average_party_size
            FROM reservations
            $whereClause
        ");
        
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get daily reservations
     */
    public function getDailyReservations($days = 30) {
        $stmt = $this->db->prepare("
            SELECT 
                reservation_date as date,
                COUNT(*) as reservation_count,
                SUM(party_size) as guest_count
            FROM reservations
            WHERE reservation_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            AND status IN ('confirmed', 'completed')
            GROUP BY reservation_date
            ORDER BY reservation_date DESC
        ");
        
        $stmt->execute([$days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get today's reservations
     */
    public function getTodaysReservations() {
        $stmt = $this->db->prepare("
            SELECT *
            FROM reservations
            WHERE reservation_date = CURDATE()
            AND status IN ('pending', 'confirmed')
            ORDER BY reservation_time ASC
        ");
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get reservations for a specific date
     */
    public function getReservationsByDate($date) {
        $stmt = $this->db->prepare("
            SELECT *
            FROM reservations
            WHERE reservation_date = ?
            ORDER BY reservation_time ASC
        ");
        
        $stmt->execute([$date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Update reservation details
     */
    public function updateReservation($id, $data, $userId = null) {
        try {
            $updateFields = [];
            $params = [];
            
            // Build update fields
            if (isset($data['customer_name'])) {
                $updateFields[] = "customer_name = ?";
                $params[] = $data['customer_name'];
            }
            
            if (isset($data['customer_phone'])) {
                $updateFields[] = "customer_phone = ?";
                $params[] = $data['customer_phone'];
            }
            
            if (isset($data['party_size'])) {
                $updateFields[] = "party_size = ?";
                $params[] = $data['party_size'];
            }
            
            if (isset($data['special_requests'])) {
                $updateFields[] = "special_requests = ?";
                $params[] = $data['special_requests'];
            }
            
            if (isset($data['reservation_date']) && isset($data['reservation_time'])) {
                // Check availability for new date/time
                if (!$this->checkAvailability($data['reservation_date'], $data['reservation_time'], $data['party_size'] ?? 1)) {
                    return ['success' => false, 'message' => 'No tables available for the selected time'];
                }
                
                $updateFields[] = "reservation_date = ?";
                $updateFields[] = "reservation_time = ?";
                $params[] = $data['reservation_date'];
                $params[] = $data['reservation_time'];
            }
            
            if (empty($updateFields)) {
                return ['success' => false, 'message' => 'No fields to update'];
            }
            
            $updateFields[] = "updated_at = NOW()";
            
            $whereClause = $userId ? "WHERE id = ? AND user_id = ?" : "WHERE id = ?";
            $params[] = $id;
            if ($userId) {
                $params[] = $userId;
            }
            
            $sql = "UPDATE reservations SET " . implode(', ', $updateFields) . " " . $whereClause;
            $stmt = $this->db->prepare($sql);
            
            $success = $stmt->execute($params);
            
            return [
                'success' => $success,
                'message' => $success ? 'Reservation updated successfully' : 'Failed to update reservation'
            ];
            
        } catch (Exception $e) {
            error_log("Reservation update failed: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while updating the reservation'];
        }
    }
    
    /**
     * Get peak hours analysis
     */
    public function getPeakHoursAnalysis($days = 30) {
        $stmt = $this->db->prepare("
            SELECT 
                HOUR(reservation_time) as hour,
                COUNT(*) as reservation_count,
                AVG(party_size) as avg_guests
            FROM reservations
            WHERE reservation_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            AND status IN ('confirmed', 'completed')
            GROUP BY HOUR(reservation_time)
            ORDER BY hour ASC
        ");
        
        $stmt->execute([$days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get reservations with customer information
     */
    public function getReservationsWithCustomers($status = '', $dateFrom = '', $dateTo = '') {
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            
            $sql = "SELECT r.*, 
                          COALESCE(CONCAT(u.first_name, ' ', u.last_name), r.customer_name) as customer_name,
                          COALESCE(u.email, r.customer_email) as customer_email,
                          COALESCE(u.phone, r.customer_phone) as customer_phone
                   FROM reservations r
                   LEFT JOIN users u ON r.user_id = u.id
                   WHERE 1=1";
            
            $params = [];
            
            if (!empty($status)) {
                $sql .= " AND r.status = ?";
                $params[] = $status;
            }
            
            if (!empty($dateFrom)) {
                $sql .= " AND r.reservation_date >= ?";
                $params[] = $dateFrom;
            }
            
            if (!empty($dateTo)) {
                $sql .= " AND r.reservation_date <= ?";
                $params[] = $dateTo;
            }
            
            $sql .= " ORDER BY r.reservation_date DESC, r.reservation_time DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching reservations: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get reservation statistics
     */
    public function getReservationStats() {
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            
            $sql = "SELECT 
                       COUNT(*) as total,
                       SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                       SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                       SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                       SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                       SUM(CASE WHEN reservation_date = CURDATE() THEN 1 ELSE 0 END) as today,
                       AVG(party_size) as avg_party_size
                   FROM reservations";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching reservation stats: " . $e->getMessage());
            return [
                'total' => 0,
                'pending' => 0,
                'confirmed' => 0,
                'completed' => 0,
                'cancelled' => 0,
                'today' => 0,
                'avg_party_size' => 0
            ];
        }
    }
    
    /**
     * Get all reservations created today
     */
    public function getTodayReservations() {
        $db = Database::getInstance();
        $pdo = $db->getConnection();
        $sql = "SELECT * FROM reservations WHERE DATE(reservation_date) = CURDATE() ORDER BY reservation_time ASC";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    }
}
?>
