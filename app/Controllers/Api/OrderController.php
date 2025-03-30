<?php

namespace App\Controllers\Api;

class OrderController extends BaseController {
    private $orderModel;

    public function __construct() {
        parent::__construct();
        $this->orderModel = $this->model('Order');
    }

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            $this->errorResponse('Unauthorized', 401);
        }

        $isAdmin = $_SESSION['role'] === 'admin';
        $orders = $isAdmin 
            ? $this->orderModel->getAllWithUserDetails()
            : $this->orderModel->getByUserId($_SESSION['user_id']);

        $this->successResponse(['orders' => $orders]);
    }

    public function show($id) {
        if (!isset($_SESSION['user_id'])) {
            $this->errorResponse('Unauthorized', 401);
        }

        $order = $this->orderModel->getWithDetails($id);
        
        if (!$order) {
            $this->errorResponse('Order not found', 404);
        }

        if (!$this->canAccessOrder($order)) {
            $this->errorResponse('Unauthorized', 401);
        }

        $orderItems = $this->orderModel->getItems($id);
        $order['items'] = $orderItems;

        $this->successResponse(['order' => $order]);
    }

    public function store() {
        if (!isset($_SESSION['user_id'])) {
            $this->errorResponse('Unauthorized', 401);
        }

        $data = $this->getRequestData();
        $data['user_id'] = $_SESSION['user_id'];

        try {
            $orderId = $this->orderModel->create($data);
            $this->successResponse(
                ['orderId' => $orderId],
                'Order created successfully'
            );
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    public function updateStatus($id) {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            $this->errorResponse('Unauthorized', 401);
        }

        $data = $this->getRequestData();
        
        try {
            $this->orderModel->updateStatus($id, $data['status']);
            $this->successResponse(null, 'Order status updated successfully');
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    private function getRequestData() {
        return json_decode(file_get_contents('php://input'), true);
    }

    private function canAccessOrder($order) {
        return $_SESSION['role'] === 'admin' || 
               $order['user_id'] === $_SESSION['user_id'];
    }
}