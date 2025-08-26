<?php
/**
 * Database Update Script for PT. Visdat Teknik Utama Recruitment System
 * 
 * This script adds KTP and Ijazah file columns to the existing applications table.
 * Run this script if you already have an existing applications table.
 * 
 * @author System Administrator
 * @version 1.0
 * @date 2025
 */

// Include configuration file
require_once 'config.php';

// Error reporting for debugging
if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Update - PT. Visdat Teknik Utama</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .info { color: #17a2b8; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class='container mt-4'>
        <div class='row justify-content-center'>
            <div class='col-md-8'>
                <div class='card shadow'>
                    <div class='card-header bg-primary text-white'>
                        <h3 class='mb-0'>
                            <i class='fas fa-database'></i> Database Update Script
                        </h3>
                        <p class='mb-0'>PT. Visdat Teknik Utama Recruitment System</p>
                    </div>
                    <div class='card-body'>";

try {
    // Create database connection using config values
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div class='alert alert-success'>
            <i class='fas fa-check-circle'></i> <strong>Database connection successful!</strong>
            <br>Connected to: <code>" . DB_NAME . "</code> on <code>" . DB_HOST . "</code>
          </div>";
    
    // Check if the applications table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'applications'");
    if ($stmt->rowCount() == 0) {
        echo "<div class='alert alert-warning'>
                <i class='fas fa-exclamation-triangle'></i> <strong>Warning:</strong> 
                The 'applications' table does not exist. Please create it first or check the table name.
              </div>";
        exit;
    }
    
    echo "<div class='alert alert-info'>
            <i class='fas fa-info-circle'></i> <strong>Table found:</strong> 'applications' table exists
          </div>";
    
    // Check current table structure
    echo "<h5><i class='fas fa-table'></i> Current Table Structure:</h5>";
    $stmt = $pdo->query("DESCRIBE applications");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div class='table-responsive'>
            <table class='table table-sm table-bordered'>
                <thead class='table-light'>
                    <tr>
                        <th>Field</th>
                        <th>Type</th>
                        <th>Null</th>
                        <th>Key</th>
                        <th>Default</th>
                        <th>Extra</th>
                    </tr>
                </thead>
                <tbody>";
    
    foreach ($columns as $column) {
        $rowClass = '';
        if (in_array($column['Field'], ['ktp_file', 'ijazah_file'])) {
            $rowClass = 'table-success';
        }
        
        echo "<tr class='$rowClass'>
                <td><strong>{$column['Field']}</strong></td>
                <td>{$column['Type']}</td>
                <td>{$column['Null']}</td>
                <td>{$column['Key']}</td>
                <td>{$column['Default']}</td>
                <td>{$column['Extra']}</td>
              </tr>";
    }
    
    echo "</tbody></table></div>";
    
    // Check if KTP and Ijazah columns already exist
    $ktpExists = false;
    $ijazahExists = false;
    
    foreach ($columns as $column) {
        if ($column['Field'] === 'ktp_file') $ktpExists = true;
        if ($column['Field'] === 'ijazah_file') $ijazahExists = true;
    }
    
    if ($ktpExists && $ijazahExists) {
        echo "<div class='alert alert-success'>
                <i class='fas fa-check-circle'></i> <strong>Update not needed:</strong> 
                Both 'ktp_file' and 'ijazah_file' columns already exist in the table.
              </div>";
    } else {
        echo "<h5><i class='fas fa-plus-circle'></i> Adding New Columns:</h5>";
        
        // Add KTP file column if it doesn't exist
        if (!$ktpExists) {
            try {
                $sql = "ALTER TABLE applications ADD COLUMN ktp_file VARCHAR(255) AFTER photo_file";
                $pdo->exec($sql);
                echo "<div class='alert alert-success'>
                        <i class='fas fa-check-circle'></i> <strong>Success:</strong> 
                        Added 'ktp_file' column after 'photo_file'
                      </div>";
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>
                        <i class='fas fa-times-circle'></i> <strong>Error adding ktp_file column:</strong> 
                        " . htmlspecialchars($e->getMessage()) . "
                      </div>";
            }
        } else {
            echo "<div class='alert alert-info'>
                    <i class='fas fa-info-circle'></i> <strong>Info:</strong> 
                    'ktp_file' column already exists
                  </div>";
        }
        
        // Add Ijazah file column if it doesn't exist
        if (!$ijazahExists) {
            try {
                $sql = "ALTER TABLE applications ADD COLUMN ijazah_file VARCHAR(255) AFTER ktp_file";
                $pdo->exec($sql);
                echo "<div class='alert alert-success'>
                        <i class='fas fa-check-circle'></i> <strong>Success:</strong> 
                        Added 'ijazah_file' column after 'ktp_file'
                      </div>";
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>
                        <i class='fas fa-times-circle'></i> <strong>Error adding ijazah_file column:</strong> 
                        " . htmlspecialchars($e->getMessage()) . "
                      </div>";
            }
        } else {
            echo "<div class='alert alert-info'>
                    <i class='fas fa-info-circle'></i> <strong>Info:</strong> 
                    'ijazah_file' column already exists
                  </div>";
        }
        
        // Verify the new columns were added
        echo "<h5><i class='fas fa-check-double'></i> Verification - Updated Table Structure:</h5>";
        $stmt = $pdo->query("DESCRIBE applications");
        $updatedColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<div class='table-responsive'>
                <table class='table table-sm table-bordered'>
                    <thead class='table-light'>
                        <tr>
                            <th>Field</th>
                            <th>Type</th>
                            <th>Null</th>
                            <th>Key</th>
                            <th>Default</th>
                            <th>Extra</th>
                        </tr>
                    </thead>
                    <tbody>";
        
        foreach ($updatedColumns as $column) {
            $rowClass = '';
            if (in_array($column['Field'], ['ktp_file', 'ijazah_file'])) {
                $rowClass = 'table-success';
            }
            
            echo "<tr class='$rowClass'>
                    <td><strong>{$column['Field']}</strong></td>
                    <td>{$column['Type']}</td>
                    <td>{$column['Null']}</td>
                    <td>{$column['Key']}</td>
                    <td>{$column['Default']}</td>
                    <td>{$column['Extra']}</td>
                  </tr>";
        }
        
        echo "</tbody></table></div>";
    }
    
    // Show sample data if table has records
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM applications");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($count['total'] > 0) {
        echo "<div class='alert alert-info'>
                <i class='fas fa-info-circle'></i> <strong>Data:</strong> 
                The table contains {$count['total']} record(s)
              </div>";
        
        // Show sample record structure
        $stmt = $pdo->query("SELECT * FROM applications LIMIT 1");
        $sample = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($sample) {
            echo "<h5><i class='fas fa-eye'></i> Sample Record Structure:</h5>";
            echo "<pre>" . htmlspecialchars(json_encode($sample, JSON_PRETTY_PRINT)) . "</pre>";
        }
    } else {
        echo "<div class='alert alert-warning'>
                <i class='fas fa-exclamation-triangle'></i> <strong>Note:</strong> 
                The table is currently empty (no records)
              </div>";
    }
    
    echo "<div class='alert alert-success mt-3'>
            <i class='fas fa-check-circle'></i> <strong>Database update completed successfully!</strong>
            <br>The 'applications' table now supports KTP and Ijazah file uploads.
          </div>";
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>
            <i class='fas fa-times-circle'></i> <strong>Database Error:</strong> 
            " . htmlspecialchars($e->getMessage()) . "
          </div>";
    
    echo "<div class='alert alert-warning'>
            <i class='fas fa-lightbulb'></i> <strong>Troubleshooting Tips:</strong>
            <ul class='mb-0'>
                <li>Check your database connection settings</li>
                <li>Ensure the database '" . DB_NAME . "' exists</li>
                <li>Verify username and password are correct</li>
                <li>Make sure MySQL service is running</li>
                <li>Check if you have ALTER TABLE privileges</li>
            </ul>
          </div>";
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>
            <i class='fas fa-times-circle'></i> <strong>General Error:</strong> 
            " . htmlspecialchars($e->getMessage()) . "
          </div>";
}

echo "</div>
        </div>
    </div>
</div>

<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js'></script>
</body>
</html>";
?>
