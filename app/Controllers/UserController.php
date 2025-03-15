<?php

namespace App\Controllers;

class UserController extends Controller {
    public function __construct() {
        parent::__construct();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /auth/login');
            exit;
        }
    }

    public function profile() {
        $stmt = $this->pdo->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

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

        // Verify current password if trying to change password
        if (!empty($newPassword)) {
            $stmt = $this->pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            if (!password_verify($currentPassword, $user['password'])) {
                $this->view('user/profile', [
                    'error' => 'Current password is incorrect',
                    'user' => ['username' => $username, 'email' => $email]
                ]);
                return;
            }
        }

        try {
            if (!empty($newPassword)) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $this->pdo->prepare("
                    UPDATE users 
                    SET username = ?, email = ?, password = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$username, $email, $hashedPassword, $_SESSION['user_id']]);
            } else {
                $stmt = $this->pdo->prepare("
                    UPDATE users 
                    SET username = ?, email = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$username, $email, $_SESSION['user_id']]);
            }

            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;

            // Fetch updated user data and show success message
            $stmt = $this->pdo->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            $this->view('user/profile', [
                'user' => $user,
                'success' => 'Profile updated successfully!' . (!empty($newPassword) ? ' Password has been changed.' : '')
            ]);

        } catch (\PDOException $e) {
            $this->view('user/profile', [
                'error' => 'Email already exists',
                'user' => ['username' => $username, 'email' => $email]
            ]);
        }
    }
}