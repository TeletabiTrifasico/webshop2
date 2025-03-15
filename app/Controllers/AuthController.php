<?php

namespace App\Controllers;

class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = $this->model('User');
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                header('Location: /');
                exit;
            }

            $this->view('auth/login', ['error' => 'Invalid email or password']);
            return;
        }

        $this->view('auth/login');
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($_POST['password'] !== $_POST['confirm_password']) {
                $this->view('auth/register', ['error' => 'Passwords do not match']);
                return;
            }

            try {
                $userData = [
                    'username' => $_POST['username'],
                    'email' => $_POST['email'],
                    'password' => $_POST['password'],
                    'role' => 'user'
                ];

                $this->userModel->create($userData);
                
                // Log the user in
                $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$_POST['email']]);
                $user = $stmt->fetch();

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                header('Location: /');
                exit;
            } catch (\PDOException $e) {
                $this->view('auth/register', ['error' => 'Email already exists']);
                return;
            }
        }

        $this->view('auth/register');
    }

    public function logout() {
        session_destroy();
        header('Location: /');
        exit;
    }
}