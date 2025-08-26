<?php
session_start();
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Set content type to JSON
header('Content-Type: application/json');

try {
    // Get all applications with full data
    $sql = "SELECT 
                id,
                full_name,
                email,
                phone,
                position,
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
            ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $applications = $stmt->fetchAll();

    // Get the base URL for file links
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $scriptDir = dirname($_SERVER['REQUEST_URI']);
    $baseUrl = $protocol . '://' . $host . str_replace('/admin', '', $scriptDir) . '/uploads/';

    // Process data for export
    $exportData = [];
    foreach ($applications as $app) {
        $row = [
            'ID' => $app['id'],
            'Nama Lengkap' => $app['full_name'],
            'Email' => $app['email'],
            'Telepon' => $app['phone'],
            'Posisi' => $app['position'],
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
    }

    // Handle empty data case
    if (empty($exportData)) {
        // Create a sample row with headers
        $exportData = [[
            'ID' => '',
            'Nama Lengkap' => '',
            'Email' => '',
            'Telepon' => '',
            'Posisi' => '',
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
    echo json_encode([
        'success' => true,
        'data' => $exportData,
        'count' => count($exportData),
        'isEmpty' => empty($applications)
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Unexpected error: ' . $e->getMessage()
    ]);
}
?>