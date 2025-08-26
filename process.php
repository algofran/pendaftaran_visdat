<?php
require_once 'config.php';

// Start output buffering to prevent any unexpected output from breaking JSON
ob_start();

// Enable error reporting for debugging
if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 0); // Don't display errors to output (breaks JSON)
    ini_set('log_errors', 1);     // Log errors instead
    error_log("process.php called at " . date('Y-m-d H:i:s'));
    error_log("POST data size: " . strlen(file_get_contents('php://input')) . " bytes");
    error_log("Files received: " . json_encode(array_keys($_FILES)));
}

// Increase memory and time limits for file uploads
ini_set('memory_limit', '128M');
ini_set('max_execution_time', 60);
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '15M');

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
        'message' => 'Method not allowed - use POST',
        'method_received' => $_SERVER['REQUEST_METHOD'],
        'debug' => 'This endpoint only accepts POST requests'
    ]);
    exit;
}

try {
    // Check if database exists and is accessible
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'applications'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        throw new Exception("Tabel 'applications' tidak ditemukan. Silakan import database.sql terlebih dahulu.");
    }
    
    // Create upload directory if not exists
    createUploadDir();
    
    // Validate required fields
    $requiredFields = [
        'full_name', 'email', 'phone', 'birth_date', 'gender', 'position',
        'education', 'experience_years', 'address', 'work_vision', 'work_mission', 'motivation'
    ];
    
    $errors = [];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "Field $field wajib diisi";
        }
    }
    
    // Validate email
    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }
    
    // Check if email already exists (only for new registrations)
    $isUpdate = isset($_POST['is_update']) && $_POST['is_update'] === '1';
    if (!empty($_POST['email']) && !$isUpdate) {
        $stmt = $pdo->prepare("SELECT id FROM applications WHERE email = ?");
        $stmt->execute([$_POST['email']]);
        if ($stmt->fetch()) {
            $errors[] = "Email sudah terdaftar sebelumnya";
        }
    }
    
    // Debug: Log uploaded files
    if (DEBUG) {
        error_log("Files received: " . print_r($_FILES, true));
    }
    
    // Check which files are uploaded
    $hasFileUploads = false;
    $fileFields = ['cv_file', 'photo_file', 'ktp_file', 'ijazah_file', 'certificate_file', 'sim_file'];
    foreach ($fileFields as $field) {
        if (!empty($_FILES[$field]['name'])) {
            $hasFileUploads = true;
            break;
        }
    }
    
    if ($hasFileUploads) {
        // Only validate required files if we detect file uploads
        if (empty($_FILES['cv_file']['name'])) {
            $errors[] = "CV/Resume wajib diupload";
        }
        
        if (empty($_FILES['photo_file']['name'])) {
            $errors[] = "Foto 3x4 wajib diupload";
        }
        
        if (empty($_FILES['ktp_file']['name'])) {
            $errors[] = "Foto KTP wajib diupload";
        }
        
        if (empty($_FILES['ijazah_file']['name'])) {
            $errors[] = "Foto Ijazah wajib diupload";
        }
        
        if (DEBUG) {
            error_log("File uploads detected - validating required files");
        }
    } else {
        // For testing without files
        if (DEBUG) {
            error_log("Form submission without file uploads - testing mode");
        }
    }
    
    // Validate position-specific requirements (only if files are being uploaded)
    $position = $_POST['position'] ?? '';
    if ($hasFileUploads) {
        if ($position === 'Driver' && empty($_FILES['sim_file']['name'])) {
            $errors[] = "SIM A/C wajib untuk posisi Driver";
        }
        
        $technicalPositions = ['Teknisi FOT', 'Teknisi FOC', 'Teknisi Jointer'];
        if (in_array($position, $technicalPositions) && empty($_FILES['certificate_file']['name'])) {
            $errors[] = "Sertifikat K3 wajib untuk posisi teknis";
        }
    }
    
    if (!empty($errors)) {
        ob_end_clean(); // Clear any unexpected output
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        exit;
    }
    
    // Process file uploads
    $uploadedFiles = [];
    $fileFields = ['cv_file', 'photo_file', 'ktp_file', 'ijazah_file', 'certificate_file', 'sim_file'];
    
    foreach ($fileFields as $field) {
        if (!empty($_FILES[$field]['name'])) {
            if (DEBUG) {
                error_log("Processing file upload for: $field");
                error_log("File details: " . print_r($_FILES[$field], true));
            }
            
            $uploadResult = uploadFile($_FILES[$field], $field);
            if ($uploadResult['success']) {
                $uploadedFiles[$field] = $uploadResult['filename'];
                if (DEBUG) {
                    error_log("File uploaded successfully: $field -> " . $uploadResult['filename']);
                }
            } else {
                // Clean up already uploaded files
                foreach ($uploadedFiles as $uploadedFile) {
                    if (file_exists(UPLOAD_DIR . $uploadedFile)) {
                        unlink(UPLOAD_DIR . $uploadedFile);
                    }
                }
                ob_end_clean(); // Clear any unexpected output
                echo json_encode(['success' => false, 'message' => $uploadResult['message']]);
                exit;
            }
        } else {
            $uploadedFiles[$field] = null;
            if (DEBUG) {
                error_log("No file uploaded for: $field");
            }
        }
    }
    
    // Prepare data for database
    $data = [
        'full_name' => sanitize($_POST['full_name']),
        'email' => sanitize($_POST['email']),
        'phone' => sanitize($_POST['phone']),
        'position' => sanitize($_POST['position']),
        'education' => sanitize($_POST['education']),
        'experience_years' => (int)$_POST['experience_years'],
        'address' => sanitize($_POST['address']),
        'birth_date' => $_POST['birth_date'],
        'gender' => sanitize($_POST['gender']),
        'cv_file' => $uploadedFiles['cv_file'] ?? null,
        'photo_file' => $uploadedFiles['photo_file'] ?? null,
        'ktp_file' => $uploadedFiles['ktp_file'] ?? null,
        'ijazah_file' => $uploadedFiles['ijazah_file'] ?? null,
        'certificate_file' => $uploadedFiles['certificate_file'] ?? null,
        'sim_file' => $uploadedFiles['sim_file'] ?? null,
        'fiber_optic_knowledge' => sanitize($_POST['fiber_optic_knowledge'] ?? ''),
        'otdr_experience' => sanitize($_POST['otdr_experience'] ?? 'Tidak'),
        'jointing_experience' => sanitize($_POST['jointing_experience'] ?? 'Tidak'),
        'tower_climbing_experience' => sanitize($_POST['tower_climbing_experience'] ?? 'Tidak'),
        'k3_certificate' => sanitize($_POST['k3_certificate'] ?? 'Tidak'),
        'work_vision' => sanitize($_POST['work_vision']),
        'work_mission' => sanitize($_POST['work_mission']),
        'motivation' => sanitize($_POST['motivation']),
        'application_status' => 'Pending'
    ];
    
    // Insert or Update into database
    if ($isUpdate) {
        // Update existing record
        $sql = "UPDATE applications SET 
            full_name = :full_name, phone = :phone, position = :position, education = :education, 
            experience_years = :experience_years, address = :address, birth_date = :birth_date, gender = :gender,
            fiber_optic_knowledge = :fiber_optic_knowledge, otdr_experience = :otdr_experience, 
            jointing_experience = :jointing_experience, tower_climbing_experience = :tower_climbing_experience, 
            k3_certificate = :k3_certificate, work_vision = :work_vision, work_mission = :work_mission, 
            motivation = :motivation";
        
        // Only update file fields if new files were uploaded
        if (!empty($uploadedFiles['cv_file'])) $sql .= ", cv_file = :cv_file";
        if (!empty($uploadedFiles['photo_file'])) $sql .= ", photo_file = :photo_file";
        if (!empty($uploadedFiles['ktp_file'])) $sql .= ", ktp_file = :ktp_file";
        if (!empty($uploadedFiles['ijazah_file'])) $sql .= ", ijazah_file = :ijazah_file";
        if (!empty($uploadedFiles['certificate_file'])) $sql .= ", certificate_file = :certificate_file";
        if (!empty($uploadedFiles['sim_file'])) $sql .= ", sim_file = :sim_file";
        
        $sql .= " WHERE email = :email";
        
        // Prepare data for update (remove file fields that weren't uploaded)
        $updateData = $data;
        if (empty($uploadedFiles['cv_file'])) unset($updateData['cv_file']);
        if (empty($uploadedFiles['photo_file'])) unset($updateData['photo_file']);
        if (empty($uploadedFiles['ktp_file'])) unset($updateData['ktp_file']);
        if (empty($uploadedFiles['ijazah_file'])) unset($updateData['ijazah_file']);
        if (empty($uploadedFiles['certificate_file'])) unset($updateData['certificate_file']);
        if (empty($uploadedFiles['sim_file'])) unset($updateData['sim_file']);
        unset($updateData['application_status']); // Don't update application status
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($updateData);
        
        if ($result) {
            // Get the existing application ID
            $stmt = $pdo->prepare("SELECT id FROM applications WHERE email = ?");
            $stmt->execute([$data['email']]);
            $application = $stmt->fetch();
            $applicationId = $application['id'];
            
            // Generate reference number
            $referenceNumber = 'VIS-' . date('Ymd') . '-' . str_pad($applicationId, 4, '0', STR_PAD_LEFT);
            
            ob_end_clean(); // Clear any unexpected output
            echo json_encode([
                'success' => true, 
                'message' => 'Data lamaran berhasil diperbarui!',
                'application_id' => $applicationId,
                'reference_number' => $referenceNumber
            ]);
        } else {
            // Clean up uploaded files if database update failed
            foreach ($uploadedFiles as $uploadedFile) {
                if ($uploadedFile && file_exists(UPLOAD_DIR . $uploadedFile)) {
                    unlink(UPLOAD_DIR . $uploadedFile);
                }
            }
            ob_end_clean(); // Clear any unexpected output
            echo json_encode(['success' => false, 'message' => 'Gagal memperbarui data lamaran']);
        }
    } else {
        // Insert new record
        $sql = "INSERT INTO applications (
            full_name, email, phone, position, education, experience_years, address, birth_date, gender,
            cv_file, photo_file, ktp_file, ijazah_file, certificate_file, sim_file,
            fiber_optic_knowledge, otdr_experience, jointing_experience, tower_climbing_experience, k3_certificate,
            work_vision, work_mission, motivation, application_status
        ) VALUES (
            :full_name, :email, :phone, :position, :education, :experience_years, :address, :birth_date, :gender,
            :cv_file, :photo_file, :ktp_file, :ijazah_file, :certificate_file, :sim_file,
            :fiber_optic_knowledge, :otdr_experience, :jointing_experience, :tower_climbing_experience, :k3_certificate,
            :work_vision, :work_mission, :motivation, :application_status
        )";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($data);
        
        if ($result) {
            $applicationId = $pdo->lastInsertId();
            
            // Send confirmation email (optional)
            sendConfirmationEmail($data['email'], $data['full_name'], $applicationId);
            
            // Generate reference number
            $referenceNumber = 'VIS-' . date('Ymd') . '-' . str_pad($applicationId, 4, '0', STR_PAD_LEFT);
            
            ob_end_clean(); // Clear any unexpected output
            echo json_encode([
                'success' => true, 
                'message' => 'Lamaran berhasil dikirim!',
                'application_id' => $applicationId,
                'reference_number' => $referenceNumber
            ]);
        } else {
            // Clean up uploaded files if database insert failed
            foreach ($uploadedFiles as $uploadedFile) {
                if ($uploadedFile && file_exists(UPLOAD_DIR . $uploadedFile)) {
                    unlink(UPLOAD_DIR . $uploadedFile);
                }
            }
            ob_end_clean(); // Clear any unexpected output
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan data lamaran']);
        }
    }
    
} catch (Exception $e) {
    error_log("Application submission error: " . $e->getMessage());
    ob_end_clean(); // Clear any unexpected output
    // In development, show detailed error
    if (defined('DEBUG') && DEBUG) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage(), 'debug' => $e->getTraceAsString()]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan server. Silakan coba lagi.']);
    }
}

