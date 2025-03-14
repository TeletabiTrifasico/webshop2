<?php

namespace App\Controllers;

class CartController extends Controller {
    public function __construct() {
        parent::__construct();
    }

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /auth/login');
            exit;
        }

        $cart = [];
        if (isset($_SESSION['cart'])) {
            $ids = array_column($_SESSION['cart'], 'productId');
            if (!empty($ids)) {
                $placeholders = str_repeat('?,', count($ids) - 1) . '?';
                $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
                $stmt->execute($ids);
                $products = $stmt->fetchAll();

                foreach ($products as $product) {
                    $cartItem = array_filter($_SESSION['cart'], function($item) use ($product) {
                        return $item['productId'] == $product['id'];
                    });
                    $cartItem = reset($cartItem);
                    
                    $cart[] = [
                        'product' => $product,
                        'quantity' => $cartItem['quantity']
                    ];
                }
            }
        }

        echo "<script>
            document.addEventListener('DOMContentLoaded', () => {
                const cart = " . json_encode($_SESSION['cart'] ?? []) . ";
                sessionStorage.setItem('cart', JSON.stringify(cart));
                updateCartDisplay();
            });
        </script>";

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
}