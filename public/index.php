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

// API Routes configuration
$apiRoutes = [
    '/api/auth/login' => ['Api\AuthController', 'login'],
    '/api/auth/register' => ['Api\AuthController', 'register'],
    '/api/auth/logout' => ['Api\AuthController', 'logout'],
    '/api/products/latest' => ['Api\ProductController', 'latest'],
    '/api/products/(\d+)' => ['Api\ProductController', 'show'],
    '/api/products' => ['Api\ProductController', 'index'],
];

// Handle API requests
if (strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
    $request = $_SERVER['REQUEST_URI'];
    $path = parse_url($request, PHP_URL_PATH);
    $path = rtrim($path, '/');

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