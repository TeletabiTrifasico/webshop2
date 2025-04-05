<?php
// Load bootstrap file that handles autoloading and critical includes
require_once __DIR__ . '/bootstrap.php';

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Get request path and method
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$_method = $_SERVER['REQUEST_METHOD'];

// Handle method spoofing for forms
if ($_method === 'POST' && isset($_GET['_method'])) {
    $_method = strtoupper($_GET['_method']);
}

// Define routes and their corresponding controllers/methods
$routes = [
    // Auth routes
    '#^/api/auth/register$#' => [
        'POST' => [\App\Controllers\Api\AuthController::class, 'register']
    ],
    '#^/api/auth/login$#' => [
        'POST' => [\App\Controllers\Api\AuthController::class, 'login']
    ],
    '#^/api/auth/logout$#' => [
        'POST' => [\App\Controllers\Api\AuthController::class, 'logout']
    ],
    '#^/api/auth/user$#' => [
        'GET' => [\App\Controllers\Api\AuthController::class, 'getCurrentUser']
    ],

    // Product routes
    '#^/api/products$#' => [
        'GET' => [\App\Controllers\Api\ProductController::class, 'index']
    ],
    '#^/api/products/latest$#' => [
        'GET' => [\App\Controllers\Api\ProductController::class, 'latest']
    ],
    '#^/api/products/(\d+)$#' => [
        'GET' => [\App\Controllers\Api\ProductController::class, 'show']
    ],

    // Cart routes
    '#^/api/cart$#' => [
        'GET' => [\App\Controllers\Api\CartController::class, 'index'],
        'POST' => [\App\Controllers\Api\CartController::class, 'addItem']
    ],
    '#^/api/cart/(\d+)$#' => [
        'PUT' => [\App\Controllers\Api\CartController::class, 'updateItem'],
        'DELETE' => [\App\Controllers\Api\CartController::class, 'removeItem']
    ],
    '#^/api/cart/clear$#' => [
        'POST' => [\App\Controllers\Api\CartController::class, 'clearCart']
    ],
    '#^/api/cart/checkout$#' => [
        'POST' => [\App\Controllers\Api\CartController::class, 'checkout']
    ],

    // Admin routes
    '#^/api/admin/dashboard$#' => [
        'GET' => [\App\Controllers\Api\AdminController::class, 'getDashboardStats']
    ],
    
    // Admin user management
    '#^/api/admin/users$#' => [
        'GET' => [\App\Controllers\Api\AdminController::class, 'getUsers'],
        'POST' => [\App\Controllers\Api\UserAdminController::class, 'create']
    ],
    '#^/api/admin/users/(\d+)$#' => [
        'GET' => [\App\Controllers\Api\UserAdminController::class, 'get'],
        'PUT' => [\App\Controllers\Api\UserAdminController::class, 'update'],
        'DELETE' => [\App\Controllers\Api\AdminController::class, 'deleteUser']
    ],
    '#^/api/admin/users/(\d+)/role$#' => [
        'PUT' => [\App\Controllers\Api\AdminController::class, 'updateUserRole']
    ],
    
    // Admin product management
    '#^/api/admin/products$#' => [
        'GET' => [\App\Controllers\Api\AdminController::class, 'getProducts'],
        'POST' => [\App\Controllers\Api\ProductController::class, 'create']
    ],
    '#^/api/admin/products/(\d+)$#' => [
        'GET' => [\App\Controllers\Api\ProductController::class, 'show'],
        'PUT' => [\App\Controllers\Api\ProductController::class, 'update'],
        'DELETE' => [\App\Controllers\Api\ProductController::class, 'delete']
    ],
    
    // Admin order management
    '#^/api/admin/orders$#' => [
        'GET' => [\App\Controllers\Api\AdminController::class, 'getOrders']
    ],
    '#^/api/admin/orders/(\d+)$#' => [
        'GET' => [\App\Controllers\Api\AdminController::class, 'getOrderDetails'],
        'PUT' => [\App\Controllers\Api\AdminController::class, 'updateOrderStatus']
    ],

    // User profile routes
    '#^/api/user/profile$#' => [
        'GET' => [\App\Controllers\Api\UserAdminController::class, 'getProfile'],
        'PUT' => [\App\Controllers\Api\UserAdminController::class, 'updateProfile']
    ],
    '#^/api/user/orders$#' => [
        'GET' => [\App\Controllers\Api\UserAdminController::class, 'getOrders']
    ],
    '#^/api/user/orders/(\d+)$#' => [
        'GET' => [\App\Controllers\Api\UserAdminController::class, 'getOrderDetails']
    ],
];

// Route matching and dispatching
$routeFound = false;
foreach ($routes as $pattern => $handlers) {
    if (preg_match($pattern, $path, $matches)) {
        if (isset($handlers[$_method])) {
            $routeFound = true;
            
            // Get controller class and method
            list($controllerClass, $method) = $handlers[$_method];
            
            // Remove the full match from the matches array
            array_shift($matches);
            
            // Create controller instance and call the method with parameters
            $controller = new $controllerClass();
            call_user_func_array([$controller, $method], $matches);
            
            // Exit after handling the route
            exit;
        }
    }
}

// No matching route found, return 404
if (!$routeFound) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Route not found'
    ]);
    exit;
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
        require_once $file;
    } else {
        error_log("Autoload failed for class: $class (File not found: $file)");
    }
});

// Make sure the JWT class exists before initializing
if (class_exists('\\App\\Utils\\JWT')) {
    // Initialize JWT with the defined constant
    \App\Utils\JWT::init(JWT_SECRET);
} else {
    error_log("Critical error: JWT class not found. Check autoloading.");
}

// Serve index.html for all other routes (Vue.js will handle routing)
$indexFile = __DIR__ . '/index.html';
if (file_exists($indexFile)) {
    echo file_get_contents($indexFile);
} else {
    echo "Error: index.html not found";
}
?>