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
            if (!isset($data['name']) || !isset($data['email']) || !isset($data['password'])) {
                throw new \Exception('All fields are required');
            }

            // Check if email already exists
            if ($this->userModel->findByEmail($data['email'])) {
                throw new \Exception('Email already registered');
            }

            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

            $userId = $this->userModel->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $hashedPassword,
                'role' => 'user'
            ]);
            
            $user = $this->userModel->findById($userId);
            unset($user['password']);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            
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

    public function logout() {
        session_destroy();
        $this->jsonResponse(['success' => true]);
    }
}