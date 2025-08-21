<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $applicationId = $_POST['application_id'] ?? 0;
    $newStatus = $_POST['new_status'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    // Validate inputs
    if (empty($applicationId) || empty($newStatus)) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }
    
    $validStatuses = ['Pending', 'Review', 'Interview', 'Accepted', 'Rejected'];
    if (!in_array($newStatus, $validStatuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit;
    }
    
    // Update application status
    $sql = "UPDATE applications SET application_status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$newStatus, $applicationId]);
    
    if ($result) {
        // Log the status change (optional)
        if (!empty($notes)) {
            $logSql = "INSERT INTO status_logs (application_id, old_status, new_status, notes, admin_user, created_at) 
                      SELECT ?, application_status, ?, ?, ?, CURRENT_TIMESTAMP 
                      FROM applications WHERE id = ?";
            // This would require creating a status_logs table
        }
        
        echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update status']);
    }
    
} catch (Exception $e) {
    error_log("Status update error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>