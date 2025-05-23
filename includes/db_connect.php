<?php
// db_connect.php

$host = 'localhost'; // Or your database host
$dbname = 'imdb_clone';
$user = 'root'; // Your database username
$password = 'admin'; // Your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connected successfully"; // Uncomment for testing
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>