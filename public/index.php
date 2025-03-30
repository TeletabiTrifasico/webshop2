<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

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

// Handle API requests
if (strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
    $request = $_SERVER['REQUEST_URI'];
    $path = rtrim(parse_url($request, PHP_URL_PATH), '/');

    // API Routes configuration
    $apiRoutes = [
        '/api/products' => ['Api\ProductController', 'index'],
        '/api/products/(\d+)' => ['Api\ProductController', 'show'],
        '/api/products/featured' => ['Api\ProductController', 'featured'],
        '/api/products/categories' => ['Api\ProductController', 'categories'],
        '/api/cart' => ['Api\CartController', 'index'],
        '/api/cart/add' => ['Api\CartController', 'add'],
        '/api/cart/update' => ['Api\CartController', 'update'],
        '/api/cart/remove' => ['Api\CartController', 'remove'],
        '/api/orders' => ['Api\OrderController', 'index'],
        '/api/orders/(\d+)' => ['Api\OrderController', 'show'],
        '/api/orders/create' => ['Api\OrderController', 'store'],
        '/api/auth/login' => ['Api\AuthController', 'login'],
        '/api/auth/register' => ['Api\AuthController', 'register'],
        '/api/auth/logout' => ['Api\AuthController', 'logout'],
        '/api/user/profile' => ['Api\UserController', 'profile'],
        '/api/user/profile/update' => ['Api\UserController', 'update'],
    ];

    // Admin API routes
    if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
        $adminApiRoutes = [
            '/api/admin/products' => ['Api\Admin\ProductController', 'index'],
            '/api/admin/products/create' => ['Api\Admin\ProductController', 'store'],
            '/api/admin/products/(\d+)' => ['Api\Admin\ProductController', 'show'],
            '/api/admin/products/(\d+)/update' => ['Api\Admin\ProductController', 'update'],
            '/api/admin/products/(\d+)/delete' => ['Api\Admin\ProductController', 'destroy'],
            '/api/admin/users' => ['Api\Admin\UserController', 'index'],
            '/api/admin/users/(\d+)' => ['Api\Admin\UserController', 'show'],
            '/api/admin/users/(\d+)/update' => ['Api\Admin\UserController', 'update'],
            '/api/admin/orders' => ['Api\Admin\OrderController', 'index'],
            '/api/admin/orders/(\d+)' => ['Api\Admin\OrderController', 'show'],
            '/api/admin/orders/(\d+)/update' => ['Api\Admin\OrderController', 'update'],
            '/api/admin/dashboard' => ['Api\Admin\DashboardController', 'index'],
        ];
        $apiRoutes = array_merge($apiRoutes, $adminApiRoutes);
    }

    try {
        // Handle API routes
        foreach ($apiRoutes as $pattern => [$controller, $method]) {
            if (preg_match("#^$pattern$#", $path, $matches)) {
                $controller = "\\App\\Controllers\\$controller";
                $controller = new $controller();
                array_shift($matches); // Remove full match
                call_user_func_array([$controller, $method], $matches);
                exit;
            }
        }

        // If no API route matches
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['error' => 'API endpoint not found']);
        exit;
    } catch (Exception $e) {
        error_log($e->getMessage());
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['error' => 'An error occurred']);
        exit;
    }
}

// Serve index.html for all other routes (Vue.js will handle routing)
$indexFile = __DIR__ . '/index.html';
if (file_exists($indexFile)) {
    echo file_get_contents($indexFile);
} else {
    echo "Error: index.html not found";
}
?>