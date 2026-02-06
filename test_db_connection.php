<?php
require_once 'config.php';

try {
    echo "Testing connection to: " . DB_FILE . "\n";
    if (file_exists(DB_FILE)) {
        echo "File exists.\n";
    } else {
        echo "File does NOT exist.\n";
    }
    
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    echo "Connection successful. Tables: " . implode(', ', $tables) . "\n";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
?>
