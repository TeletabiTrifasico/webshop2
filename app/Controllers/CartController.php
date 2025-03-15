<?php

namespace App\Controllers;

class CartController extends Controller {
    private $productModel;
    private $orderModel;

    public function __construct() {
        parent::__construct();
        $this->productModel = $this->model('Product');
        $this->orderModel = $this->model('Order');
    }

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /auth/login');
            exit;
        }

        $cart = [];
        $total = 0;

        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $item) {
                $product = $this->productModel->findById($item['productId']);
                if ($product) {
                    $subtotal = $product['price'] * $item['quantity'];
                    $total += $subtotal;
                    $cart[] = [
                        'product' => $product,
                        'quantity' => $item['quantity'],
                        'subtotal' => $subtotal
                    ];
                }
            }
        }

        $this->view('cart/index', [
            'cart' => $cart,
            'total' => $total
        ]);
    }

    public function addItem() {
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Please login to add items to cart']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /products');
            exit;
        }

        $productId = $_POST['product_id'];
        $quantity = (int)($_POST['quantity'] ?? 1);

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Check if product already exists in cart
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['productId'] == $productId) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }

        // If product not found in cart, add it
        if (!$found) {
            $_SESSION['cart'][] = [
                'productId' => $productId,
                'quantity' => $quantity
            ];
        }

        $this->jsonResponse([
            'success' => true,
            'message' => 'Product added to cart',
            'cartCount' => array_reduce($_SESSION['cart'], function($sum, $item) {
                return $sum + $item['quantity'];
            }, 0)
        ]);
    }

    public function removeItem() {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        $productId = $_POST['product_id'];

        // Remove item from cart
        $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($productId) {
            return $item['productId'] != $productId;
        });

        // Get updated cart total
        $cartTotal = array_reduce($_SESSION['cart'], function($sum, $item) {
            return $sum + $item['quantity'];
        }, 0);

        $this->jsonResponse([
            'success' => true,
            'cartCount' => $cartTotal
        ]);
    }

    public function updateQuantity() {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        $productId = $_POST['product_id'];
        $quantity = (int)$_POST['quantity'];

        if ($quantity < 1) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid quantity']);
            return;
        }

        // Update cart quantity
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['productId'] == $productId) {
                $item['quantity'] = $quantity;
                break;
            }
        }

        // Get updated cart total
        $cartTotal = array_reduce($_SESSION['cart'], function($sum, $item) {
            return $sum + $item['quantity'];
        }, 0);

        $this->jsonResponse([
            'success' => true,
            'cartCount' => $cartTotal
        ]);
    }

    public function checkout() {
        require_once '../app/views/orders/checkout.php';
    }

    public function processCheckout() {
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Please login to complete your order']);
            return;
        }

        try {
            $total = 0;
            $orderItems = [];

            foreach ($_SESSION['cart'] as $item) {
                $product = $this->productModel->findById($item['productId']);
                $itemTotal = $product['price'] * $item['quantity'];
                $total += $itemTotal;

                $orderItems[] = [
                    'product_id' => $item['productId'],
                    'quantity' => $item['quantity'],
                    'price' => $product['price']
                ];
            }

            $orderData = [
                'user_id' => $_SESSION['user_id'],
                'total_amount' => $total,
                'status' => 'pending',
                'items' => $orderItems
            ];

            $this->orderModel->create($orderData);
            $_SESSION['cart'] = [];

            $this->jsonResponse([
                'success' => true,
                'message' => 'Order completed successfully!'
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'An error occurred while processing your order.'
            ]);
        }
    }

    private function jsonResponse($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}