<?php
/**
 * Database Migration and Verification Script
 * This script ensures the MySQL database schema matches the current form requirements.
 */

require_once 'config.php';

// Set headers for plain text output
header('Content-Type: text/plain');

echo "PT. Visdat Teknik Utama - Database Verification Tool\n";
echo "==================================================\n\n";

try {
    // 1. Check Table Existence
    echo "1. Checking 'applications' table... ";
    $stmt = $pdo->query("SHOW TABLES LIKE 'applications'");
    if ($stmt->rowCount() == 0) {
        echo "NOT FOUND\n   Creating table... ";
        $sql = "CREATE TABLE applications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            position VARCHAR(100) NOT NULL,
            location VARCHAR(100),
            education VARCHAR(100) NOT NULL,
            experience_years INT NOT NULL,
            work_experiences TEXT,
            address TEXT NOT NULL,
            birth_date DATE NOT NULL,
            gender ENUM('Laki-laki', 'Perempuan') NOT NULL,
            cv_file VARCHAR(255),
            photo_file VARCHAR(255),
            ktp_file VARCHAR(255),
            ijazah_file VARCHAR(255),
            kk_file VARCHAR(255),
            certificate_file VARCHAR(255),
            sim_file VARCHAR(255),
            driver_pre_trip TEXT,
            driver_emergency TEXT,
            driver_integrity TEXT,
            driver_fatigue TEXT,
            driver_commitment TEXT,
            tech_troubleshooting TEXT,
            tech_commitment TEXT,
            tech_inventory TEXT,
            tech_safety TEXT,
            tech_communication TEXT,
            work_vision TEXT,
            work_mission TEXT,
            motivation TEXT,
            application_status ENUM('Pending', 'Review', 'Interview', 'Accepted', 'Rejected') DEFAULT 'Pending',
            is_archived TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
        echo "SUCCESS\n";
    } else {
        echo "EXISTS\n";
    }

    // 2. Check for missing columns
    echo "\n2. Verifying columns...\n";
    
    $required_columns = [
        'location' => "VARCHAR(100) AFTER position",
        'work_experiences' => "TEXT NULL AFTER experience_years",
        'ktp_file' => "VARCHAR(255) AFTER photo_file",
        'ijazah_file' => "VARCHAR(255) AFTER ktp_file",
        'kk_file' => "VARCHAR(255) AFTER ijazah_file",
        'sim_file' => "VARCHAR(255) AFTER certificate_file",
        'driver_pre_trip' => "TEXT AFTER sim_file",
        'driver_emergency' => "TEXT AFTER driver_pre_trip",
        'driver_integrity' => "TEXT AFTER driver_emergency",
        'driver_fatigue' => "TEXT AFTER driver_integrity",
        'driver_commitment' => "TEXT AFTER driver_fatigue",
        'tech_troubleshooting' => "TEXT AFTER driver_commitment",
        'tech_commitment' => "TEXT AFTER tech_troubleshooting",
        'tech_inventory' => "TEXT AFTER tech_commitment",
        'tech_safety' => "TEXT AFTER tech_inventory",
        'tech_communication' => "TEXT AFTER tech_safety",
        'is_archived' => "TINYINT(1) DEFAULT 0 AFTER application_status"
    ];

    // Get current columns
    $stmt = $pdo->query("DESCRIBE applications");
    $existing_columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($required_columns as $col => $definition) {
        if (!in_array($col, $existing_columns)) {
            echo "   Column '$col' is missing. Migrating... ";
            try {
                $pdo->exec("ALTER TABLE applications ADD COLUMN $col $definition");
                echo "SUCCESS\n";
            } catch (PDOException $e) {
                echo "FAILED: " . $e->getMessage() . "\n";
            }
        } else {
            echo "   Column '$col' verified.\n";
        }
    }

    // 3. Check ENUM values for safety
    echo "\n3. Verifying application_status ENUM... ";
    $stmt = $pdo->query("DESCRIBE applications 'application_status'");
    $status_info = $stmt->fetch();
    // Simply ensuring 'Review' and others are there if it's an old table
    if (strpos($status_info['Type'], 'Review') === false) {
        echo "Outdated. Updating... ";
        $pdo->exec("ALTER TABLE applications MODIFY COLUMN application_status ENUM('Pending', 'Review', 'Interview', 'Accepted', 'Rejected') DEFAULT 'Pending'");
        echo "SUCCESS\n";
    }

    // 4. Check Settings Table
    echo "\n4. Checking 'settings' table... ";
    $stmt = $pdo->query("SHOW TABLES LIKE 'settings'");
    if ($stmt->rowCount() == 0) {
        echo "NOT FOUND\n   Creating table... ";
        $sql = "CREATE TABLE settings (
            setting_key VARCHAR(50) PRIMARY KEY,
            setting_value TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
        
        // Insert default values
        $stmtInsert = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
        $stmtInsert->execute(['registration_status', 'open']);
        $stmtInsert->execute(['close_date', '']);
        echo "SUCCESS\n";
    } else {
        echo "EXISTS\n";
        // Ensure default records exist
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = ?");
        
        $stmtCheck->execute(['registration_status']);
        if ($stmtCheck->fetchColumn() == 0) {
            $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('registration_status', 'open')")->execute();
        }
        
        $stmtCheck->execute(['close_date']);
        if ($stmtCheck->fetchColumn() == 0) {
            $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('close_date', '')")->execute();
        }
        echo "   Settings records verified.\n";
    }

    echo "\nVerification Complete. Database is ready for use.\n";

} catch (PDOException $e) {
    echo "\nCRITICAL ERROR: " . $e->getMessage() . "\n";
    echo "Check your config.php and MySQL service status.\n";
}
?>
