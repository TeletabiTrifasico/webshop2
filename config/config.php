<?php
// filepath: c:\Users\hugoj\Desktop\websites\webshop\config\config.php

// General configuration settings
define('APP_NAME', 'Webshop');
define('APP_VERSION', '1.0.0');

// Set application environment
define('APP_ENV', getenv('APP_ENV') ?: 'development');

// Set debug mode
define('APP_DEBUG', APP_ENV === 'development');

// Load environment variables from .env file if it exists
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse key=value pairs
        list($key, $value) = explode('=', $line, 2) + [null, null];
        if (!empty($key)) {
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            if (preg_match('/^([\'"])(.*)\1$/', $value, $matches)) {
                $value = $matches[2];
            }
            
            // Set as environment variable
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// Database configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'webshop_db');
define('DB_USER', getenv('DB_USER') ?: 'webshopadmin');
define('DB_PASS', getenv('DB_PASSWORD') ?: '!webshopadmin2025');

// Other settings
define('ITEMS_PER_PAGE', 10);
define('SESSION_TIMEOUT', 3600);

// API settings for Docker
define('API_URL', 'http://localhost:8088/api');

// JWT settings
define('JWT_SECRET', getenv('JWT_SECRET') ?: 'your_default_jwt_secret_for_development');
define('JWT_EXPIRY', getenv('JWT_EXPIRY') ? (int)getenv('JWT_EXPIRY') : 86400);

// URL root
define('URL_ROOT', '/');

// Site name
define('SITE_NAME', 'WebShop');

// Image upload settings
define('UPLOAD_DIR', 'uploads/');
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

// Other application settings
define('UPLOADS_DIR', __DIR__ . '/../public/images/products');
define('PRODUCTS_PER_PAGE', 12);
define('USERS_PER_PAGE', 10);
define('ORDERS_PER_PAGE', 10);
?>