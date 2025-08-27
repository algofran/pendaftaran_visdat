<?php
// Production Configuration Template
// Copy this file to config.php and update with your production database credentials

// Konfigurasi Database - UPDATE THESE VALUES FOR PRODUCTION
define('DB_HOST', 'localhost'); // Change to your production database host
define('DB_NAME', 'visdat_recruitment'); // Change to your production database name
define('DB_USER', 'your_db_user'); // Change to your production database user
define('DB_PASS', 'your_db_password'); // Change to your production database password

// Konfigurasi Upload
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png']);

// Debug mode (set to false in production)
define('DEBUG', false);

// Production error reporting settings
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't show errors to users
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

// Koneksi Database
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    // Log the error instead of displaying it
    error_log("Database connection failed: " . $e->getMessage());
    
    if (DEBUG) {
        die("Koneksi database gagal: " . $e->getMessage());
    } else {
        // Show generic error message in production
        http_response_code(500);
        die("Sistem sedang mengalami gangguan. Silakan coba lagi nanti.");
    }
}

// Fungsi helper
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function generateFileName($originalName) {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    return uniqid() . '_' . time() . '.' . $extension;
}

function createUploadDir() {
    if (!file_exists(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
        // Create .htaccess for security
        $htaccess = UPLOAD_DIR . '.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Options -Indexes\nOptions -ExecCGI\nAddHandler cgi-script .php .pl .py .jsp .asp .sh .cgi\n");
        }
    }
}
?>