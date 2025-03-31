<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Enable CORS for development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Add error reporting for development
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// Autoload classes
spl_autoload_register(function ($class) {
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file = __DIR__ . '/../' . $class . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

session_start();

// Handle method spoofing for HTML forms
$_method = $_SERVER['REQUEST_METHOD'];
if ($_method === 'POST' && isset($_GET['_method'])) {
    $_method = strtoupper($_GET['_method']);
}

// API Routes configuration
$apiRoutes = [
    // Auth routes
    '/api/auth/login' => ['Api\AuthController', 'login'],
    '/api/auth/register' => ['Api\AuthController', 'register'],
    '/api/auth/logout' => ['Api\AuthController', 'logout'],
    
    // Product routes
    '/api/products/latest' => ['Api\ProductController', 'latest'],
    '/api/products/(\d+)' => ['Api\ProductController', 'show'],
    '/api/products' => ['Api\ProductController', 'index'],
    
    // Cart routes
    '/api/cart' => ['Api\CartController', 'index'],
    '/api/cart/add' => ['Api\CartController', 'add'],
    '/api/cart/update' => ['Api\CartController', 'update'],
    '/api/cart/remove' => ['Api\CartController', 'remove'],
    '/api/cart/clear' => ['Api\CartController', 'clear'],
    '/api/cart/checkout' => ['Api\CartController', 'checkout'],
    
    // Admin routes
    '/api/admin/users' => ['Api\AdminController', 'getUsers'],
    '/api/admin/orders' => ['Api\AdminController', 'getOrders'],
];

// Routes with specific HTTP methods
$apiRoutesMethods = [
    // User management routes
    'GET:/api/admin/users/(\d+)' => ['Api\UserAdminController', 'getUser'],
    'POST:/api/admin/users' => ['Api\UserAdminController', 'create'],
    'PUT:/api/admin/users/(\d+)' => ['Api\UserAdminController', 'update'],
    
    // Admin user routes
    'PUT:/api/admin/users/(\d+)/role' => ['Api\AdminController', 'updateUserRole'],
    'DELETE:/api/admin/users/(\d+)' => ['Api\AdminController', 'deleteUser'],
    
    // Admin order routes
    'GET:/api/admin/orders/(\d+)' => ['Api\AdminController', 'getOrderDetails'],
    'PUT:/api/admin/orders/(\d+)/status' => ['Api\AdminController', 'updateOrderStatus'],
    'DELETE:/api/admin/orders/(\d+)' => ['Api\AdminController', 'deleteOrder'],
    
    // Admin product routes
    'POST:/api/admin/products' => ['Api\AdminProductController', 'create'],
    'PUT:/api/admin/products/(\d+)' => ['Api\AdminProductController', 'update'],
    'POST:/api/admin/products/(\d+)' => ['Api\AdminProductController', 'update'],  // Allow POST for PUT with _method
    'DELETE:/api/admin/products/(\d+)' => ['Api\AdminProductController', 'delete'],
];

// Handle API requests
if (strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
    $request = $_SERVER['REQUEST_URI'];
    $path = parse_url($request, PHP_URL_PATH);
    $path = rtrim($path, '/');
    $method = $_method;  // Use the potentially spoofed method

    // First check method-specific routes
    $methodPath = "$method:$path";
    foreach ($apiRoutesMethods as $pattern => [$controller, $action]) {
        $methodPattern = explode(':', $pattern)[0];
        $urlPattern = explode(':', $pattern)[1];
        
        if ($methodPattern === $method && preg_match("#^$urlPattern$#", $path, $matches)) {
            $controller = "\\App\\Controllers\\$controller";
            $controller = new $controller();
            array_shift($matches);
            call_user_func_array([$controller, $action], $matches);
            exit;
        }
    }

    // Then check general routes
    foreach ($apiRoutes as $pattern => [$controller, $method]) {
        if (preg_match("#^$pattern$#", $path, $matches)) {
            $controller = "\\App\\Controllers\\$controller";
            $controller = new $controller();
            array_shift($matches);
            call_user_func_array([$controller, $method], $matches);
            exit;
        }
    }

    header('HTTP/1.1 404 Not Found');
    echo json_encode(['error' => 'API endpoint not found']);
    exit;
}

// Serve index.html for all other routes (Vue.js will handle routing)
$indexFile = __DIR__ . '/index.html';
if (file_exists($indexFile)) {
    echo file_get_contents($indexFile);
} else {
    echo "Error: index.html not found";
}
?>