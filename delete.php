<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// This file handles FilePond file deletions
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Get the filename from the request body
    $filename = file_get_contents('php://input');
    
    if (empty($filename)) {
        http_response_code(400);
        echo json_encode(['error' => 'No filename provided']);
        exit;
    }
    
    // Sanitize filename to prevent directory traversal
    $filename = basename($filename);
    $filePath = UPLOAD_DIR . $filename;
    
    // Check if file exists and delete it
    if (file_exists($filePath)) {
        if (unlink($filePath)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete file']);
        }
    } else {
        // File doesn't exist, but that's okay for FilePond
        echo json_encode(['success' => true]);
    }
    
} catch (Exception $e) {
    error_log("File deletion error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
?>