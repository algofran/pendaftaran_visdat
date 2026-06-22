<?php
require_once 'config.php';

echo "<!DOCTYPE html><html><head><title>Update Database v3</title><link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'></head><body class='p-4'><div class='container'>";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h3>Updating Database Schema (v3)...</h3><p>Adding Technician Commitment field</p><hr>";

    $queries = [
        "ALTER TABLE applications ADD COLUMN tech_commitment TEXT AFTER tech_troubleshooting"
    ];

    foreach ($queries as $sql) {
        try {
            $pdo->exec($sql);
            echo "<div class='alert alert-success'>Executed: <code>$sql</code></div>";
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1060) {
                echo "<div class='alert alert-info'>Skipped (Already exists): <code>$sql</code></div>";
            } else {
                echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
            }
        }
    }

    echo "<div class='alert alert-primary mt-4'><h4>Update Selesai!</h4>Silakan kembali ke <a href='index.html'>Halaman Pendaftaran</a> atau <a href='admin/index.php'>Halaman Admin</a>.</div>";

} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Koneksi Gagal: " . $e->getMessage() . "</div>";
}

echo "</div></body></html>";
