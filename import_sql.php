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

// Clear the database before import
echo "Clearing existing tables...<br>";
$conn->query("SET FOREIGN_KEY_CHECKS = 0;");
if ($result = $conn->query("SHOW TABLES")) {
    while ($row = $result->fetch_array()) {
        $conn->query("DROP TABLE IF EXISTS `" . $row[0] . "`;");
    }
}
$conn->query("SET FOREIGN_KEY_CHECKS = 1;");

$filename = __DIR__ . '/shop.sql';

if (!file_exists($filename)) {
    die("Error: shop.sql not found at " . $filename);
}

echo "Reading shop.sql...<br>";
$sql = file_get_contents($filename);

if ($sql === false) {
    die("Error: Could not read shop.sql");
}

echo "Executing SQL queries (Size: " . round(strlen($sql) / 1024 / 1024, 2) . " MB)...<br>";

// Disable foreign key checks for the import
$conn->query("SET FOREIGN_KEY_CHECKS = 0;");

if ($conn->multi_query($sql)) {
    $count = 0;
    do {
        $count++;
        // Need to consume results
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());

    if ($conn->error) {
        echo "<br><b style='color:red;'>SQL Error during import:</b> " . $conn->error;
    } else {
        echo "<br><b style='color:green;'>SUCCESS: Data imported successfully!</b> ($count queries processed)";
    }
} else {
    echo "<br><b style='color:red;'>Error executing multi-query:</b> " . $conn->error;
}

$conn->query("SET FOREIGN_KEY_CHECKS = 1;");
$conn->close();

echo "<br><br><b>Done. Please delete this script (public/import_sql.php) when finished.</b>";
