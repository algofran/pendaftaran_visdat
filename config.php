<?php
// Konfigurasi Database - SQLite
define('DB_FILE', dirname(__FILE__) . '/visdat.sqlite');

// Konfigurasi Upload
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png']);

// Debug mode (set to false in production)
define('DEBUG', true);

// Koneksi Database
try {
    $pdo = new PDO("sqlite:" . DB_FILE);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Enable foreign keys
    $pdo->exec("PRAGMA foreign_keys = ON;");
    
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
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