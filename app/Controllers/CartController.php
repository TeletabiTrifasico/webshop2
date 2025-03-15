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
        if (isset($_SESSION['cart'])) {
            $cartItems = $_SESSION['cart'];
            foreach ($cartItems as $item) {
                $product = $this->productModel->findById($item['productId']);
                if ($product) {
                    $cart[] = [
                        'product' => $product,
                        'quantity' => $item['quantity']
                    ];
                }
            }
        }

        $this->view('cart/index', ['cart' => $cart]);
    }

    public function addItem($productId) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $exists = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['productId'] == $productId) {
                $item['quantity']++;
                $exists = true;
                break;
            }
        }

        if (!$exists) {
            $_SESSION['cart'][] = [
                'productId' => $productId,
                'quantity' => 1
            ];
        }

        header('Location: /cart');
    }

    public function removeItem($productId) {
        if (isset($_SESSION['cart'])) {
            $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($productId) {
                return $item['productId'] != $productId;
            });
        }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }

        header('Location: /cart');
        exit;
    }

    public function updateQuantity($productId, $quantity) {
        if (!isset($_SESSION['cart'])) {
            return;
        }

        if ($quantity <= 0) {
            $this->removeItem($productId);
            return;
        }

        foreach ($_SESSION['cart'] as &$item) {
            if ($item['productId'] == $productId) {
                $item['quantity'] = $quantity;
                break;
            }
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
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