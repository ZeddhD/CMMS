<?php
$host = 'localhost';
$db   = 'course_manager';
$user = 'root';       // default XAMPP user
$pass = '';           // default XAMPP password is empty
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // echo "Connected successfully"; // Optional for debugging
} catch (\PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
?>
