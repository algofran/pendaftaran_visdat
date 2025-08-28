<?php
/**
 * Windows Compatibility Test for Image Rotation
 * This script helps diagnose issues with image rotation on Windows systems
 */

require_once '../config.php';

// Set headers
header('Content-Type: text/plain');

echo "=== Windows Image Rotation Compatibility Test ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Operating System: " . PHP_OS . "\n";
echo "Directory Separator: '" . DIRECTORY_SEPARATOR . "'\n";
echo "Current Directory: " . __DIR__ . "\n";
echo "Parent Directory: " . dirname(__DIR__) . "\n";
echo "\n";

// Test upload directory path resolution
echo "=== Upload Directory Tests ===\n";
$uploadDir1 = '../uploads/';
$uploadDir2 = realpath(__DIR__ . '/../uploads/');
$uploadDir3 = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads';

echo "Method 1 (../uploads/): $uploadDir1\n";
echo "Method 2 (realpath): $uploadDir2\n";
echo "Method 3 (DIRECTORY_SEPARATOR): $uploadDir3\n";

echo "Method 1 exists: " . (is_dir($uploadDir1) ? 'YES' : 'NO') . "\n";
echo "Method 2 exists: " . (is_dir($uploadDir2) ? 'YES' : 'NO') . "\n";
echo "Method 3 exists: " . (is_dir($uploadDir3) ? 'YES' : 'NO') . "\n";

echo "Method 1 writable: " . (is_writable($uploadDir1) ? 'YES' : 'NO') . "\n";
echo "Method 2 writable: " . (is_writable($uploadDir2) ? 'YES' : 'NO') . "\n";
echo "Method 3 writable: " . (is_writable($uploadDir3) ? 'YES' : 'NO') . "\n";
echo "\n";

// Test file operations
echo "=== File Operations Test ===\n";
$testFile = 'test_rotation_' . time() . '.txt';
$testPath1 = $uploadDir1 . $testFile;
$testPath2 = ($uploadDir2 ? $uploadDir2 . DIRECTORY_SEPARATOR . $testFile : 'N/A');
$testPath3 = $uploadDir3 . DIRECTORY_SEPARATOR . $testFile;

echo "Test file name: $testFile\n";
echo "Test path 1: $testPath1\n";
echo "Test path 2: $testPath2\n";
echo "Test path 3: $testPath3\n";

// Try to create test files
$testData = "Test data for Windows compatibility\nGenerated: " . date('Y-m-d H:i:s');

echo "\nTrying to create test files:\n";

// Test method 1
if (file_put_contents($testPath1, $testData)) {
    echo "Method 1 file creation: SUCCESS\n";
    echo "Method 1 file readable: " . (is_readable($testPath1) ? 'YES' : 'NO') . "\n";
    echo "Method 1 file writable: " . (is_writable($testPath1) ? 'YES' : 'NO') . "\n";
    
    // Test copy operation
    $backupPath1 = $testPath1 . '.backup';
    if (copy($testPath1, $backupPath1)) {
        echo "Method 1 copy operation: SUCCESS\n";
        unlink($backupPath1);
    } else {
        echo "Method 1 copy operation: FAILED\n";
    }
    
    unlink($testPath1);
} else {
    echo "Method 1 file creation: FAILED\n";
}

// Test method 2 (if realpath worked)
if ($uploadDir2 && file_put_contents($testPath2, $testData)) {
    echo "Method 2 file creation: SUCCESS\n";
    echo "Method 2 file readable: " . (is_readable($testPath2) ? 'YES' : 'NO') . "\n";
    echo "Method 2 file writable: " . (is_writable($testPath2) ? 'YES' : 'NO') . "\n";
    
    // Test copy operation
    $backupPath2 = $testPath2 . '.backup';
    if (copy($testPath2, $backupPath2)) {
        echo "Method 2 copy operation: SUCCESS\n";
        unlink($backupPath2);
    } else {
        echo "Method 2 copy operation: FAILED\n";
    }
    
    unlink($testPath2);
} else {
    echo "Method 2 file creation: FAILED or N/A\n";
}

// Test method 3
if (file_put_contents($testPath3, $testData)) {
    echo "Method 3 file creation: SUCCESS\n";
    echo "Method 3 file readable: " . (is_readable($testPath3) ? 'YES' : 'NO') . "\n";
    echo "Method 3 file writable: " . (is_writable($testPath3) ? 'YES' : 'NO') . "\n";
    
    // Test copy operation
    $backupPath3 = $testPath3 . '.backup';
    if (copy($testPath3, $backupPath3)) {
        echo "Method 3 copy operation: SUCCESS\n";
        unlink($backupPath3);
    } else {
        echo "Method 3 copy operation: FAILED\n";
    }
    
    unlink($testPath3);
} else {
    echo "Method 3 file creation: FAILED\n";
}

echo "\n";

// Test GD library
echo "=== GD Library Tests ===\n";
if (extension_loaded('gd')) {
    echo "GD Extension: LOADED\n";
    $gdInfo = gd_info();
    echo "GD Version: " . $gdInfo['GD Version'] . "\n";
    echo "JPEG Support: " . ($gdInfo['JPEG Support'] ? 'YES' : 'NO') . "\n";
    echo "PNG Support: " . ($gdInfo['PNG Support'] ? 'YES' : 'NO') . "\n";
    echo "GIF Support: " . ($gdInfo['GIF Read Support'] ? 'YES' : 'NO') . "\n";
} else {
    echo "GD Extension: NOT LOADED\n";
}

echo "\n";

// Check for existing image files
echo "=== Existing Image Files ===\n";
$uploadDir = $uploadDir2 ?: $uploadDir3;
if ($uploadDir && is_dir($uploadDir)) {
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $files = scandir($uploadDir);
    $imageFiles = [];
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($ext, $imageExtensions)) {
            $imageFiles[] = $file;
        }
    }
    
    echo "Found " . count($imageFiles) . " image files:\n";
    foreach (array_slice($imageFiles, 0, 5) as $file) {
        $fullPath = $uploadDir . DIRECTORY_SEPARATOR . $file;
        echo "- $file (readable: " . (is_readable($fullPath) ? 'YES' : 'NO') . 
             ", writable: " . (is_writable($fullPath) ? 'YES' : 'NO') . ")\n";
    }
    
    if (count($imageFiles) > 5) {
        echo "... and " . (count($imageFiles) - 5) . " more files\n";
    }
} else {
    echo "Upload directory not accessible\n";
}

echo "\n=== Test Complete ===\n";
echo "If you see any FAILED results above, those indicate potential issues with the image rotation functionality on Windows.\n";
echo "Please check file permissions and ensure the web server has write access to the uploads directory.\n";
?>