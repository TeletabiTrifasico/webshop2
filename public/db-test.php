<?php
// Simple database connection test
$host = getenv('DB_HOST') ?: 'db';
$dbname = getenv('DB_NAME') ?: 'webshop_db';
$username = getenv('DB_USER') ?: 'webshopadmin';
$password = getenv('DB_PASSWORD') ?: '!webshopadmin2025';

echo "<h1>Database Connection Test</h1>";
echo "<p>Attempting to connect to database with:</p>";
echo "<ul>";
echo "<li>Host: $host</li>";
echo "<li>Database: $dbname</li>";
echo "<li>Username: $username</li>";
echo "</ul>";

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "<p style='color: green;'>✅ Connection successful!</p>";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT * FROM products LIMIT 5");
    $products = $stmt->fetchAll();
    
    echo "<h2>Products from Database:</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>Price</th></tr>";
    
    foreach ($products as $product) {
        echo "<tr>";
        echo "<td>{$product['id']}</td>";
        echo "<td>{$product['name']}</td>";
        echo "<td>\${$product['price']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Connection failed: " . $e->getMessage() . "</p>";
}