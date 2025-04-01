<?php

namespace App\Controllers\Api;

class CartController extends BaseApiController {
    private $cartModel;
    private $productModel;

    public function __construct() {
        parent::__construct();
        $this->cartModel = $this->model('Cart');
        $this->productModel = $this->model('Product');
    }

    public function index() {
        // Require authentication
        $this->requireAuth();
        
        // Get user ID from token
        $userData = $this->getAuthUser();
        $userId = $userData['user_id'];
        
        try {
            $items = $this->cartModel->getCartItems($userId);
            
            $this->jsonResponse([
                'success' => true,
                'items' => $items
            ]);
        } catch (\Exception $e) {
            $this->handleException($e, 'Failed to fetch cart');
        }
    }

    public function addItem() {
        // Require authentication
        $this->requireAuth();
        
        // Get user ID from token
        $userData = $this->getAuthUser();
        $userId = $userData['user_id'];
        
        $data = $this->getRequestData();
        
        if (!isset($data['product_id']) || !isset($data['quantity'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Product ID and quantity are required'
            ], 400);
            return;
        }
        
        $productId = (int)$data['product_id'];
        $quantity = max(1, (int)$data['quantity']);
        
        // Check if product exists
        $product = $this->productModel->findById($productId);
        if (!$product) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Product not found'
            ], 404);
            return;
        }
        
        try {
            $this->cartModel->addItem($userId, $productId, $quantity);
            $items = $this->cartModel->getCartItems($userId);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Item added to cart',
                'items' => $items
            ]);
        } catch (\Exception $e) {
            $this->handleException($e, 'Failed to add item to cart');
        }
    }
}