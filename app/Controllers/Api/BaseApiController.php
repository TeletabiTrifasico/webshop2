<?php

namespace App\Controllers\Api;

use App\Utils\JWT;
use App\Middleware\JwtMiddleware;

class BaseApiController {
    public function __construct() {
        // Initialize JWT with secret if defined
        if (defined('JWT_SECRET')) {
            JWT::init(JWT_SECRET);
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
        error_log($message . ": " . $e->getMessage());
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
                'trace' => $e->getTraceAsString()
            ];
        }
        
        $this->jsonResponse($errorData, 500);
    }
}