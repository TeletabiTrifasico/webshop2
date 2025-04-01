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
        
        try {
            // Get user ID from JWT token
            $userData = $this->getAuthUser();
            $userId = $userData['user_id'];
            
            $items = $this->cartModel->getCartItems($userId);
            
            // Format items for frontend
            $formattedItems = [];
            foreach ($items as $item) {
                $formattedItems[] = [
                    'id' => $item['id'],
                    'quantity' => (int)$item['quantity'],
                    'product' => [
                        'id' => $item['product_id'],
                        'name' => $item['name'],
                        'price' => (float)$item['price'],
                        'image' => $item['image']
                    ]
                ];
            }
            
            $this->jsonResponse([
                'success' => true,
                'items' => $formattedItems
            ]);
        } catch (\Exception $e) {
            $this->handleException($e, 'Failed to fetch cart');
        }
    }

    public function addItem() {
        // Require authentication
        $this->requireAuth();
        
        try {
            // Get user ID from JWT token
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
            
            $this->cartModel->addItem($userId, $productId, $quantity);
            $items = $this->cartModel->getCartItems($userId);
            
            // Format items for frontend (same as index method)
            $formattedItems = [];
            foreach ($items as $item) {
                $formattedItems[] = [
                    'id' => $item['id'],
                    'quantity' => (int)$item['quantity'],
                    'product' => [
                        'id' => $item['product_id'],
                        'name' => $item['name'],
                        'price' => (float)$item['price'],
                        'image' => $item['image']
                    ]
                ];
            }
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Item added to cart',
                'items' => $formattedItems
            ]);
        } catch (\Exception $e) {
            $this->handleException($e, 'Failed to add item to cart');
        }
    }
    
    public function updateItem() {
        $this->requireAuth();
        
        try {
            $userData = $this->getAuthUser();
            $userId = $userData['user_id'];
            
            $data = $this->getRequestData();
            
            if (!isset($data['item_id']) || !isset($data['quantity'])) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Item ID and quantity are required'
                ], 400);
                return;
            }
            
            $itemId = (int)$data['item_id'];
            $quantity = max(1, (int)$data['quantity']);
            
            $result = $this->cartModel->updateItem($itemId, $userId, $quantity);
            
            if (!$result) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Failed to update cart item'
                ], 500);
                return;
            }
            
            $items = $this->cartModel->getCartItems($userId);
            
            // Format items for frontend
            $formattedItems = [];
            foreach ($items as $item) {
                $formattedItems[] = [
                    'id' => $item['id'],
                    'quantity' => (int)$item['quantity'],
                    'product' => [
                        'id' => $item['product_id'],
                        'name' => $item['name'],
                        'price' => (float)$item['price'],
                        'image' => $item['image']
                    ]
                ];
            }
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Cart updated successfully',
                'items' => $formattedItems
            ]);
        } catch (\Exception $e) {
            $this->handleException($e, 'Failed to update cart');
        }
    }
    
    public function removeItem() {
        $this->requireAuth();
        
        try {
            $userData = $this->getAuthUser();
            $userId = $userData['user_id'];
            
            $data = $this->getRequestData();
            
            if (!isset($data['item_id'])) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Item ID is required'
                ], 400);
                return;
            }
            
            $itemId = (int)$data['item_id'];
            
            $result = $this->cartModel->removeItem($itemId, $userId);
            
            if (!$result) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Failed to remove cart item'
                ], 500);
                return;
            }
            
            $items = $this->cartModel->getCartItems($userId);
            
            // Format items for frontend
            $formattedItems = [];
            foreach ($items as $item) {
                $formattedItems[] = [
                    'id' => $item['id'],
                    'quantity' => (int)$item['quantity'],
                    'product' => [
                        'id' => $item['product_id'],
                        'name' => $item['name'],
                        'price' => (float)$item['price'],
                        'image' => $item['image']
                    ]
                ];
            }
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Item removed successfully',
                'items' => $formattedItems
            ]);
        } catch (\Exception $e) {
            $this->handleException($e, 'Failed to remove item from cart');
        }
    }
    
    public function clearCart() {
        $this->requireAuth();
        
        try {
            $userData = $this->getAuthUser();
            $userId = $userData['user_id'];
            
            $result = $this->cartModel->clearCart($userId);
            
            if (!$result) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Failed to clear cart'
                ], 500);
                return;
            }
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Cart cleared successfully'
            ]);
        } catch (\Exception $e) {
            $this->handleException($e, 'Failed to clear cart');
        }
    }
    
    public function checkout() {
        $this->requireAuth();
        
        try {
            $userData = $this->getAuthUser();
            $userId = $userData['user_id'];
            
            // Get cart items
            $cartItems = $this->cartModel->getCartItems($userId);
            
            if (empty($cartItems)) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Your cart is empty'
                ], 400);
                return;
            }
            
            // Get request data for shipping info
            $data = $this->getRequestData();
            
            // Create transaction
            $orderModel = $this->model('Order');
            $orderItemModel = $this->model('OrderItem');
            
            // Create order
            $orderData = [
                'user_id' => $userId,
                'status' => 'pending',
                'total_amount' => $this->cartModel->getTotal($userId),
                'shipping_address' => json_encode([
                    'firstName' => $data['firstName'] ?? '',
                    'lastName' => $data['lastName'] ?? '',
                    'address' => $data['address'] ?? '',
                    'city' => $data['city'] ?? '',
                    'state' => $data['state'] ?? '',
                    'zip' => $data['zip'] ?? '',
                    'email' => $data['email'] ?? '',
                    'phone' => $data['phone'] ?? ''
                ]),
                'notes' => $data['notes'] ?? ''
            ];
            
            $orderId = $orderModel->create($orderData);
            
            if (!$orderId) {
                throw new \Exception('Failed to create order');
            }
            
            // Add order items
            foreach ($cartItems as $item) {
                $orderItemModel->create([
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ]);
            }
            
            // Clear cart after successful order
            $this->cartModel->clearCart($userId);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Order placed successfully',
                'order_id' => $orderId
            ]);
        } catch (\Exception $e) {
            $this->handleException($e, 'Failed to complete checkout');
        }
    }
    
    public function getCartItems() {
        $this->requireAuth();
        
        try {
            $userData = $this->getAuthUser();
            $userId = $userData['user_id'];
            
            $items = $this->cartModel->getCartItems($userId);
            
            // Format items for frontend
            $formattedItems = [];
            foreach ($items as $item) {
                $formattedItems[] = [
                    'id' => $item['id'],
                    'quantity' => (int)$item['quantity'],
                    'product' => [
                        'id' => $item['product_id'],
                        'name' => $item['name'],
                        'price' => (float)$item['price'],
                        'image' => $item['image']
                    ]
                ];
            }
            
            $this->jsonResponse([
                'success' => true,
                'items' => $formattedItems
            ]);
        } catch (\Exception $e) {
            $this->handleException($e, 'Failed to fetch cart items');
        }
    }
}