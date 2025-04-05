<?php
header('Content-Type: application/json');

// Load necessary files for testing
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Models/Model.php';
require_once __DIR__ . '/../app/Models/Order.php';
require_once __DIR__ . '/../app/Models/OrderItem.php';
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
    
    // Check if orders table exists
    $tables = $pdo->query("SHOW TABLES LIKE 'orders'")->fetchAll();
    $orderTableExists = count($tables) > 0;
    
    $orderTableStructure = [];
    if ($orderTableExists) {
        $orderTableStructure = $pdo->query("DESCRIBE orders")->fetchAll(PDO::FETCH_COLUMN, 0);
    }
    
    // Check if order_items table exists
    $tables = $pdo->query("SHOW TABLES LIKE 'order_items'")->fetchAll();
    $orderItemsTableExists = count($tables) > 0;
    
    $orderItemsTableStructure = [];
    if ($orderItemsTableExists) {
        $orderItemsTableStructure = $pdo->query("DESCRIBE order_items")->fetchAll(PDO::FETCH_COLUMN, 0);
    }
    
    $response = [
        'success' => true,
        'database' => [
            'connected' => true,
            'host' => $host,
            'name' => $dbname
        ],
        'tables' => [
            'orders' => [
                'exists' => $orderTableExists,
                'columns' => $orderTableStructure
            ],
            'order_items' => [
                'exists' => $orderItemsTableExists,
                'columns' => $orderItemsTableStructure
            ]
        ]
    ];
    
    // Test order creation if tables exist
    if ($orderTableExists && $orderItemsTableExists) {
        $orderModel = new App\Models\Order();
        $orderItemModel = new App\Models\OrderItem();
        
        // Check if we can create a test order
        $testOrderId = $orderModel->create([
            'user_id' => 1,
            'total_amount' => 99.99,
            'status' => 'pending'
        ]);
        
        if ($testOrderId) {
            $response['test_order'] = [
                'success' => true,
                'order_id' => $testOrderId
            ];
            
            // Add a test order item
            $testOrderItemId = $orderItemModel->create([
                'order_id' => $testOrderId,
                'product_id' => 1,
                'quantity' => 1,
                'price' => 99.99
            ]);
            
            if ($testOrderItemId) {
                $response['test_order']['order_item'] = [
                    'success' => true,
                    'order_item_id' => $testOrderItemId
                ];
            } else {
                $response['test_order']['order_item'] = [
                    'success' => false,
                    'error' => 'Failed to create test order item'
                ];
            }
        } else {
            $response['test_order'] = [
                'success' => false,
                'error' => 'Failed to create test order'
            ];
        }
    }
    
} catch (PDOException $e) {
    $response = [
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage(),
        'database' => [
            'host' => $host ?? 'unknown',
            'name' => $dbname ?? 'unknown'
        ]
    ];
} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => 'General error: ' . $e->getMessage()
    ];
}

echo json_encode($response, JSON_PRETTY_PRINT);