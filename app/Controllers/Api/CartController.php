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
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'User not authenticated'
            ], 401);
            return;
        }

        $userId = $_SESSION['user_id'];
        $cartItems = $this->cartModel->getByUserId($userId);

        $items = [];
        foreach ($cartItems as $item) {
            $items[] = [
                'id' => $item['id'],
                'quantity' => $item['quantity'],
                'product' => [
                    'id' => $item['product_id'],
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'image' => $item['image']
                ]
            ];
        }

        $this->jsonResponse([
            'success' => true,
            'items' => $items
        ]);
    }

    public function add() {
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'User not authenticated'
            ], 401);
            return;
        }

        $data = $this->getRequestData();
        $userId = $_SESSION['user_id'];

        if (!isset($data['product_id']) || !isset($data['quantity'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Product ID and quantity are required'
            ], 400);
            return;
        }

        $product = $this->productModel->findById($data['product_id']);
        if (!$product) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Product not found'
            ], 404);
            return;
        }

        $result = $this->cartModel->addItem(
            $userId,
            $data['product_id'],
            intval($data['quantity'])
        );

        if ($result) {
            $this->jsonResponse([
                'success' => true,
                'message' => 'Item added to cart'
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to add item to cart'
            ], 500);
        }
    }

    public function update() {
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'User not authenticated'
            ], 401);
            return;
        }

        $data = $this->getRequestData();
        $userId = $_SESSION['user_id'];

        if (!isset($data['item_id']) || !isset($data['quantity'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Item ID and quantity are required'
            ], 400);
            return;
        }

        $result = $this->cartModel->updateQuantity(
            $userId,
            $data['item_id'],
            intval($data['quantity'])
        );

        if ($result) {
            $this->jsonResponse([
                'success' => true,
                'message' => 'Cart updated'
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to update cart'
            ], 500);
        }
    }

    public function remove() {
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'User not authenticated'
            ], 401);
            return;
        }

        $data = $this->getRequestData();
        $userId = $_SESSION['user_id'];

        if (!isset($data['item_id'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Item ID is required'
            ], 400);
            return;
        }

        $result = $this->cartModel->removeItem($userId, $data['item_id']);

        if ($result) {
            $this->jsonResponse([
                'success' => true,
                'message' => 'Item removed from cart'
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to remove item'
            ], 500);
        }
    }

    public function clear() {
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'User not authenticated'
            ], 401);
            return;
        }

        $userId = $_SESSION['user_id'];
        $result = $this->cartModel->clearCart($userId);

        if ($result) {
            $this->jsonResponse([
                'success' => true,
                'message' => 'Cart cleared'
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to clear cart'
            ], 500);
        }
    }

    public function checkout() {
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'User not authenticated'
            ], 401);
            return;
        }

        $userId = $_SESSION['user_id'];
        $cartItems = $this->cartModel->getByUserId($userId);

        if (empty($cartItems)) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Cart is empty'
            ], 400);
            return;
        }

        // Create order
        $orderModel = $this->model('Order');
        
        $items = [];
        foreach ($cartItems as $item) {
            $items[] = [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price']
            ];
        }

        try {
            $orderId = $orderModel->create([
                'user_id' => $userId,
                'items' => $items
            ]);

            // Clear cart after successful order
            $this->cartModel->clearCart($userId);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Order placed successfully',
                'order_id' => $orderId
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to create order: ' . $e->getMessage()
            ], 500);
        }
    }
}