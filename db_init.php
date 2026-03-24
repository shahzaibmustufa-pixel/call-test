<?php
// Database Initialization Script

$host = 'localhost';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `admin_panel_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `admin_panel_db`");

    // Read SQL file
    $sql = file_get_contents(__DIR__ . '/database.sql');
    
    // Execute SQL
    $pdo->exec($sql);

    echo "Database and tables created successfully!\n";
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
