<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to browser in production
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/export_error.log');

session_start();

// Log the start of export attempt
error_log("Export attempt started at " . date('Y-m-d H:i:s'));

try {
    require_once '../config.php';
    error_log("Config loaded successfully");
} catch (Exception $e) {
    error_log("Failed to load config: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Configuration error']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    error_log("Unauthorized access attempt");
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

error_log("User authenticated successfully");

// Set content type to JSON
header('Content-Type: application/json');

try {
    // Check if database connection exists
    if (!isset($pdo)) {
        throw new Exception("Database connection not available");
    }
    
    error_log("Database connection confirmed");
    
    // Check if applications table exists
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'applications'");
    if (!$tableCheck->fetch()) {
        throw new Exception("Applications table does not exist");
    }
    
    error_log("Applications table exists");
    
    // Get all applications
    error_log("Using standard query for SQLite");
    $sql = "SELECT 
                id,
                full_name,
                email,
                phone,
                position,
                location,
                education,
                experience_years,
                address,
                birth_date,
                gender,
                cv_file,
                photo_file,
                ktp_file,
                ijazah_file,
                certificate_file,
                sim_file,
                fiber_optic_knowledge,
                otdr_experience,
                jointing_experience,
                tower_climbing_experience,
                k3_certificate,
                work_vision,
                work_mission,
                motivation,
                application_status,
                created_at,
                updated_at
            FROM applications 
            ORDER BY created_at ASC";
    
    error_log("Preparing SQL query");
    $stmt = $pdo->prepare($sql);
    
    error_log("Executing SQL query");
    $stmt->execute();
    
    error_log("Fetching results");
    $applications = $stmt->fetchAll();
    
    error_log("Query executed successfully. Found " . count($applications) . " applications");

    // Get the base URL for file links
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $scriptDir = dirname($_SERVER['REQUEST_URI']);
    $baseUrl = $protocol . '://' . $host . str_replace('/admin', '', $scriptDir) . '/uploads/';

    // Process data for export
    $exportData = [];
    $registrationNumber = 1; // Manual counter for MySQL < 8.0
    
    foreach ($applications as $app) {
        // Use registration_number from query if available (MySQL 8.0+), otherwise use manual counter
        $regNumber = isset($app['registration_number']) ? $app['registration_number'] : $registrationNumber;
        
        $row = [
            'No' => $regNumber,
            'Nama Lengkap' => $app['full_name'],
            'Email' => $app['email'],
            'Telepon' => $app['phone'],
            'Posisi' => $app['position'],
            'Lokasi Penempatan' => $app['location'] ?? '',
            'Pendidikan' => $app['education'],
            'Pengalaman (Tahun)' => $app['experience_years'],
            'Alamat' => $app['address'],
            'Tanggal Lahir' => $app['birth_date'],
            'Jenis Kelamin' => $app['gender'],
            'File CV' => !empty($app['cv_file']) ? $baseUrl . $app['cv_file'] : '',
            'File Foto' => !empty($app['photo_file']) ? $baseUrl . $app['photo_file'] : '',
            'File KTP' => !empty($app['ktp_file']) ? $baseUrl . $app['ktp_file'] : '',
            'File Ijazah' => !empty($app['ijazah_file']) ? $baseUrl . $app['ijazah_file'] : '',
            'File Sertifikat K3' => !empty($app['certificate_file']) ? $baseUrl . $app['certificate_file'] : '',
            'File SIM' => !empty($app['sim_file']) ? $baseUrl . $app['sim_file'] : '',
            'Pengetahuan Fiber Optik' => $app['fiber_optic_knowledge'],
            'Pengalaman OTDR' => $app['otdr_experience'],
            'Pengalaman Jointing' => $app['jointing_experience'],
            'Pengalaman Panjat Tower' => $app['tower_climbing_experience'],
            'Sertifikat K3' => $app['k3_certificate'],
            'Visi Kerja' => $app['work_vision'],
            'Misi Kerja' => $app['work_mission'],
            'Motivasi' => $app['motivation'],
            'Status Lamaran' => $app['application_status'],
            'Tanggal Daftar' => date('d/m/Y H:i:s', strtotime($app['created_at'])),
            'Terakhir Update' => date('d/m/Y H:i:s', strtotime($app['updated_at']))
        ];
        $exportData[] = $row;
        $registrationNumber++; // Increment manual counter
    }

    // Handle empty data case
    if (empty($exportData)) {
        // Create a sample row with headers
        $exportData = [[
            'No' => '',
            'Nama Lengkap' => '',
            'Email' => '',
            'Telepon' => '',
            'Posisi' => '',
            'Lokasi Penempatan' => '',
            'Pendidikan' => '',
            'Pengalaman (Tahun)' => '',
            'Alamat' => '',
            'Tanggal Lahir' => '',
            'Jenis Kelamin' => '',
            'File CV' => '',
            'File Foto' => '',
            'File Sertifikat K3' => '',
            'File SIM' => '',
            'Pengetahuan Fiber Optik' => '',
            'Pengalaman OTDR' => '',
            'Pengalaman Jointing' => '',
            'Pengalaman Panjat Tower' => '',
            'Sertifikat K3' => '',
            'Visi Kerja' => '',
            'Misi Kerja' => '',
            'Motivasi' => '',
            'Status Lamaran' => '',
            'Tanggal Daftar' => '',
            'Terakhir Update' => ''
        ]];
    }

    // Return JSON response
    error_log("Returning JSON response with " . count($exportData) . " records");
    echo json_encode([
        'success' => true,
        'data' => $exportData,
        'count' => count($exportData),
        'isEmpty' => empty($applications)
    ]);
    
    error_log("Export completed successfully");

} catch (PDOException $e) {
    error_log("PDO Exception: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("General Exception: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Unexpected error: ' . $e->getMessage()
    ]);
}
?>