<?php

namespace App\Controllers\Api;

use App\Utils\JWT;
use App\Middleware\JwtMiddleware;

abstract class BaseApiController {
    protected $pdo;

    public function __construct() {
        // Initialize JWT with secret if defined
        if (defined('JWT_SECRET')) {
            JWT::init(JWT_SECRET);
        }
        $this->connectToDatabase();
    }

    protected function connectToDatabase() {
        try {
            $host = getenv('DB_HOST') ?: 'db';
            $dbname = getenv('DB_NAME') ?: 'webshop_db';
            $username = getenv('DB_USER') ?: 'webshopadmin';
            $password = getenv('DB_PASSWORD') ?: '!webshopadmin2025';
            
            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->pdo = new \PDO($dsn, $username, $password, $options);
        } catch (\PDOException $e) {
            error_log("API controller database connection error: " . $e->getMessage());
            throw new \Exception("Database connection error: " . $e->getMessage());
        }
    }
    
    protected function model($model) {
        $modelClass = "\\App\\Models\\{$model}";
        return new $modelClass();
    }
    
    protected function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
    
    protected function getRequestData() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        // If JSON parsing failed, fallback to POST data
        if (json_last_error() !== JSON_ERROR_NONE) {
            $data = $_POST;
        }
        
        return $data ?? [];
    }
    
    /**
     * Check if the user is authenticated via JWT
     */
    protected function isAuthenticated() {
        return JwtMiddleware::getAuthUser() !== false;
    }
    
    /**
     * Check if the authenticated user is an admin
     */
    protected function isAdmin() {
        return JwtMiddleware::isAdmin();
    }
    
    /**
     * Get the authenticated user data
     */
    protected function getAuthUser() {
        return JwtMiddleware::getAuthUser();
    }
    
    /**
     * Require authentication for a route
     */
    protected function requireAuth() {
        if (!$this->isAuthenticated()) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Authentication required'
            ], 401);
            exit;
        }
    }
    
    /**
     * Require admin role for a route
     */
    protected function requireAdmin() {
        if (!$this->isAuthenticated()) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Authentication required'
            ], 401);
            exit;
        }
        
        if (!$this->isAdmin()) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Admin privileges required'
            ], 403);
            exit;
        }
    }
    
    /**
     * Handle exceptions in a consistent way
     * 
     * @param \Exception $e The exception to handle
     * @param string $message The user-friendly error message
     */
    protected function handleException(\Exception $e, $message = 'An error occurred') {
        // Log the full error details for debugging
        error_log("=== ERROR DETAILS ===");
        error_log("{$message}: " . $e->getMessage());
        error_log("File: " . $e->getFile() . " on line " . $e->getLine());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        $errorData = [
            'success' => false,
            'error' => $message
        ];
        
        // Add debug information in development environment
        if (defined('APP_ENV') && APP_ENV === 'development') {
            $errorData['debug'] = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => explode("\n", $e->getTraceAsString())
            ];
        }
        
        $this->jsonResponse($errorData, 500);
    }
}