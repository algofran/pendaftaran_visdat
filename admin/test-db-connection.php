<?php
// Test database connection script for production debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Test</h2>";

// Test 1: Check if config file exists and is readable
echo "<h3>1. Testing config file...</h3>";
$configPath = '../config.php';
if (file_exists($configPath)) {
    echo "✅ Config file exists<br>";
    if (is_readable($configPath)) {
        echo "✅ Config file is readable<br>";
    } else {
        echo "❌ Config file is not readable<br>";
    }
} else {
    echo "❌ Config file does not exist at: " . realpath($configPath) . "<br>";
}

// Test 2: Include config and check constants
echo "<h3>2. Testing config constants...</h3>";
try {
    require_once $configPath;
    echo "✅ Config included successfully<br>";
    
    $constants = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'];
    foreach ($constants as $const) {
        if (defined($const)) {
            $value = constant($const);
            if ($const === 'DB_PASS') {
                $value = str_repeat('*', strlen($value)); // Hide password
            }
            echo "✅ $const: $value<br>";
        } else {
            echo "❌ $const not defined<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Error including config: " . $e->getMessage() . "<br>";
}

// Test 3: Test database connection
echo "<h3>3. Testing database connection...</h3>";
try {
    $testPdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $testPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connection successful<br>";
    
    // Test 4: Check if applications table exists
    echo "<h3>4. Testing applications table...</h3>";
    $tableCheck = $testPdo->query("SHOW TABLES LIKE 'applications'");
    if ($tableCheck->rowCount() > 0) {
        echo "✅ Applications table exists<br>";
        
        // Test 5: Count records
        $countStmt = $testPdo->query("SELECT COUNT(*) as count FROM applications");
        $count = $countStmt->fetch()['count'];
        echo "✅ Applications table has $count records<br>";
        
        // Test 6: Check table structure
        echo "<h3>5. Testing table structure...</h3>";
        $columnsStmt = $testPdo->query("DESCRIBE applications");
        $columns = $columnsStmt->fetchAll();
        echo "✅ Table has " . count($columns) . " columns:<br>";
        foreach ($columns as $column) {
            echo "  - " . $column['Field'] . " (" . $column['Type'] . ")<br>";
        }
        
        // Test 7: Test ROW_NUMBER() function (MySQL 8.0+ feature)
        echo "<h3>6. Testing ROW_NUMBER() function...</h3>";
        try {
            $rowNumberTest = $testPdo->query("SELECT ROW_NUMBER() OVER (ORDER BY id) as rn FROM applications LIMIT 1");
            if ($rowNumberTest->rowCount() > 0) {
                echo "✅ ROW_NUMBER() function works<br>";
            } else {
                echo "⚠️ ROW_NUMBER() function works but no data to test<br>";
            }
        } catch (PDOException $e) {
            echo "❌ ROW_NUMBER() function not supported: " . $e->getMessage() . "<br>";
            echo "💡 This might be the issue! ROW_NUMBER() requires MySQL 8.0+<br>";
        }
        
    } else {
        echo "❌ Applications table does not exist<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}

// Test 8: Check MySQL version
echo "<h3>7. Testing MySQL version...</h3>";
try {
    if (isset($testPdo)) {
        $versionStmt = $testPdo->query("SELECT VERSION() as version");
        $version = $versionStmt->fetch()['version'];
        echo "✅ MySQL version: $version<br>";
        
        // Check if version supports ROW_NUMBER()
        $versionNumber = floatval($version);
        if ($versionNumber >= 8.0) {
            echo "✅ MySQL version supports ROW_NUMBER()<br>";
        } else {
            echo "❌ MySQL version does not support ROW_NUMBER() - requires 8.0+<br>";
            echo "💡 Current version: $version<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Could not check MySQL version: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<p><strong>Summary:</strong> Check the results above to identify the root cause of the export issue.</p>";
?>