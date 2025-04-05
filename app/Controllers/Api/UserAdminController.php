<?php

namespace App\Controllers\Api;

class UserAdminController extends BaseApiController {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = $this->model('User');
    }

    // Get user by ID
    public function get($id) {
        $this->requireAdmin();
        
        try {
            $user = $this->userModel->findById($id);
            
            if (!$user) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'User not found'
                ], 404);
                return;
            }
            
            // Don't expose password
            unset($user['password']);
            
            $this->jsonResponse([
                'success' => true,
                'user' => $user
            ]);
        } catch (\Exception $e) {
            error_log('Error fetching user: ' . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to fetch user'
            ], 500);
        }
    }

    // Create new user
    public function create() {
        $this->requireAdmin();
        
        try {
            $data = $this->getRequestData();
            
            // Basic validation
            if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Username, email, and password are required'
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
            
            // Create user with the provided data
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
        } catch (\Exception $e) {
            error_log('Error creating user: ' . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to create user: ' . $e->getMessage()
            ], 500);
        }
    }

    // Update existing user
    public function update($id) {
        $this->requireAdmin();
        
        try {
            // Check if user exists
            $user = $this->userModel->findById($id);
            if (!$user) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'User not found'
                ], 404);
                return;
            }
            
            $data = $this->getRequestData();
            $userData = [];
            
            // Update email if provided
            if (!empty($data['email'])) {
                // Check if email exists and belongs to another user
                $existingUser = $this->userModel->findByEmail($data['email']);
                if ($existingUser && $existingUser['id'] != $id) {
                    $this->jsonResponse([
                        'success' => false,
                        'error' => 'Email already registered to another user'
                    ], 400);
                    return;
                }
                
                $userData['email'] = strtolower(trim($data['email']));
            }
            
            // Update password if provided
            if (!empty($data['password'])) {
                $userData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            
            // Update role if provided
            if (isset($data['role'])) {
                // Don't allow users to change their own role
                $authUser = $this->getAuthUser();
                if ($id == $authUser['user_id'] && $data['role'] != $authUser['role']) {
                    $this->jsonResponse([
                        'success' => false,
                        'error' => 'You cannot change your own role'
                    ], 400);
                    return;
                }
                
                $userData['role'] = $data['role'];
            }
            
            // Only update if there are changes
            if (empty($userData)) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'No changes to update',
                    'user' => $user
                ]);
                return;
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
        } catch (\Exception $e) {
            error_log('Error updating user: ' . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to update user: ' . $e->getMessage()
            ], 500);
        }
    }
}