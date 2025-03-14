<?php
// General configuration settings for the webshop application
define('APP_NAME', 'Webshop');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); // 'production' for live environment

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'webshop_db');
define('DB_USER', 'root');
define('DB_PASS', '!webshopadmin2025'); 

// Other settings
define('ITEMS_PER_PAGE', 10);
define('SESSION_TIMEOUT', 3600); 

// API settings
define('API_URL', 'http://localhost/webshop/api'); 
?>