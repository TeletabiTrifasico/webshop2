<?php
// General configuration settings
define('APP_NAME', 'Webshop');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development');

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
define('JWT_SECRET', 'cGC9EdNfxMLwDQ3PjHmS4TKvRbF8aZtY'); // Use a strong random key in production
define('JWT_EXPIRY', 24 * 3600); // 24 hours in seconds

// URL root
define('URL_ROOT', '/');

// Site name
define('SITE_NAME', 'WebShop');

// Image upload settings
define('UPLOAD_DIR', 'uploads/');
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
?>