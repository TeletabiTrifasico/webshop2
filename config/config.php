<?php
// General configuration settings
define('APP_NAME', 'Webshop');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development');

// Database configuration
define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_NAME', getenv('DB_NAME') ?: 'webshop_db');
define('DB_USER', getenv('DB_USER') ?: 'webshopadmin');
define('DB_PASS', getenv('DB_PASSWORD') ?: '!webshopadmin2025');

// Other settings
define('ITEMS_PER_PAGE', 10);
define('SESSION_TIMEOUT', 3600);

// API settings for Docker
define('API_URL', 'http://localhost:8088/api');
?>