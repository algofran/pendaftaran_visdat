<?php
require_once 'config.php';

// Start output buffering to prevent any unexpected output from breaking JSON
ob_start();

// Enable error reporting for debugging
if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 0); // Don't display errors to output (breaks JSON)
    ini_set('log_errors', 1);     // Log errors instead
}

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
    ob_end_clean(); // Clear any unexpected output
    echo json_encode([
        'success' => false, 
        'message' => 'Method not allowed - use POST'
    ]);
    exit;
}

try {
    // Get email from POST data
    $input = json_decode(file_get_contents('php://input'), true);
    $email = isset($input['email']) ? trim($input['email']) : '';
    
    // Validate email
    if (empty($email)) {
        ob_end_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Email tidak boleh kosong'
        ]);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        ob_end_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Format email tidak valid'
        ]);
        exit;
    }
    
    // Check if email exists in database
    $stmt = $pdo->prepare("SELECT * FROM applications WHERE email = ?");
    $stmt->execute([$email]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($application) {
        // Email found, return user data
        ob_end_clean();
        echo json_encode([
            'success' => true,
            'found' => true,
            'message' => 'Email ditemukan',
            'data' => [
                'full_name' => $application['full_name'],
                'email' => $application['email'],
                'phone' => $application['phone'],
                'birth_date' => $application['birth_date'],
                'gender' => $application['gender'],
                'position' => $application['position'],
                'education' => $application['education'],
                'experience_years' => $application['experience_years'],
                'address' => $application['address'],
                'fiber_optic_knowledge' => $application['fiber_optic_knowledge'],
                'otdr_experience' => $application['otdr_experience'],
                'jointing_experience' => $application['jointing_experience'],
                'tower_climbing_experience' => $application['tower_climbing_experience'],
                'k3_certificate' => $application['k3_certificate'],
                'work_vision' => $application['work_vision'],
                'work_mission' => $application['work_mission'],
                'motivation' => $application['motivation']
            ]
        ]);
    } else {
        // Email not found
        ob_end_clean();
        echo json_encode([
            'success' => true,
            'found' => false,
            'message' => 'Email tidak ditemukan dalam database'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Email check error: " . $e->getMessage());
    ob_end_clean(); // Clear any unexpected output
    if (defined('DEBUG') && DEBUG) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan server. Silakan coba lagi.']);
    }
}
?>