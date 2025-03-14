<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Autoload classes
spl_autoload_register(function ($class) {
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file = __DIR__ . '/../' . $class . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Start session
session_start();

// Basic routing
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
$path = rtrim($path, '/');

try {
    switch ($path) {
        case '':
        case '/':
            $controller = new \App\Controllers\HomeController();
            $controller->index();
            break;
            
        case '/products':
            $controller = new \App\Controllers\ProductController();
            $controller->index();
            break;
            
        case '/auth/login':
            $controller = new \App\Controllers\AuthController();
            $controller->login();
            break;
            
        case '/auth/register':
            $controller = new \App\Controllers\AuthController();
            $controller->register();
            break;
            
        case '/auth/logout':
            $controller = new \App\Controllers\AuthController();
            $controller->logout();
            break;
            
        case '/cart':
            $controller = new \App\Controllers\CartController();
            $controller->index();
            break;
            
        case '/cart/add':
            $controller = new \App\Controllers\CartController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : null;
                $controller->addItem($productId);
            } else {
                header('Location: /products');
            }
            break;

        case '/cart/update':
            $controller = new \App\Controllers\CartController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : null;
                $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : null;
                $controller->updateQuantity($productId, $quantity);
            }
            break;

        case '/cart/remove':
            $controller = new \App\Controllers\CartController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : null;
                $controller->removeItem($productId);
            }
            break;
            
        default:
            if (preg_match('/^\/products\/(\d+)$/', $path, $matches)) {
                $controller = new \App\Controllers\ProductController();
                $controller->detail($matches[1]);
                break;
            }
            
            http_response_code(404);
            require __DIR__ . '/../app/views/404.php';
            break;
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo "An error occurred. Please try again later.";
}
?>