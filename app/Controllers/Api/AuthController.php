<?php

namespace App\Controllers\Api;

class AuthController extends BaseApiController {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = $this->model('User');
    }

    public function login() {
        try {
            $data = $this->getRequestData();
            
            if (!isset($data['email']) || !isset($data['password'])) {
                throw new \Exception('Email and password are required');
            }

            $user = $this->userModel->findByEmail($data['email']);
            
            if (!$user || !password_verify($data['password'], $user['password'])) {
                throw new \Exception('Invalid credentials');
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            
            unset($user['password']);
            
            $this->jsonResponse([
                'success' => true,
                'user' => $user
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function register() {
        try {
            $data = $this->getRequestData();
            
            // Validate required fields
            if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
                throw new \Exception('All fields are required');
            }

            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('Invalid email format');
            }

            // Check if email exists
            if ($this->userModel->findByEmail($data['email'])) {
                throw new \Exception('Email already registered');
            }

            // Create user
            $userId = $this->userModel->create([
                'username' => trim($data['username']),
                'email' => strtolower(trim($data['email'])),
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'role' => 'user'
            ]);

            if (!$userId) {
                throw new \Exception('Failed to create account');
            }

            $user = $this->userModel->findById($userId);
            if (!$user) {
                throw new \Exception('Error retrieving user account');
            }

            // Start session
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            // Remove sensitive data
            unset($user['password']);

            $this->jsonResponse([
                'success' => true,
                'user' => $user
            ]);
        } catch (\Exception $e) {
            error_log('Registration error: ' . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function logout() {
        session_destroy();
        $this->jsonResponse(['success' => true]);
    }
}