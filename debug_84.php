<?php
// Simple debug script for PHP 8.4 upgrade
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Environment Debug (PHP 8.4)</h1>";
echo "PHP Version: " . phpversion() . "<br>";

// Check for Laravel bootstrap
if (file_exists(__DIR__ . '/bootstrap/app.php')) {
    echo "Laravel bootstrap file found.<br>";
} else {
    echo "ERROR: bootstrap/app.php NOT FOUND!<br>";
}

// Check vendor
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "Vendor autoload found.<br>";
} else {
    echo "ERROR: vendor/autoload.php NOT FOUND!<br>";
}

// Check DB variables
echo "<h2>Database Variables:</h2>";
echo "DB_HOST: " . getenv('DB_HOST') . "<br>";
echo "DB_DATABASE: " . getenv('DB_DATABASE') . "<br>";
echo "DB_USERNAME: " . getenv('DB_USERNAME') . "<br>";

echo "<h2>Testing DB Connection...</h2>";
try {
    $host = getenv('DB_HOST');
    $port = getenv('DB_PORT') ?: '3306';
    $db = getenv('DB_DATABASE');
    $user = getenv('DB_USERNAME');
    $pass = getenv('DB_PASSWORD');

    $dsn = "mysql:host=$host;port=$port;dbname=$db";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "SUCCESS: Database connected!<br>";
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "<br>";
}
