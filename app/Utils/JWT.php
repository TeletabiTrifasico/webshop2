<?php

namespace App\Utils;

class JWT {
    private static $secret;
    
    /**
     * Initialize JWT with a secret key
     */
    public static function init($secret) {
        self::$secret = $secret;
    }
    
    /**
     * Generate a JWT token
     * 
     * @param array $payload Data to encode in the token
     * @param int $expiry Expiration time in seconds
     * @return string The JWT token
     */
    public static function generate($payload, $expiry = 3600) {
        // Add standard JWT claims
        $payload['iat'] = time();                // Issued at
        $payload['exp'] = time() + $expiry;      // Expiration time
        $payload['jti'] = bin2hex(random_bytes(16)); // JWT ID for uniqueness
        
        // Create JWT parts
        $header = self::base64UrlEncode(json_encode([
            'typ' => 'JWT', 
            'alg' => 'HS256'
        ]));
        
        $payload = self::base64UrlEncode(json_encode($payload));
        $signature = self::generateSignature($header, $payload);
        
        return "{$header}.{$payload}.{$signature}";
    }
    
    /**
     * Verify and decode a JWT token
     * 
     * @param string $token The JWT token
     * @return array|false Decoded payload or false if invalid
     */
    public static function verify($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }
        
        list($header, $payload, $signature) = $parts;
        
        // Verify signature
        if (!self::verifySignature($header, $payload, $signature)) {
            return false;
        }
        
        // Decode payload
        $decodedPayload = json_decode(self::base64UrlDecode($payload), true);
        
        // Check expiration
        if (!$decodedPayload || !isset($decodedPayload['exp']) || $decodedPayload['exp'] < time()) {
            return false;
        }
        
        return $decodedPayload;
    }
    
    /**
     * Generate signature for token parts
     */
    private static function generateSignature($header, $payload) {
        $data = "$header.$payload";
        $signature = hash_hmac('sha256', $data, self::$secret, true);
        return self::base64UrlEncode($signature);
    }
    
    /**
     * Verify signature
     */
    private static function verifySignature($header, $payload, $signature) {
        $expectedSignature = self::generateSignature($header, $payload);
        return self::constantTimeCompare($expectedSignature, $signature);
    }
    
    /**
     * Base64 URL-safe encoding
     */
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Base64 URL-safe decoding
     */
    private static function base64UrlDecode($data) {
        $base64 = strtr($data, '-_', '+/');
        return base64_decode($base64);
    }
    
    /**
     * Constant-time string comparison to prevent timing attacks
     */
    private static function constantTimeCompare($knownString, $userString) {
        if (function_exists('hash_equals')) {
            return hash_equals($knownString, $userString);
        }
        
        // Fallback implementation
        if (strlen($knownString) !== strlen($userString)) {
            return false;
        }
        
        $result = 0;
        for ($i = 0; $i < strlen($knownString); $i++) {
            $result |= ord($knownString[$i]) ^ ord($userString[$i]);
        }
        
        return $result === 0;
    }
}