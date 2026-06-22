<?php
require_once 'config.php';

echo "<!DOCTYPE html><html><head><title>Update Database v2</title><link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'></head><body class='p-4'><div class='container'>";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h3>Updating Database Schema...</h3><hr>";

    $queries = [
        // 1. Add SIM file column
        "ALTER TABLE applications ADD COLUMN sim_file VARCHAR(255) AFTER certificate_file",
        
        // 2. Add Archiving column
        "ALTER TABLE applications ADD COLUMN is_archived TINYINT(1) DEFAULT 0 AFTER application_status",
        
        // 3. Add Driver Evaluation columns
        "ALTER TABLE applications ADD COLUMN driver_pre_trip TEXT AFTER sim_file",
        "ALTER TABLE applications ADD COLUMN driver_emergency TEXT AFTER driver_pre_trip",
        "ALTER TABLE applications ADD COLUMN driver_integrity TEXT AFTER driver_emergency",
        "ALTER TABLE applications ADD COLUMN driver_fatigue TEXT AFTER driver_integrity",
        
        // 4. Add Technician Evaluation columns
        "ALTER TABLE applications ADD COLUMN tech_troubleshooting TEXT AFTER driver_fatigue",
        "ALTER TABLE applications ADD COLUMN tech_inventory TEXT AFTER tech_troubleshooting",
        "ALTER TABLE applications ADD COLUMN tech_safety TEXT AFTER tech_inventory",
        "ALTER TABLE applications ADD COLUMN tech_communication TEXT AFTER tech_safety",

        // 5. Cleanup old columns if they exist (optional, let's just make them safe)
        "ALTER TABLE applications MODIFY COLUMN fiber_optic_knowledge TEXT NULL",
        "ALTER TABLE applications MODIFY COLUMN otdr_experience VARCHAR(50) NULL",
        "ALTER TABLE applications MODIFY COLUMN jointing_experience VARCHAR(50) NULL"
    ];

    foreach ($queries as $sql) {
        try {
            $pdo->exec($sql);
            echo "<div class='alert alert-success'>Executed: <code>$sql</code></div>";
        } catch (PDOException $e) {
            // IF NOT EXISTS is not supported in all MariaDB/MySQL versions for ADD COLUMN
            // So we catch the "Duplicate column name" error (1060)
            if ($e->errorInfo[1] == 1060) {
                echo "<div class='alert alert-info'>Skipped (Already exists): <code>$sql</code></div>";
            } else {
                echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
            }
        }
    }

    echo "<div class='alert alert-primary mt-4'><h4>Update Selesai!</h4>Silakan kembali ke <a href='admin/index.php'>Halaman Admin</a>.</div>";

} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Koneksi Gagal: " . $e->getMessage() . "</div>";
}

echo "</div></body></html>";
