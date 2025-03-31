<?php

namespace App\Controllers\Api;

class AdminController extends BaseApiController {
    private $userModel;
    private $productModel;
    private $orderModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = $this->model('User');
        $this->productModel = $this->model('Product');
        $this->orderModel = $this->model('Order');
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

    // Get all users
    public function getUsers() {
        $this->checkAdmin();
        
        $users = $this->userModel->getAll();
        
        foreach ($users as &$user) {
            unset($user['password']);
        }
        
        $this->jsonResponse([
            'success' => true,
            'users' => $users
        ]);
    }

    // Get all orders
    public function getOrders() {
        $this->checkAdmin();
        
        $orders = $this->orderModel->getAll();
        
        $this->jsonResponse([
            'success' => true,
            'orders' => $orders
        ]);
    }
}