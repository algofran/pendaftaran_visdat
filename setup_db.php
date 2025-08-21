<?php
header('Content-Type: application/json');

try {
    // Try to connect to MySQL server
    $pdo = new PDO("mysql:host=localhost", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS visdat_recruitment");
    
    // Connect to the specific database
    $pdo = new PDO("mysql:host=localhost;dbname=visdat_recruitment", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read and execute SQL file
    $sql = file_get_contents('database.sql');
    $pdo->exec($sql);
    
    echo json_encode([
        'success' => true,
        'message' => 'Database berhasil dibuat dan tabel sudah siap!'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
