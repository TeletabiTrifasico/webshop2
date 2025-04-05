<?php
header('Content-Type: application/json');

// Load necessary files for testing
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Models/Model.php';
require_once __DIR__ . '/../app/Models/Cart.php';
require_once __DIR__ . '/../app/Models/Product.php';

// Initialize models and connection
try {
    // Use environment variables
    $host = getenv('DB_HOST') ?: 'db';
    $dbname = getenv('DB_NAME') ?: 'webshop_db';
    $username = getenv('DB_USER') ?: 'webshopadmin';
    $password = getenv('DB_PASSWORD') ?: '!webshopadmin2025';
    
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Check if cart_items table exists
    $tables = $pdo->query("SHOW TABLES LIKE 'cart_items'")->fetchAll();
    $tableExists = count($tables) > 0;
    
    if (!$tableExists) {
        // Create cart_items table if it doesn't exist
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS cart_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                product_id INT NOT NULL,
                quantity INT NOT NULL DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        $tableStatus = "Table 'cart_items' created successfully";
    } else {
        $tableStatus = "Table 'cart_items' already exists";
    }
    
    // Check if it has the right structure
    $columns = $pdo->query("SHOW COLUMNS FROM cart_items")->fetchAll(PDO::FETCH_COLUMN, 0);
    
    $response = [
        'success' => true,
        'database' => [
            'connected' => true,
            'host' => $host,
            'name' => $dbname,
            'username' => $username
        ],
        'tables' => [
            'cart_items' => $tableStatus,
            'columns' => $columns
        ],
        'message' => 'API debug completed successfully'
    ];
    
} catch (PDOException $e) {
    $response = [
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage(),
        'database' => [
            'host' => $host ?? 'unknown',
            'name' => $dbname ?? 'unknown',
            'username' => $username ?? 'unknown'
        ]
    ];
} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => 'General error: ' . $e->getMessage()
    ];
}

echo json_encode($response, JSON_PRETTY_PRINT);