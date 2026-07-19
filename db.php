<?php
session_start();

$DB_SERVER = "localhost";
$DB_USERNAME = "root";
$DB_PASSWORD = "";
$DB_NAME = "user";

function db_connect() {
    global $DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_NAME;

    $conn = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $databaseExists = $conn->select_db($DB_NAME);
    if (!$databaseExists) {
        $createDbSql = "CREATE DATABASE IF NOT EXISTS `$DB_NAME` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        if (!$conn->query($createDbSql)) {
            die("Failed to create database: " . $conn->error);
        }
        $conn->select_db($DB_NAME);
    }

    $conn->set_charset("utf8mb4");
    initialize_database($conn);
    return $conn;
}

function initialize_database($conn) {
    $conn->query("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $conn->query("CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        sender ENUM('user','admin') NOT NULL,
        content TEXT NOT NULL,
        is_read_by_user TINYINT(1) NOT NULL DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}
