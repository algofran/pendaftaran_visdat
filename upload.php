<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// This file is no longer used for FilePond uploads
// FilePond now works directly with the form submission
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed', 'info' => 'This endpoint is deprecated']);
    exit;
}

// Return info message
echo json_encode(['error' => 'This endpoint is deprecated. Use direct form submission instead.']);
exit;

try {
    // Create upload directory if not exists
    createUploadDir();
    
    // Get the uploaded file
    $file = $_FILES['filepond'] ?? null;
    
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'No file uploaded or upload error']);
        exit;
    }
    
    // Validate file size
    if ($file['size'] > MAX_FILE_SIZE) {
        http_response_code(400);
        echo json_encode(['error' => 'File too large (max 5MB)']);
        exit;
    }
    
    // Validate file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        http_response_code(400);
        echo json_encode(['error' => 'File type not allowed']);
        exit;
    }
    
    // Generate unique filename
    $filename = generateFileName($file['name']);
    $uploadPath = UPLOAD_DIR . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // Set proper permissions
        chmod($uploadPath, 0644);
        
        // Return the filename for FilePond
        echo $filename;
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save file']);
    }
    
} catch (Exception $e) {
    error_log("File upload error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
?>