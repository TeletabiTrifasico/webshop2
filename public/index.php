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

        case '/cart/checkout':
            $controller = new \App\Controllers\CartController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->processCheckout();
            }
            break;

        // API Routes
        case '/api/products':
            $controller = new \App\Controllers\ApiController();
            $controller->products();
            break;

        case (preg_match('/^\/api\/products\/(\d+)$/', $path, $matches) ? true : false):
            $controller = new \App\Controllers\ApiController();
            $controller->product($matches[1]);
            break;

        case '/api/cart':
            $controller = new \App\Controllers\ApiController();
            $controller->cart();
            break;

        // Admin Routes
        case '/admin':
        case '/admin/dashboard':
            $controller = new \App\Controllers\AdminController();
            $controller->dashboard();
            break;

        case '/admin/products':
            $controller = new \App\Controllers\AdminController();
            $controller->products();
            break;

        case '/admin/products/create':
            $controller = new \App\Controllers\AdminController();
            $controller->createProduct();
            break;

        case (preg_match('/^\/admin\/products\/edit\/(\d+)$/', $path, $matches) ? true : false):
            $controller = new \App\Controllers\AdminController();
            $controller->editProduct($matches[1]);
            break;

        case (preg_match('/^\/admin\/products\/delete\/(\d+)$/', $path, $matches) ? true : false):
            $controller = new \App\Controllers\AdminController();
            $controller->deleteProduct($matches[1]);
            break;

        case '/admin/users':
            $controller = new \App\Controllers\AdminController();
            $controller->users();
            break;

        case '/admin/users/create':
            $controller = new \App\Controllers\AdminController();
            $controller->createUser();
            break;

        case (preg_match('/^\/admin\/users\/edit\/(\d+)$/', $path, $matches) ? true : false):
            $controller = new \App\Controllers\AdminController();
            $controller->editUser($matches[1]);
            break;

        case (preg_match('/^\/admin\/users\/delete\/(\d+)$/', $path, $matches) ? true : false):
            $controller = new \App\Controllers\AdminController();
            $controller->deleteUser($matches[1]);
            break;

        case '/admin/orders':
            $controller = new \App\Controllers\AdminController();
            $controller->orders();
            break;

        case (preg_match('/^\/admin\/orders\/view\/(\d+)$/', $path, $matches) ? true : false):
            $controller = new \App\Controllers\AdminController();
            $controller->viewOrder($matches[1]);
            break;

        case (preg_match('/^\/admin\/orders\/edit\/(\d+)$/', $path, $matches) ? true : false):
            $controller = new \App\Controllers\AdminController();
            $controller->editOrder($matches[1]);
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