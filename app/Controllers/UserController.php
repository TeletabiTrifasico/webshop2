<?php

namespace App\Controllers;

class UserController extends Controller {
    private $userModel;

    public function __construct() {
        parent::__construct();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /auth/login');
            exit;
        }
        $this->userModel = $this->model('User');
    }

    public function profile() {
        $user = $this->userModel->findById($_SESSION['user_id']);
        $this->view('user/profile', ['user' => $user]);
    }

    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /user/profile');
            exit;
        }

        $username = $_POST['username'];
        $email = $_POST['email'];
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';

        try {
            // If user wants to change password
            if (!empty($newPassword)) {
                $user = $this->userModel->findById($_SESSION['user_id']);
                
                if (!password_verify($currentPassword, $user['password'])) {
                    $this->view('user/profile', [
                        'error' => 'Current password is incorrect',
                        'user' => ['username' => $username, 'email' => $email]
                    ]);
                    return;
                }

                $userData = [
                    'username' => $username,
                    'email' => $email,
                    'password' => $newPassword
                ];

                $this->userModel->updateWithPassword($_SESSION['user_id'], $userData);
            } else {
                $userData = [
                    'username' => $username,
                    'email' => $email
                ];

                $this->userModel->update($_SESSION['user_id'], $userData);
            }

            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;

            $user = $this->userModel->findById($_SESSION['user_id']);
            
            $this->view('user/profile', [
                'user' => $user,
                'success' => 'Profile updated successfully!' . 
                    (!empty($newPassword) ? ' Password has been changed.' : '')
            ]);

        } catch (\PDOException $e) {
            $this->view('user/profile', [
                'error' => 'Email already exists',
                'user' => ['username' => $username, 'email' => $email]
            ]);
        }
    }
}