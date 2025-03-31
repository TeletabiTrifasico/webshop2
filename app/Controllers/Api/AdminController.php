<?php

namespace App\Controllers\Api;

class AdminController extends BaseApiController {
    private $userModel;
    private $productModel;
    private $orderModel;
    private $orderItemModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = $this->model('User');
        $this->productModel = $this->model('Product');
        $this->orderModel = $this->model('Order');
        $this->orderItemModel = $this->model('OrderItem');
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

    // Update user role
    public function updateUserRole($userId) {
        $this->checkAdmin();
        
        $data = $this->getRequestData();
        
        if (!isset($data['role']) || !in_array($data['role'], ['admin', 'user'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Invalid role'
            ], 400);
            return;
        }
        
        // Don't allow changing own role
        if ($userId == $_SESSION['user_id']) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Cannot change your own role'
            ], 400);
            return;
        }
        
        $result = $this->userModel->update($userId, ['role' => $data['role']]);
        
        if ($result) {
            $this->jsonResponse([
                'success' => true,
                'message' => 'User role updated successfully'
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to update user role'
            ], 500);
        }
    }
    
    // Delete user
    public function deleteUser($userId) {
        $this->checkAdmin();
        
        // Don't allow deleting own account
        if ($userId == $_SESSION['user_id']) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Cannot delete your own account'
            ], 400);
            return;
        }
        
        $result = $this->userModel->delete($userId);
        
        if ($result) {
            $this->jsonResponse([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to delete user'
            ], 500);
        }
    }

    // Get all orders
    public function getOrders() {
        $this->checkAdmin();
        
        $orders = $this->orderModel->getAllWithUserDetails();
        
        $this->jsonResponse([
            'success' => true,
            'orders' => $orders
        ]);
    }
    
    // Get order details
    public function getOrderDetails($orderId) {
        $this->checkAdmin();
        
        $order = $this->orderModel->getWithDetails($orderId);
        
        if (!$order) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Order not found'
            ], 404);
            return;
        }
        
        $items = $this->orderItemModel->getByOrderId($orderId);
        
        $this->jsonResponse([
            'success' => true,
            'order' => $order,
            'items' => $items
        ]);
    }
    
    // Update order status
    public function updateOrderStatus($orderId) {
        $this->checkAdmin();
        
        $data = $this->getRequestData();
        
        if (!isset($data['status'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Status is required'
            ], 400);
            return;
        }
        
        $result = $this->orderModel->updateStatus($orderId, $data['status']);
        
        if ($result) {
            $this->jsonResponse([
                'success' => true,
                'message' => 'Order status updated successfully'
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to update order status'
            ], 500);
        }
    }
    
    // Delete order
    public function deleteOrder($orderId) {
        $this->checkAdmin();
        
        $result = $this->orderModel->delete($orderId);
        
        if ($result) {
            $this->jsonResponse([
                'success' => true,
                'message' => 'Order deleted successfully'
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to delete order'
            ], 500);
        }
    }
}