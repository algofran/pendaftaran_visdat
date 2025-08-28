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
    $fileUrl = $_POST['fileUrl'] ?? $_POST['fileName'] ?? ''; // Support both new fileUrl and legacy fileName
    $rotation = intval($_POST['rotation'] ?? 0);
    
    if (empty($fileUrl)) {
        throw new Exception('File URL not provided');
    }
    
    // Extract filename from URL
    // Handle both relative URLs (../uploads/filename.jpg) and full URLs (http://domain.com/uploads/filename.jpg)
    $fileName = basename(parse_url($fileUrl, PHP_URL_PATH));
    
    if (empty($fileName)) {
        throw new Exception('Could not extract filename from URL: ' . $fileUrl);
    }
    
    // Validate rotation value
    if (!in_array($rotation, [0, 90, 180, 270])) {
        throw new Exception('Invalid rotation value');
    }
    
    // Construct file path with proper directory separators for cross-platform compatibility
    $filePath = realpath(__DIR__ . '/../uploads/') . DIRECTORY_SEPARATOR . basename($fileName);
    
    // Fallback if realpath fails (directory doesn't exist)
    if ($filePath === false) {
        $filePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . basename($fileName);
    }
    
    // Verify the original file exists and is an image
    if (!file_exists($filePath)) {
        if (DEBUG) {
            error_log("File not found - Path: $filePath, OS: " . PHP_OS . ", DIR: " . __DIR__);
            error_log("Original fileUrl: $fileUrl, Extracted fileName: $fileName");
        }
        throw new Exception('Original file not found: ' . basename($fileName));
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
        if (DEBUG) {
            error_log("Backup failed - Source: $filePath, Backup: $backupPath, OS: " . PHP_OS);
            error_log("Source readable: " . (is_readable($filePath) ? 'yes' : 'no'));
            error_log("Destination writable: " . (is_writable(dirname($backupPath)) ? 'yes' : 'no'));
        }
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
            if (DEBUG) {
                error_log("Image save failed - Path: $filePath, MIME: " . $imageInfo['mime'] . ", OS: " . PHP_OS);
                error_log("Directory writable: " . (is_writable(dirname($filePath)) ? 'yes' : 'no'));
                error_log("File writable: " . (is_writable($filePath) ? 'yes' : 'no'));
            }
            throw new Exception('Failed to save rotated image');
        }
        
        // Set proper file permissions (skip on Windows as it behaves differently)
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            chmod($filePath, 0644);
        } else {
            // On Windows, ensure the file is writable
            if (!is_writable($filePath)) {
                if (DEBUG) {
                    error_log("Warning: File may not be writable on Windows: $filePath");
                }
            }
        }
        
        // Remove backup file on success
        if (file_exists($backupPath)) {
            unlink($backupPath);
        }
        
        // Log the successful rotation
        if (DEBUG) {
            error_log("Image rotated successfully: $fileName (rotation: {$rotation}°) on OS: " . PHP_OS);
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