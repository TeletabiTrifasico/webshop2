<?php

namespace App\Controllers\Api;

class UserAdminController extends BaseApiController {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = $this->model('User');
    }

    // Check if the user is admin
    private function checkAdmin() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Unauthorized access'
            ], 403);
            exit;
        }
    }

    // Get user details
    public function getUser($id) {
        $this->checkAdmin();
        
        $user = $this->userModel->findById($id);
        
        if (!$user) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'User not found'
            ], 404);
            return;
        }
        
        // Don't send the password
        unset($user['password']);
        
        $this->jsonResponse([
            'success' => true,
            'user' => $user
        ]);
    }
    
    // Create user
    public function create() {
        $this->checkAdmin();

        $data = $this->getRequestData();
        
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

        // Check if email exists
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
            'role' => $data['role'] ?? 'user'
        ];
        
        $userId = $this->userModel->create($userData);
        
        if ($userId) {
            $user = $this->userModel->findById($userId);
            unset($user['password']);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'User created successfully',
                'user' => $user
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to create user'
            ], 500);
        }
    }

    // Update user
    public function update($id) {
        $this->checkAdmin();
        
        $data = $this->getRequestData();
        $userData = [];
        
        // Email is required
        if (empty($data['email'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Email is required'
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
        
        // Check if email exists and belongs to another user
        $existingUser = $this->userModel->findByEmail($data['email']);
        if ($existingUser && $existingUser['id'] != $id) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Email already registered to another user'
            ], 400);
            return;
        }
        
        // Build update data
        $userData['email'] = strtolower(trim($data['email']));
        
        // Update password if provided
        if (!empty($data['password'])) {
            $userData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        // Update role if provided
        if (isset($data['role'])) {
            // Don't allow user to change their own role
            if ($id == $_SESSION['user_id']) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'You cannot change your own role'
                ], 400);
                return;
            }
            
            $userData['role'] = $data['role'];
        }
        
        $success = $this->userModel->update($id, $userData);
        
        if ($success) {
            $updatedUser = $this->userModel->findById($id);
            unset($updatedUser['password']);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'User updated successfully',
                'user' => $updatedUser
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to update user'
            ], 500);
        }
    }
}