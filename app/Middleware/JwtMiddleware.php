<?php

namespace App\Middleware;

use App\Utils\JWT;

class JwtMiddleware {
    /**
     * Get the authenticated user from JWT token
     * 
     * @return array|false User data if authenticated, false otherwise
     */
    public static function getAuthUser() {
        $headers = self::getAuthorizationHeader();
        
        if (!$headers) {
            return false;
        }
        
        // Extract token from Bearer format
        $token = str_replace('Bearer ', '', $headers);
        
        if (empty($token)) {
            return false;
        }
        
        // Verify token
        return JWT::verify($token);
    }
    
    /**
     * Get authorization header
     */
    private static function getAuthorizationHeader() {
        $headers = null;
        
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        
        return $headers;
    }
    
    /**
     * Check if the request has a valid admin token
     */
    public static function isAdmin() {
        $userData = self::getAuthUser();
        return $userData && isset($userData['role']) && $userData['role'] === 'admin';
    }
}