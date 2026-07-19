<?php
header('Content-Type: text/plain; charset=utf-8');

$host = 'localhost';
$user = 'root';
$pass = '';
$dbName = 'user';

$mysqli = new mysqli($host, $user, $pass);
if ($mysqli->connect_error) {
    echo "MySQL connection failed: " . $mysqli->connect_error . "\n";
    exit;
}

echo "Connected to MySQL server successfully.\n";

if (!$mysqli->select_db($dbName)) {
    echo "Database '$dbName' does not exist or cannot be selected.\n";
    echo "Available databases:\n";
    $result = $mysqli->query('SHOW DATABASES');
    while ($row = $result->fetch_assoc()) {
        echo " - " . $row['Database'] . "\n";
    }
    exit;
}

echo "Database '$dbName' exists and is selected.\n";

$tables = ['users', 'messages'];
foreach ($tables as $table) {
    $result = $mysqli->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "Table '$table' exists.\n";
    } else {
        echo "Table '$table' is missing.\n";
    }
}

$mysqli->close();
