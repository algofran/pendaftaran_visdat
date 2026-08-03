<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Akses tidak sah. Silakan login kembali.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode request tidak diizinkan.']);
    exit;
}

try {
    $status = isset($_POST['registration_status']) ? sanitize($_POST['registration_status']) : 'open';
    $closeDateInput = isset($_POST['close_date']) ? trim($_POST['close_date']) : '';
    
    // Validate status
    if (!in_array($status, ['open', 'closed'])) {
        throw new Exception("Status pendaftaran tidak valid.");
    }
    
    // Parse close date if provided
    $closeDate = '';
    if (!empty($closeDateInput)) {
        $timestamp = strtotime($closeDateInput);
        if ($timestamp === false) {
            throw new Exception("Format tanggal penutupan tidak valid.");
        }
        $closeDate = date('Y-m-d H:i:s', $timestamp);
    }
    
    // Update settings in database
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'registration_status'");
    $stmt->execute([$status]);
    
    $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'close_date'");
    $stmt->execute([$closeDate]);
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Pengaturan pendaftaran berhasil disimpan.'
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
