<?php

namespace App\Controllers;

class ApiController extends Controller {
    private function sendJson($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    public function products() {
        $productModel = $this->model('Product');
        $products = $productModel->getAll();
        $this->sendJson(['products' => $products]);
    }

    public function product($id) {
        $productModel = $this->model('Product');
        $product = $productModel->findById($id);

        if (!$product) {
            $this->sendJson(['error' => 'Product not found'], 404);
        }

        $this->sendJson(['product' => $product]);
    }

    public function categories() {
        $productModel = $this->model('Product');
        $categories = $productModel->getAllCategories();
        $this->sendJson(['categories' => $categories]);
    }

    public function featuredProducts() {
        $productModel = $this->model('Product');
        $products = $productModel->getFeatured();
        $this->sendJson(['products' => $products]);
    }

    public function cart() {
        if (!isset($_SESSION['user_id'])) {
            $this->sendJson(['error' => 'Unauthorized'], 401);
        }

        $cartModel = $this->model('Cart');
        $cart = $cartModel->getByUserId($_SESSION['user_id']);
        $this->sendJson(['cart' => $cart]);
    }

    public function orders() {
        if (!isset($_SESSION['user_id'])) {
            $this->sendJson(['error' => 'Unauthorized'], 401);
        }

        $orderModel = $this->model('Order');
        $orders = $orderModel->getByUserId($_SESSION['user_id']);
        $this->sendJson(['orders' => $orders]);
    }

    public function createOrder() {
        if (!isset($_SESSION['user_id'])) {
            $this->sendJson(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $orderModel = $this->model('Order');
        
        try {
            $orderId = $orderModel->create([
                'user_id' => $_SESSION['user_id'],
                'items' => $data['items']
            ]);
            $this->sendJson(['success' => true, 'orderId' => $orderId]);
        } catch (\Exception $e) {
            $this->sendJson(['error' => $e->getMessage()], 400);
        }
    }

    public function updateOrderStatus($id) {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            $this->sendJson(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $orderModel = $this->model('Order');
        
        try {
            $orderModel->updateStatus($id, $data['status']);
            $this->sendJson(['success' => true]);
        } catch (\Exception $e) {
            $this->sendJson(['error' => $e->getMessage()], 400);
        }
    }

    public function login() {
        $data = json_decode(file_get_contents('php://input'), true);
        $userModel = $this->model('User');
        
        $user = $userModel->findByEmail($data['email']);
        if ($user && password_verify($data['password'], $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            
            unset($user['password']); // Don't send password to client
            $this->sendJson(['success' => true, 'user' => $user]);
        } else {
            $this->sendJson(['error' => 'Invalid credentials'], 401);
        }
    }

    public function register() {
        $data = json_decode(file_get_contents('php://input'), true);
        $userModel = $this->model('User');
        
        try {
            $userId = $userModel->create([
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password']
            ]);
            
            $user = $userModel->findById($userId);
            unset($user['password']); // Don't send password to client
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            
            $this->sendJson(['success' => true, 'user' => $user]);
        } catch (\Exception $e) {
            $this->sendJson(['error' => $e->getMessage()], 400);
        }
    }

    public function logout() {
        session_destroy();
        $this->sendJson(['success' => true]);
    }
}