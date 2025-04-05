<?php
// This file helps with case-sensitivity issues in class loading

// Check if we need case-insensitive autoloading
if (file_exists(__DIR__ . '/../vendor/autoload/case_insensitive_loader.php')) {
    require_once __DIR__ . '/../vendor/autoload/case_insensitive_loader.php';
}

// Try to find the controllers directory
$controllersPaths = [
    __DIR__ . '/../App/Controllers',
    __DIR__ . '/../app/Controllers',
    __DIR__ . '/../App/controllers',
    __DIR__ . '/../app/controllers'
];

foreach ($controllersPaths as $path) {
    if (is_dir($path)) {
        // Set a constant with the correct path
        define('CONTROLLERS_PATH', $path);
        break;
    }
}

// Log the path for debugging
error_log("Controllers path: " . (defined('CONTROLLERS_PATH') ? CONTROLLERS_PATH : 'Not found'));