<?php

namespace App\Middleware;

use App\Utils\JWT;

class JwtMiddleware {
    /**
     * Get authenticated user from JWT token
     *
     * @return array|false User data or false if not authenticated
     */
    public static function getAuthUser() {
        $token = self::getBearerToken();
        
        if (!$token) {
            return false;
        }
        
        try {
            return JWT::verify($token);
        } catch (\Exception $e) {
            error_log('JWT validation error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if the request has a valid admin token
     */
    public static function isAdmin() {
        $userData = self::getAuthUser();
        return $userData && isset($userData['role']) && $userData['role'] === 'admin';
    }
    
    /**
     * Extract the bearer token from the Authorization header
     *
     * @return string|null
     */
    public static function getBearerToken() {
        $headers = self::getAuthorizationHeader();
        
        if (!empty($headers) && preg_match('/Bearer\s+(.*)$/i', $headers, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    /**
     * Get authorization header
     *
     * @return string|null
     */
    public static function getAuthorizationHeader() {
        $headers = null;
        
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            // Nginx or fast CGI
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (function_exists('apache_request_headers')) {
            // Apache
            $requestHeaders = apache_request_headers();
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        
        // In case we're using PHP-FPM or FastCGI
        if (empty($headers) && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
        }
        
        // Add debug logging to help troubleshoot auth issues
        error_log('Authorization header: ' . ($headers ? 'Found' : 'Not found'));
        
        return $headers;
    }
}