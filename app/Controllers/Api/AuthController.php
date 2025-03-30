<?php

namespace App\Controllers\Api;

class AuthController extends BaseApiController {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = $this->model('User');
    }

    public function login() {
        $data = $this->getRequestData();
        
        if (!isset($data['email']) || !isset($data['password'])) {
            $this->jsonResponse(['error' => 'Email and password are required'], 400);
            return;
        }

        $user = $this->userModel->findByEmail($data['email']);
        
        if ($user && password_verify($data['password'], $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            
            unset($user['password']);
            $this->jsonResponse([
                'success' => true,
                'user' => $user
            ]);
        } else {
            $this->jsonResponse(['error' => 'Invalid credentials'], 401);
        }
    }

    public function register() {
        $data = $this->getRequestData();
        
        try {
            $userId = $this->userModel->create([
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'],
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
            $this->jsonResponse(['error' => 'Registration failed'], 400);
        }
    }

    public function logout() {
        session_destroy();
        $this->jsonResponse(['success' => true]);
    }
}