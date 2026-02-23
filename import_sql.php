<?php
// Prevent unauthorized access (optional, but good for production)
// if (isset($_GET['key']) && $_GET['key'] !== 'safe123') { die('Access denied'); }

$host = getenv('DB_HOST') ?: 'mysql.railway.internal';
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: 'FSLjLYbxXEvnJSRSNDtpKqfVPTFJjFTM';
$db = getenv('DB_DATABASE') ?: 'railway';
$port = getenv('DB_PORT') ?: '3306';

echo "<h3>Database Import Script</h3>";
echo "Connecting to: $host:$port ($db)...<br>";

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$filename = __DIR__ . '/shop.sql';

// 1. Drop tables mentioned in the SQL file more aggressively
echo "Scanning shop.sql for tables to drop...<br>";
$sql = file_get_contents($filename);
if ($sql === false) {
    die("Error: Could not read shop.sql");
}

preg_match_all("/CREATE TABLE `([^`]+)`/i", $sql, $matches);
if (!empty($matches[1])) {
    $conn->query("SET FOREIGN_KEY_CHECKS = 0;");
    foreach (array_unique($matches[1]) as $tableName) {
        if (!$conn->query("DROP TABLE IF EXISTS `$tableName`")) {
            echo "Warning: Could not drop $tableName: " . $conn->error . "<br>";
        }
    }
    $conn->query("SET FOREIGN_KEY_CHECKS = 1;");
}

echo "Executing SQL queries (Size: " . round(strlen($sql) / 1024 / 1024, 2) . " MB)...<br>";

$conn->query("SET FOREIGN_KEY_CHECKS = 0;");
$conn->query("SET NAMES 'utf8mb4';");

// Use try-catch for PHP 8+ mysqli exceptions
try {
    if ($conn->multi_query($sql)) {
        $count = 0;
        do {
            $count++;
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->next_result());

        echo "<br><b style='color:green;'>SUCCESS: Data imported successfully!</b> ($count blocks processed)";
    } else {
        echo "<br><b style='color:red;'>Error:</b> " . $conn->error;
    }
} catch (mysqli_sql_exception $e) {
    echo "<br><b style='color:red;'>Fatal Error during SQL execution:</b> " . $e->getMessage();
    echo "<br><i>Check if the database user has sufficient permissions.</i>";
}

$conn->query("SET FOREIGN_KEY_CHECKS = 1;");
$conn->close();

echo "<br><br><b>Done. Please delete this script (public/import_sql.php) when finished.</b>";
