<?php
// Production Configuration Checker
// This script helps verify if your config.php is properly set up for production

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Production Configuration Check</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
    .check { margin: 10px 0; padding: 10px; border-radius: 5px; }
    .pass { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
    .warn { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
    .fail { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
    h3 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 5px; }
</style>";

// Check 1: Config file exists
echo "<h3>1. Configuration File</h3>";
$configPath = '../config.php';
if (file_exists($configPath)) {
    echo "<div class='check pass'>✅ Config file exists</div>";
    
    // Include config
    try {
        require_once $configPath;
        echo "<div class='check pass'>✅ Config file loaded successfully</div>";
    } catch (Exception $e) {
        echo "<div class='check fail'>❌ Error loading config: " . $e->getMessage() . "</div>";
        exit;
    }
} else {
    echo "<div class='check fail'>❌ Config file missing: $configPath</div>";
    exit;
}

// Check 2: Debug mode
echo "<h3>2. Debug Mode</h3>";
if (defined('DEBUG')) {
    if (DEBUG === false) {
        echo "<div class='check pass'>✅ DEBUG is set to FALSE (production ready)</div>";
    } else {
        echo "<div class='check warn'>⚠️ DEBUG is set to TRUE (should be FALSE in production)</div>";
        echo "<div class='check'>💡 Set DEBUG to false in config.php for production</div>";
    }
} else {
    echo "<div class='check warn'>⚠️ DEBUG constant not defined</div>";
}

// Check 3: Database credentials
echo "<h3>3. Database Configuration</h3>";
$dbConstants = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'];
$hasAllConstants = true;

foreach ($dbConstants as $const) {
    if (defined($const)) {
        $value = constant($const);
        $displayValue = ($const === 'DB_PASS') ? str_repeat('*', strlen($value)) : $value;
        echo "<div class='check pass'>✅ $const: $displayValue</div>";
        
        // Check for development values
        if ($const === 'DB_USER' && $value === 'root') {
            echo "<div class='check warn'>⚠️ Using 'root' user - consider using dedicated database user in production</div>";
        }
        if ($const === 'DB_PASS' && empty($value)) {
            echo "<div class='check warn'>⚠️ Empty password - ensure your production database has proper authentication</div>";
        }
    } else {
        echo "<div class='check fail'>❌ $const not defined</div>";
        $hasAllConstants = false;
    }
}

// Check 4: Database connection
echo "<h3>4. Database Connection</h3>";
if ($hasAllConstants) {
    try {
        $testPdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $testPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "<div class='check pass'>✅ Database connection successful</div>";
        
        // Check MySQL version
        $versionStmt = $testPdo->query("SELECT VERSION() as version");
        $version = $versionStmt->fetch()['version'];
        $versionNumber = floatval($version);
        echo "<div class='check pass'>✅ MySQL version: $version</div>";
        
        if ($versionNumber >= 8.0) {
            echo "<div class='check pass'>✅ MySQL 8.0+ detected - ROW_NUMBER() function supported</div>";
        } else {
            echo "<div class='check warn'>⚠️ MySQL < 8.0 detected - using compatibility mode for export</div>";
        }
        
    } catch (PDOException $e) {
        echo "<div class='check fail'>❌ Database connection failed: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<div class='check fail'>❌ Cannot test database connection - missing constants</div>";
}

// Check 5: File permissions
echo "<h3>5. File Permissions</h3>";
$uploadDir = '../uploads/';
if (is_dir($uploadDir)) {
    if (is_writable($uploadDir)) {
        echo "<div class='check pass'>✅ Uploads directory is writable</div>";
    } else {
        echo "<div class='check warn'>⚠️ Uploads directory is not writable</div>";
    }
} else {
    echo "<div class='check warn'>⚠️ Uploads directory does not exist</div>";
}

// Check if error log directory is writable
$errorLogDir = __DIR__;
if (is_writable($errorLogDir)) {
    echo "<div class='check pass'>✅ Admin directory is writable (for error logs)</div>";
} else {
    echo "<div class='check warn'>⚠️ Admin directory is not writable (error logging may fail)</div>";
}

// Check 6: PHP Configuration
echo "<h3>6. PHP Configuration</h3>";
$memoryLimit = ini_get('memory_limit');
echo "<div class='check pass'>✅ Memory limit: $memoryLimit</div>";

$maxFileSize = ini_get('upload_max_filesize');
echo "<div class='check pass'>✅ Max file upload: $maxFileSize</div>";

$maxPostSize = ini_get('post_max_size');
echo "<div class='check pass'>✅ Max POST size: $maxPostSize</div>";

// Final recommendations
echo "<h3>7. Recommendations</h3>";
echo "<div class='check'>
    <strong>For Production Environment:</strong><br>
    • Set DEBUG = false in config.php<br>
    • Use strong database passwords<br>
    • Consider using dedicated database user (not root)<br>
    • Ensure file permissions are secure (755 for directories, 644 for files)<br>
    • Keep error logs for debugging but don't display errors to users<br>
    • Regular backup of database and uploaded files
</div>";

echo "<hr>";
echo "<p><a href='test-db-connection.php'>→ Run Database Connection Test</a></p>";
echo "<p><a href='test-export.php'>→ Test Export Functionality</a></p>";
echo "<p><strong>Last checked:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>