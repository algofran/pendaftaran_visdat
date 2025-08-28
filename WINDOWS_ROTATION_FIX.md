# Windows Image Rotation Fix

## Problem Description
The image rotation save functionality was not working properly on Windows systems due to several platform-specific issues:

1. **File Path Separators**: Windows uses backslashes (`\`) while Unix/Linux uses forward slashes (`/`)
2. **File Permissions**: Windows handles file permissions differently than Unix systems
3. **Directory Path Resolution**: Relative paths may resolve differently on Windows

## Changes Made

### 1. URL-Based Filename Extraction (`admin/rotate_image.php`)

**Before:**
```php
$fileName = $_POST['fileName'] ?? '';
$filePath = '../uploads/' . basename($fileName);
```

**After:**
```php
// Get fileUrl parameter and extract filename from it
$fileUrl = $_POST['fileUrl'] ?? $_POST['fileName'] ?? ''; // Support both new fileUrl and legacy fileName
$fileName = basename(parse_url($fileUrl, PHP_URL_PATH));

// Construct file path with proper directory separators for cross-platform compatibility
$filePath = realpath(__DIR__ . '/../uploads/') . DIRECTORY_SEPARATOR . basename($fileName);

// Fallback if realpath fails (directory doesn't exist)
if ($filePath === false) {
    $filePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . basename($fileName);
}
```

### 2. JavaScript Frontend Updates (`admin/admin-script.js`)

**Before:**
```javascript
formData.append('fileName', fileName);
```

**After:**
```javascript
formData.append('fileUrl', fileUrl);
```

### 3. Enhanced Error Logging

Added detailed debugging information that helps identify Windows-specific issues:

```php
if (!file_exists($filePath)) {
    if (DEBUG) {
        error_log("File not found - Path: $filePath, OS: " . PHP_OS . ", DIR: " . __DIR__);
        error_log("Original fileName: $fileName, Basename: " . basename($fileName));
    }
    throw new Exception('Original file not found: ' . basename($fileName));
}
```

### 4. Windows-Compatible File Permissions

**Before:**
```php
chmod($filePath, 0644);
```

**After:**
```php
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
```

### 5. Improved Backup Operation Debugging

Added logging to help identify backup creation failures:

```php
if (!copy($filePath, $backupPath)) {
    if (DEBUG) {
        error_log("Backup failed - Source: $filePath, Backup: $backupPath, OS: " . PHP_OS);
        error_log("Source readable: " . (is_readable($filePath) ? 'yes' : 'no'));
        error_log("Destination writable: " . (is_writable(dirname($backupPath)) ? 'yes' : 'no'));
    }
    throw new Exception('Failed to create backup of original file');
}
```

## Testing and Diagnosis

### Diagnostic Script
A comprehensive test script has been created at `admin/test_rotation_windows.php` that checks:

- File path resolution methods
- Directory permissions
- File operations (create, read, write, copy)
- GD library functionality
- Existing image file accessibility

### How to Use the Diagnostic Script

1. Access the script via browser: `http://yoursite.com/admin/test_rotation_windows.php`
2. Review the output for any "FAILED" results
3. Check the specific areas that failed and address the underlying issues

## Troubleshooting Guide

### Common Windows Issues and Solutions

#### 1. "Original file not found" Error
**Cause**: File path resolution issues
**Solution**: 
- Ensure the `uploads` directory exists
- Check that the web server has read access to the uploads directory
- Verify file names don't contain invalid Windows characters

#### 2. "Failed to create backup of original file" Error
**Cause**: Write permission issues
**Solution**:
- Grant write permissions to the uploads directory for the web server user
- Check if the directory is read-only
- Ensure sufficient disk space

#### 3. "Failed to save rotated image" Error
**Cause**: File write permission or GD library issues
**Solution**:
- Verify the GD extension is loaded (`php -m | grep gd`)
- Check file write permissions
- Ensure the image format is supported

### Windows-Specific Considerations

1. **File Locking**: Windows may lock files that are being processed. Ensure no other applications are accessing the image files.

2. **Path Length**: Windows has a 260-character path limit (unless extended paths are enabled). Keep file paths short.

3. **File Attributes**: Remove read-only attributes from uploaded files if they exist.

4. **Web Server Configuration**: 
   - For IIS: Ensure the application pool identity has write permissions
   - For Apache on Windows: Check that the Apache service user has appropriate permissions

### Checking Permissions on Windows

1. Right-click the `uploads` folder
2. Select "Properties" → "Security" tab
3. Ensure the web server user (e.g., `IIS_IUSRS`, `NETWORK SERVICE`) has:
   - Read & execute
   - Write
   - Modify permissions

## Debug Mode

To enable enhanced debugging:

1. In `config.php`, ensure `DEBUG` is set to `true`:
   ```php
   define('DEBUG', true);
   ```

2. Check your web server error logs for detailed debugging information when rotation fails.

## Verification

After implementing these fixes:

1. Run the diagnostic script: `admin/test_rotation_windows.php`
2. Test image rotation functionality through the admin interface
3. Check error logs for any remaining issues
4. Verify that rotated images are properly saved and accessible

## Additional Notes

- These changes maintain backward compatibility with Linux/Unix systems
- The fixes include fallback mechanisms for edge cases
- All file operations now use cross-platform compatible approaches
- Enhanced error reporting helps identify specific issues quickly

If you continue to experience issues after implementing these fixes, run the diagnostic script and check the detailed error logs for specific failure points.