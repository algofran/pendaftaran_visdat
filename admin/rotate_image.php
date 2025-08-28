<?php
require_once '../config.php';

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false, 
        'message' => 'Method not allowed - use POST'
    ]);
    exit();
}

try {
    // Check if image file was uploaded
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No image file received or upload error');
    }
    
    // Get parameters
    $fileName = $_POST['fileName'] ?? '';
    $rotation = intval($_POST['rotation'] ?? 0);
    
    if (empty($fileName)) {
        throw new Exception('File name not provided');
    }
    
    // Validate rotation value
    if (!in_array($rotation, [0, 90, 180, 270])) {
        throw new Exception('Invalid rotation value');
    }
    
    // Construct file path
    $filePath = '../uploads/' . basename($fileName);
    
    // Verify the original file exists and is an image
    if (!file_exists($filePath)) {
        throw new Exception('Original file not found');
    }
    
    $imageInfo = getimagesize($filePath);
    if ($imageInfo === false) {
        throw new Exception('File is not a valid image');
    }
    
    // Get the uploaded rotated image data
    $tempImagePath = $_FILES['image']['tmp_name'];
    
    // Validate the uploaded rotated image
    $rotatedImageInfo = getimagesize($tempImagePath);
    if ($rotatedImageInfo === false) {
        throw new Exception('Rotated image is not valid');
    }
    
    // Create a backup of the original file
    $backupPath = $filePath . '.backup.' . time();
    if (!copy($filePath, $backupPath)) {
        throw new Exception('Failed to create backup of original file');
    }
    
    try {
        // Create image resource from the rotated image
        $rotatedImage = null;
        switch ($rotatedImageInfo['mime']) {
            case 'image/jpeg':
                $rotatedImage = imagecreatefromjpeg($tempImagePath);
                break;
            case 'image/png':
                $rotatedImage = imagecreatefrompng($tempImagePath);
                break;
            case 'image/gif':
                $rotatedImage = imagecreatefromgif($tempImagePath);
                break;
            default:
                throw new Exception('Unsupported image type: ' . $rotatedImageInfo['mime']);
        }
        
        if ($rotatedImage === false) {
            throw new Exception('Failed to create image resource from rotated image');
        }
        
        // Save the rotated image, preserving the original format
        $success = false;
        switch ($imageInfo['mime']) {
            case 'image/jpeg':
                $success = imagejpeg($rotatedImage, $filePath, 90);
                break;
            case 'image/png':
                // Preserve transparency for PNG
                imagealphablending($rotatedImage, false);
                imagesavealpha($rotatedImage, true);
                $success = imagepng($rotatedImage, $filePath, 6);
                break;
            case 'image/gif':
                $success = imagegif($rotatedImage, $filePath);
                break;
        }
        
        // Clean up memory
        imagedestroy($rotatedImage);
        
        if (!$success) {
            throw new Exception('Failed to save rotated image');
        }
        
        // Set proper file permissions
        chmod($filePath, 0644);
        
        // Remove backup file on success
        if (file_exists($backupPath)) {
            unlink($backupPath);
        }
        
        // Log the successful rotation
        if (DEBUG) {
            error_log("Image rotated successfully: $fileName (rotation: {$rotation}°)");
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Image rotation saved successfully',
            'fileName' => $fileName,
            'rotation' => $rotation
        ]);
        
    } catch (Exception $e) {
        // Restore backup if something went wrong
        if (file_exists($backupPath)) {
            copy($backupPath, $filePath);
            unlink($backupPath);
        }
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Image rotation error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>