function uploadFile($file, $fieldName) {
    if (DEBUG) {
        error_log("=== uploadFile() called for: $fieldName ===");
        error_log("File array: " . print_r($file, true));
        error_log("Upload directory: " . UPLOAD_DIR);
        error_log("Upload directory exists: " . (is_dir(UPLOAD_DIR) ? 'YES' : 'NO'));
        error_log("Upload directory writable: " . (is_writable(UPLOAD_DIR) ? 'YES' : 'NO'));
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'File terlalu besar (melebihi upload_max_filesize)',
            UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (melebihi MAX_FILE_SIZE)',
            UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian',
            UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ada',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
            UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi PHP'
        ];
        $errorMsg = $errorMessages[$file['error']] ?? "Error tidak diketahui ({$file['error']})";
        if (DEBUG) {
            error_log("Upload error for $fieldName: $errorMsg");
        }
        return ['success' => false, 'message' => "Error uploading $fieldName: $errorMsg"];
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => "File $fieldName terlalu besar (maksimal 5MB)"];
    }
    
    // Check file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        return ['success' => false, 'message' => "Format file $fieldName tidak didukung"];
    }
    
    // Validate file type based on field
    $allowedTypes = [];
    switch ($fieldName) {
        case 'cv_file':
            $allowedTypes = ['pdf', 'doc', 'docx'];
            break;
        case 'photo_file':
            $allowedTypes = ['jpg', 'jpeg', 'png'];
            break;
        case 'ktp_file':
        case 'ijazah_file':
        case 'certificate_file':
        case 'sim_file':
            $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png'];
            break;
    }
    
    if (!in_array($extension, $allowedTypes)) {
        return ['success' => false, 'message' => "Format file $fieldName tidak sesuai"];
    }
    
    // Generate unique filename
    $filename = generateFileName($file['name']);
    $uploadPath = UPLOAD_DIR . $filename;
    
    // Move uploaded file
    if (DEBUG) {
        error_log("Attempting to move file from: " . $file['tmp_name']);
        error_log("To: $uploadPath");
        error_log("Temp file exists: " . (file_exists($file['tmp_name']) ? 'YES' : 'NO'));
        error_log("Temp file size: " . (file_exists($file['tmp_name']) ? filesize($file['tmp_name']) : 'N/A'));
    }
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // Set proper permissions
        chmod($uploadPath, 0644);
        if (DEBUG) {
            error_log("File uploaded successfully: $uploadPath");
            error_log("Final file size: " . filesize($uploadPath));
        }
        return ['success' => true, 'filename' => $filename];
    } else {
        if (DEBUG) {
            error_log("move_uploaded_file() failed for $fieldName");
            error_log("Last error: " . error_get_last()['message'] ?? 'No error details');
        }
        return ['success' => false, 'message' => "Gagal menyimpan file $fieldName"];
    }
}

function sendConfirmationEmail($email, $fullName, $applicationId) {
    // Simple email confirmation (you can implement with PHPMailer for better functionality)
    $subject = "Konfirmasi Lamaran - PT. Visdat Teknik Utama";
    $message = "
    Yth. $fullName,
    
    Terima kasih telah mengirimkan lamaran kerja ke PT. Visdat Teknik Utama.
    
    ID Lamaran Anda: $applicationId
    
    Lamaran Anda sedang dalam proses review. Kami akan menghubungi Anda jika ada perkembangan lebih lanjut.
    
    Hormat kami,
    Tim HR PT. Visdat Teknik Utama
    ";
    
    $headers = "From: noreply@visualdata.co.id\r\n";
    $headers .= "Reply-To: hrd@visualdata.co.id\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    // Uncomment to send email
    // mail($email, $subject, $message, $headers);
}
?>