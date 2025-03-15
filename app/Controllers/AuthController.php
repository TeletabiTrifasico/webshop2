<?php

namespace App\Controllers;

class AuthController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function login()
    {
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
                $_SESSION['role'] = $user['role'];  // Add role to session
                header('Location: /');
                exit;
            } else {
                $this->view('auth/login', ['error' => 'Invalid email or password']);
                return;
            }
        }

        $this->view('auth/login');
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];

            if ($password !== $confirmPassword) {
                $this->view('auth/register', ['error' => 'Passwords do not match']);
                return;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            try {
                // Add role column to insert
                $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
                $stmt->execute([$username, $email, $hashedPassword]);
                
                $_SESSION['user_id'] = $this->pdo->lastInsertId();
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = 'user';  // Set default role
                
                header('Location: /');
                exit;
            } catch (\PDOException $e) {
                $this->view('auth/register', ['error' => 'Email already exists']);
                return;
            }
        }

        $this->view('auth/register');
    }

    public function logout()
    {
        session_destroy();
        header('Location: /');
        exit;
    }
}