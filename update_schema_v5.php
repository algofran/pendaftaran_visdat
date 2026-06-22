<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

try {
    echo "Updating database schema...<br>\n";

    // Add work_experiences column as JSON text
    $stmt = $pdo->prepare("ALTER TABLE applications ADD COLUMN work_experiences TEXT NULL");
    $stmt->execute();
    echo "Column 'work_experiences' added to applications table.<br>\n";

    echo "Update complete.<br>\n";

} catch (Exception $e) {
    if (strpos($e->getMessage(), 'duplicate column name') !== false || strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column already exists.<br>\n";
    } else {
        echo "Error: " . $e->getMessage() . "<br>\n";
    }
}
?>
