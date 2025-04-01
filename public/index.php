<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Define correct MIME types for common file extensions
$mimeTypes = [
    'js' => 'application/javascript',
    'mjs' => 'application/javascript',
    'css' => 'text/css',
    'json' => 'application/json',
    'png' => 'image/png',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif' => 'image/gif',
    'svg' => 'image/svg+xml',
];

// Check if the request is for a static file with a known MIME type
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$extension = pathinfo($requestPath, PATHINFO_EXTENSION);

if (isset($mimeTypes[$extension]) && file_exists(__DIR__ . $requestPath)) {
    header("Content-Type: {$mimeTypes[$extension]}");
    readfile(__DIR__ . $requestPath);
    exit;
}

// Error reporting settings
if (defined('APP_ENV') && APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Custom error handler for API routes to return JSON errors instead of HTML
function apiErrorHandler($errno, $errstr, $errfile, $errline) {
    if (strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
        $error = [
            'success' => false,
            'error' => 'Server Error',
            'message' => $errstr
        ];
        
        if (defined('APP_ENV') && APP_ENV === 'development') {
            $error['debug'] = [
                'file' => $errfile,
                'line' => $errline,
                'type' => $errno
            ];
        }
        
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode($error);
        exit;
    }
    
    // For non-API routes, use PHP's default error handler
    return false;
}

// Set the custom error handler
set_error_handler('apiErrorHandler', E_ALL);

// Autoload classes
spl_autoload_register(function ($class) {
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file = __DIR__ . '/../' . $class . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Initialize JWT
if (defined('JWT_SECRET')) {
    \App\Utils\JWT::init(JWT_SECRET);
}

session_start();

// Handle method spoofing for forms
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