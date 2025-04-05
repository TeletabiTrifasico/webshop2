<?php
header('Content-Type: application/json');

// Load necessary files for testing
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Models/Model.php';
require_once __DIR__ . '/../app/Models/User.php';
require_once __DIR__ . '/../app/Models/Product.php';
require_once __DIR__ . '/../app/Utils/JWT.php';
require_once __DIR__ . '/../app/Middleware/JwtMiddleware.php';

// Test database connection
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
    
    // Initialize models
    $userModel = new App\Models\User();
    $productModel = new App\Models\Product();
    
    // Test user model functions
    $testResults = [
        'database_connection' => true,
        'models' => [
            'user' => [
                'findById' => false,
                'findByEmail' => false,
                'getAll' => false
            ],
            'product' => [
                'findById' => false,
                'getAll' => false
            ]
        ]
    ];
    
    // Test User::findById
    try {
        $user = $userModel->findById(1);
        $testResults['models']['user']['findById'] = [
            'success' => true,
            'result' => $user ? 'Found user' : 'User not found',
            'data' => $user ? ['id' => $user['id'], 'username' => $user['username']] : null
        ];
    } catch (Exception $e) {
        $testResults['models']['user']['findById'] = [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
    
    // Test User::findByEmail
    try {
        $user = $userModel->findByEmail('admin@webshop.com');
        $testResults['models']['user']['findByEmail'] = [
            'success' => true,
            'result' => $user ? 'Found user' : 'User not found',
            'data' => $user ? ['id' => $user['id'], 'username' => $user['username']] : null
        ];
    } catch (Exception $e) {
        $testResults['models']['user']['findByEmail'] = [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
    
    // Test User::getAll
    try {
        $users = $userModel->getAll(1, 5);
        $testResults['models']['user']['getAll'] = [
            'success' => true,
            'result' => count($users) . ' users found',
            'data' => array_map(function($user) {
                return ['id' => $user['id'], 'username' => $user['username']];
            }, $users)
        ];
    } catch (Exception $e) {
        $testResults['models']['user']['getAll'] = [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
    
    // Test Product::findById
    try {
        $product = $productModel->findById(1);
        $testResults['models']['product']['findById'] = [
            'success' => true,
            'result' => $product ? 'Found product' : 'Product not found',
            'data' => $product ? ['id' => $product['id'], 'name' => $product['name']] : null
        ];
    } catch (Exception $e) {
        $testResults['models']['product']['findById'] = [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
    
    // Test Product::getAll
    try {
        $products = $productModel->getAll(1, 5);
        $testResults['models']['product']['getAll'] = [
            'success' => true,
            'result' => count($products) . ' products found',
            'data' => array_map(function($product) {
                return ['id' => $product['id'], 'name' => $product['name']];
            }, $products)
        ];
    } catch (Exception $e) {
        $testResults['models']['product']['getAll'] = [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
    
    // Get database structure
    $tables = [
        'users' => [],
        'products' => [],
        'orders' => [],
        'order_items' => [],
        'cart_items' => []
    ];
    
    foreach (array_keys($tables) as $table) {
        try {
            $stmt = $pdo->query("DESCRIBE $table");
            $tables[$table] = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        } catch (Exception $e) {
            $tables[$table] = ['error' => $e->getMessage()];
        }
    }
    
    // Format the response
    $response = [
        'success' => true,
        'database' => [
            'host' => $host,
            'name' => $dbname,
            'connected' => true
        ],
        'tables' => $tables,
        'test_results' => $testResults
    ];
    
} catch (PDOException $e) {
    $response = [
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage(),
        'database' => [
            'host' => $host ?? 'unknown',
            'name' => $dbname ?? 'unknown',
            'connected' => false
        ]
    ];
} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => 'General error: ' . $e->getMessage()
    ];
}

// Output the response
echo json_encode($response, JSON_PRETTY_PRINT);