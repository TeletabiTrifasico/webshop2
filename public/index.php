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

session_start();

$request = $_SERVER['REQUEST_URI'];
$path = rtrim(parse_url($request, PHP_URL_PATH), '/');

try {
    // Main Routes
    $mainRoutes = [
        '' => ['HomeController', 'index'],
        '/' => ['HomeController', 'index'],
        '/products' => ['ProductController', 'index'],
        '/cart' => ['CartController', 'index'],
    ];

    // Auth Routes
    $authRoutes = [
        '/auth/login' => ['AuthController', 'login'],
        '/auth/register' => ['AuthController', 'register'],
        '/auth/logout' => ['AuthController', 'logout'],
    ];

    // Cart Routes
    $cartRoutes = [
        '/cart/add' => ['CartController', 'addItem'],
        '/cart/update' => ['CartController', 'updateQuantity'],
        '/cart/remove' => ['CartController', 'removeItem'],
        '/cart/checkout' => ['CartController', 'processCheckout'],
    ];

    // API Routes
    $apiRoutes = [
        '/api/products' => ['ApiController', 'products'],
        '/api/cart' => ['ApiController', 'cart'],
    ];

    // User Routes
    $userRoutes = [
        '/user/profile' => ['UserController', 'profile'],
        '/user/profile/update' => ['UserController', 'updateProfile'],
    ];

    // Find route in static routes first
    $allRoutes = array_merge($mainRoutes, $authRoutes, $cartRoutes, $apiRoutes, $userRoutes);
    if (isset($allRoutes[$path])) {
        [$controllerName, $method] = $allRoutes[$path];
        $controller = new ("\\App\\Controllers\\$controllerName")();
        
        if (in_array($path, array_keys($cartRoutes)) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /products');
            exit;
        }
        
        $controller->$method();
        exit;
    }

    // Dynamic Routes
    $dynamicRoutes = [
        '/^\/products\/(\d+)$/' => ['ProductController', 'detail'],
        '/^\/api\/products\/(\d+)$/' => ['ApiController', 'product'],
        '/^\/admin\/products\/edit\/(\d+)$/' => ['AdminController', 'editProduct'],
        '/^\/admin\/products\/delete\/(\d+)$/' => ['AdminController', 'deleteProduct'],
        '/^\/admin\/users\/edit\/(\d+)$/' => ['AdminController', 'editUser'],
        '/^\/admin\/users\/delete\/(\d+)$/' => ['AdminController', 'deleteUser'],
        '/^\/admin\/orders\/view\/(\d+)$/' => ['AdminController', 'viewOrder'],
        '/^\/admin\/orders\/edit\/(\d+)$/' => ['AdminController', 'editOrder'],
    ];

    foreach ($dynamicRoutes as $pattern => [$controllerName, $method]) {
        if (preg_match($pattern, $path, $matches)) {
            $controller = new ("\\App\\Controllers\\$controllerName")();
            $controller->$method($matches[1]);
            exit;
        }
    }

    // Admin Routes (static)
    $adminRoutes = [
        '/admin' => ['dashboard'],
        '/admin/dashboard' => ['dashboard'],
        '/admin/products' => ['products'],
        '/admin/products/create' => ['createProduct'],
        '/admin/users' => ['users'],
        '/admin/users/create' => ['createUser'],
        '/admin/orders' => ['orders'],
    ];

    foreach ($adminRoutes as $adminPath => $method) {
        if ($path === $adminPath) {
            $controller = new \App\Controllers\AdminController();
            $controller->{$method[0]}();
            exit;
        }
    }

    // If no route matches, show 404
    http_response_code(404);
    require __DIR__ . '/../app/views/404.php';

} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo "An error occurred. Please try again later.";
}
?>