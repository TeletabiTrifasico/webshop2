<?php
header('Content-Type: application/json');

// Load necessary files for testing
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Models/Model.php';
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
    
    // Initialize product model
    $productModel = new App\Models\Product();
    
    // Get all products
    $products = $productModel->getAll(1, 10);
    
    // Test product operations
    $testResults = [
        'database_connection' => true,
        'products_found' => count($products),
        'product_details' => array_map(function($p) {
            return [
                'id' => $p['id'],
                'name' => $p['name'],
                'price' => $p['price'],
                'image' => $p['image']
            ];
        }, $products)
    ];
    
    // Test update function on a product
    if (!empty($products)) {
        $testProduct = $products[0];
        $testProductId = $testProduct['id'];
        
        $updatedData = [
            'name' => $testProduct['name'] . ' (Updated)',
            'description' => $testProduct['description'],
            'price' => $testProduct['price'],
            'image' => $testProduct['image']
        ];
        
        $updateResult = $productModel->update($testProductId, $updatedData);
        $testResults['update_test'] = [
            'success' => $updateResult,
            'product_id' => $testProductId,
            'data_sent' => $updatedData
        ];
        
        // Verify update
        if ($updateResult) {
            $updatedProduct = $productModel->findById($testProductId);
            $testResults['update_verification'] = [
                'product_found' => !!$updatedProduct,
                'name_updated' => $updatedProduct ? $updatedProduct['name'] === $updatedData['name'] : false,
                'image_preserved' => $updatedProduct ? $updatedProduct['image'] === $updatedData['image'] : false,
                'updated_product' => $updatedProduct
            ];
        }
    }
    
    // Response with test results
    $response = [
        'success' => true,
        'message' => 'Product operations test completed',
        'test_results' => $testResults
    ];
    
} catch (PDOException $e) {
    $response = [
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ];
} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => 'General error: ' . $e->getMessage()
    ];
}

echo json_encode($response, JSON_PRETTY_PRINT);