<?php

namespace App\Controllers\Api;

use App\Utils\JWT;

class AuthController extends BaseApiController {
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = $this->model('User');
        
        // Initialize JWT with secret
        JWT::init(JWT_SECRET);
    }
    
    public function register() {
        $data = $this->getRequestData();
        
        // Validation
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Username, email, and password are required'
            ], 400);
            return;
        }
        
        // Validate email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Invalid email format'
            ], 400);
            return;
        }
        
        // Check if email already exists
        if ($this->userModel->findByEmail($data['email'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Email already registered'
            ], 400);
            return;
        }
        
        // Create user
        $userData = [
            'username' => trim($data['username']),
            'email' => strtolower(trim($data['email'])),
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => 'user' // Default role
        ];
        
        $userId = $this->userModel->create($userData);
        
        if ($userId) {
            $user = $this->userModel->findById($userId);
            
            // Create payload for JWT
            $payload = [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ];
            
            // Generate JWT token
            $token = JWT::generate($payload, JWT_EXPIRY);
            
            // Don't send password back
            unset($user['password']);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Registration successful',
                'token' => $token,
                'user' => $user
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to create account'
            ], 500);
        }
    }
    
    public function login() {
        $data = $this->getRequestData();
        
        // Validation
        if (empty($data['email']) || empty($data['password'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Email and password are required'
            ], 400);
            return;
        }
        
        // Find user
        $user = $this->userModel->findByEmail($data['email']);
        
        if (!$user) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Invalid credentials'
            ], 401);
            return;
        }
        
        // Verify password
        if (password_verify($data['password'], $user['password'])) {
            // Create payload for JWT
            $payload = [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ];
            
            // Generate JWT token
            $token = JWT::generate($payload, JWT_EXPIRY);
            
            // Don't send password back
            unset($user['password']);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Login successful',
                'token' => $token,
                'user' => $user
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Invalid credentials'
            ], 401);
        }
    }
    
    public function logout() {
        // JWT is stateless, so no server-side logout needed
        // Client will handle removing the token
        
        $this->jsonResponse([
            'success' => true,
            'message' => 'Logout successful'
        ]);
    }
    
    public function getCurrentUser() {
        // Use middleware to verify token
        $userData = \App\Middleware\JwtMiddleware::getAuthUser();
        
        if (!$userData) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Unauthorized'
            ], 401);
            return;
        }
        
        $this->jsonResponse([
            'success' => true,
            'user' => [
                'id' => $userData['user_id'],
                'username' => $userData['username'],
                'email' => $userData['email'],
                'role' => $userData['role']
            ]
        ]);
    }
}