<?php
session_start();
require_once '../config.php';

// Simple authentication
if (!isset($_SESSION['admin_logged_in'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $archive = $_POST['archive'] ?? 1;
    $startDate = $_POST['start_date'] ?? null;
    $endDate = $_POST['end_date'] ?? null;

    try {
        if (!empty($startDate) && !empty($endDate)) {
            // Bulk archive by date range
            $stmt = $pdo->prepare("UPDATE applications SET is_archived = 1 WHERE DATE(created_at) BETWEEN ? AND ? AND is_archived = 0");
            $result = $stmt->execute([$startDate, $endDate]);
            $count = $stmt->rowCount();
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => "$count data berhasil diarsipkan dalam rentang $startDate s/d $endDate"]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal memproses pengarsipan massal']);
            }
        } else if (!empty($id)) {
            // Single toggle
            $stmt = $pdo->prepare("UPDATE applications SET is_archived = ? WHERE id = ?");
            $result = $stmt->execute([$archive, $id]);

            if ($result && $stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => $archive ? 'Data berhasil diarsipkan' : 'Data berhasil dikembalikan']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal: Data tidak ditemukan atau sudah berstatus tersebut']);
            }
        } else {
            // Debugging info if both are empty
            $received = json_encode($_POST);
            echo json_encode(['success' => false, 'message' => "Parameter tidak lengkap. Data diterima: $received"]);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
