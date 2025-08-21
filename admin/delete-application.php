<?php
session_start();
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['application_id']) || empty($input['application_id'])) {
        echo json_encode(['success' => false, 'message' => 'Application ID is required']);
        exit;
    }
    
    $applicationId = intval($input['application_id']);
    
    // First, get the application details to delete associated files
    $stmt = $pdo->prepare("SELECT cv_file, photo_file, certificate_file, sim_file FROM applications WHERE id = ?");
    $stmt->execute([$applicationId]);
    $application = $stmt->fetch();
    
    if (!$application) {
        echo json_encode(['success' => false, 'message' => 'Application not found']);
        exit;
    }
    
    // Delete associated files
    $filesToDelete = [
        $application['cv_file'],
        $application['photo_file'],
        $application['certificate_file'],
        $application['sim_file']
    ];
    
    $deletedFiles = [];
    foreach ($filesToDelete as $filename) {
        if (!empty($filename)) {
            $filePath = '../uploads/' . $filename;
            if (file_exists($filePath)) {
                if (unlink($filePath)) {
                    $deletedFiles[] = $filename;
                } else {
                    error_log("Failed to delete file: $filePath");
                }
            }
        }
    }
    
    // Log deletion attempt
    error_log("Deleting application ID: $applicationId, Files deleted: " . implode(', ', $deletedFiles));
    
    // Delete the application from database
    $stmt = $pdo->prepare("DELETE FROM applications WHERE id = ?");
    $result = $stmt->execute([$applicationId]);
    
    if ($result) {
        echo json_encode([
            'success' => true, 
            'message' => 'Application deleted successfully',
            'details' => [
                'application_id' => $applicationId,
                'files_deleted' => $deletedFiles,
                'deleted_at' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to delete application from database',
            'application_id' => $applicationId
        ]);
    }
    
} catch (Exception $e) {
    error_log("Delete application error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Server error occurred while deleting application'
    ]);
}
?>
