<?php
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'visdat_recruitment');
define('DB_USER', 'root');
define('DB_PASS', '');

// Konfigurasi Upload
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png']);

// Debug mode (set to false in production)
define('DEBUG', true);

// Set default timezone (WITA - Asia/Makassar)
date_default_timezone_set('Asia/Makassar');

// Koneksi Database
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
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

function getRegistrationSettings($pdo) {
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
        $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        return [
            'registration_status' => $results['registration_status'] ?? 'open',
            'close_date' => $results['close_date'] ?? ''
        ];
    } catch (PDOException $e) {
        return [
            'registration_status' => 'open',
            'close_date' => ''
        ];
    }
}

function isRegistrationOpen($pdo) {
    $settings = getRegistrationSettings($pdo);
    
    if ($settings['registration_status'] === 'closed') {
        return false;
    }
    
    if (!empty($settings['close_date'])) {
        $closeTime = strtotime($settings['close_date']);
        if (time() >= $closeTime) {
            return false;
        }
    }
    
    return true;
}
?>