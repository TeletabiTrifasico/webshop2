<?php
// Include configuration and database connection
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Models/Model.php';
require_once __DIR__ . '/../app/Models/Cart.php';
require_once __DIR__ . '/../app/Models/Product.php';

// Test database connection
echo "<h1>Cart Functionality Debug</h1>";

try {
    // Use environment variables
    $host = getenv('DB_HOST') ?: 'db';
    $dbname = getenv('DB_NAME') ?: 'webshop_db';
    $username = getenv('DB_USER') ?: 'webshopadmin';
    $password = getenv('DB_PASSWORD') ?: '!webshopadmin2025';
    
    echo "<p>Attempting connection to: $host, $dbname, $username</p>";
    
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "<p style='color:green'>Database connection successful!</p>";
    
    // Initialize cart and product models
    $cart = new \App\Models\Cart();
    $product = new \App\Models\Product();
    
    // Test getting products
    echo "<h2>Products</h2>";
    $products = $product->getLatest(5);
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>Price</th></tr>";
    foreach ($products as $p) {
        echo "<tr><td>{$p['id']}</td><td>{$p['name']}</td><td>\${$p['price']}</td></tr>";
    }
    echo "</table>";
    
    // Test cart functionality with user ID 1
    $userId = 1;
    
    // Get existing cart items
    echo "<h2>Current Cart Items for User $userId</h2>";
    $cartItems = $cart->getCartItems($userId);
    
    if (empty($cartItems)) {
        echo "<p>Cart is empty</p>";
    } else {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Item ID</th><th>Product</th><th>Quantity</th><th>Price</th><th>Total</th></tr>";
        foreach ($cartItems as $item) {
            $total = $item['quantity'] * $item['price'];
            echo "<tr><td>{$item['id']}</td><td>{$item['name']}</td><td>{$item['quantity']}</td><td>\${$item['price']}</td><td>\${$total}</td></tr>";
        }
        echo "</table>";
        
        // Test cart total
        $cartTotal = $cart->getTotal($userId);
        echo "<p><strong>Cart Total:</strong> \${$cartTotal}</p>";
    }
    
    // Test add item if products exist
    if (!empty($products)) {
        $testProduct = $products[0];
        echo "<h2>Test Add Item</h2>";
        echo "<p>Adding product ID {$testProduct['id']} to cart...</p>";
        
        $result = $cart->addItem($userId, $testProduct['id'], 1);
        if ($result) {
            echo "<p style='color:green'>Successfully added item to cart!</p>";
            
            // Show updated cart
            $updatedCart = $cart->getCartItems($userId);
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Item ID</th><th>Product</th><th>Quantity</th><th>Price</th></tr>";
            foreach ($updatedCart as $item) {
                echo "<tr><td>{$item['id']}</td><td>{$item['name']}</td><td>{$item['quantity']}</td><td>\${$item['price']}</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color:red'>Failed to add item to cart</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Database Error: " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>General Error: " . $e->getMessage() . "</p>";
}