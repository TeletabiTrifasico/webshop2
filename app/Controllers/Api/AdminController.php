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

    // Get dashboard stats
    public function getDashboardStats() {
        parent::requireAdmin();
        
        try {
            $totalUsers = $this->userModel->getTotal();
            $totalOrders = $this->orderModel->getTotal();
            $revenue = $this->orderModel->getTotalRevenue();
            $totalProducts = $this->productModel->getTotal();
            
            $this->jsonResponse([
                'success' => true,
                'stats' => [
                    'totalUsers' => $totalUsers,
                    'totalOrders' => $totalOrders,
                    'revenue' => $revenue,
                    'totalProducts' => $totalProducts
                ]
            ]);
        } catch (\Exception $e) {
            $this->handleException($e, 'Failed to fetch dashboard stats');
        }
    }

    // Get all users
    public function getUsers() {
        parent::requireAdmin();
        
        // Get pagination parameters
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 10;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        // Get paginated users
        $users = $this->userModel->getAll($page, $limit, $search);
        
        // Get total for pagination metadata
        $total = $this->userModel->getTotal($search);
        
        // Remove password from response
        foreach ($users as &$user) {
            unset($user['password']);
        }
        
        $this->jsonResponse([
            'success' => true,
            'users' => $users,
            'pagination' => [
                'total' => $total,
                'per_page' => $limit,
                'current_page' => $page,
                'last_page' => ceil($total / $limit),
                'from' => ($page - 1) * $limit + 1,
                'to' => min($page * $limit, $total)
            ]
        ]);
    }

    // Update user role
    public function updateUserRole($userId) {
        parent::requireAdmin();
        
        $data = $this->getRequestData();
        
        if (!isset($data['role']) || !in_array($data['role'], ['admin', 'user'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Invalid role'
            ], 400);
            return;
        }
        
        // Check if user is trying to change their own role
        $authUser = $this->getAuthUser();
        if ($userId == $authUser['user_id']) {
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
        parent::requireAdmin();
        
        // Don't allow deleting own account
        $authUser = $this->getAuthUser();
        if ($userId == $authUser['user_id']) {
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
        parent::requireAdmin();
        
        // Get pagination parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        // Validate page and limit
        if ($page < 1) $page = 1;
        if ($limit < 1 || $limit > 100) $limit = 10;
        
        // Get paginated orders
        $orders = $this->orderModel->getAllWithUserDetails($page, $limit, $search);
        
        // Get total for pagination metadata
        $total = $this->orderModel->getTotal($search);
        
        $this->jsonResponse([
            'success' => true,
            'orders' => $orders,
            'pagination' => [
                'total' => $total,
                'per_page' => $limit,
                'current_page' => $page,
                'last_page' => ceil($total / $limit),
                'from' => ($page - 1) * $limit + 1,
                'to' => min($page * $limit, $total)
            ]
        ]);
    }
    
    // Get order details
    public function getOrderDetails($orderId) {
        parent::requireAdmin();
        
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
        parent::requireAdmin();
        
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
        parent::requireAdmin();
        
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