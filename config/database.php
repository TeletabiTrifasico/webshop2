<?php

$host = getenv('DB_HOST') ?: 'db'; 
$dbname = getenv('DB_NAME') ?: 'webshop_db';
$username = getenv('DB_USER') ?: 'webshopadmin';
$password = getenv('DB_PASSWORD') ?: '!webshopadmin2025';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}