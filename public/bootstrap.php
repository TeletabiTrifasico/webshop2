<?php
// Helper function to find files regardless of case sensitivity
function require_file($path) {
    if (file_exists($path)) {
        require_once $path;
        return true;
    }
    
    // Try with 'App' instead of 'app'
    $altPath = str_replace('/app/', '/App/', $path);
    if (file_exists($altPath)) {
        require_once $altPath;
        return true;
    }
    
    // Try with 'app' instead of 'App'
    $altPath = str_replace('/App/', '/app/', $path);
    if (file_exists($altPath)) {
        require_once $altPath;
        return true;
    }
    
    return false;
}

// Define the root directory
define('ROOT_DIR', dirname(__DIR__));

// Load configurations first
require_once ROOT_DIR . '/config/config.php';
require_once ROOT_DIR . '/config/database.php';

// Try to load critical files with case-insensitive handling
$criticalFiles = [
    ROOT_DIR . '/app/Utils/JWT.php',
    ROOT_DIR . '/app/Middleware/JwtMiddleware.php',
    ROOT_DIR . '/app/Controllers/Controller.php',
    ROOT_DIR . '/app/Controllers/Api/BaseApiController.php',
    ROOT_DIR . '/app/Models/Model.php'
];

foreach ($criticalFiles as $file) {
    if (!require_file($file)) {
        error_log("Critical error: Failed to load {$file}");
    }
}

// Autoload classes with case-insensitive handling
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = ROOT_DIR . '/app/';
    
    // Does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // No, move to the next registered autoloader
        return;
    }
    
    // Get the relative class name
    $relative_class = substr($class, $len);
    
    // Replace namespace separators with directory separators
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    // Try the normal case first
    if (file_exists($file)) {
        require $file;
        return;
    }
    
    // Try uppercase first letter variant (App instead of app)
    $upperFile = ROOT_DIR . '/App/' . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($upperFile)) {
        require $upperFile;
        return;
    }
    
    error_log("Autoload failed for class: $class (File not found: $file or $upperFile)");
});

// Initialize JWT
if (class_exists('\\App\\Utils\\JWT')) {
    \App\Utils\JWT::init(JWT_SECRET);
} else {
    error_log("Critical error: JWT class not found. Check autoloading.");
}

// Start session
session_start();