<?php

namespace App\Controllers;

class ApiController extends Controller {
    private function sendJson($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    public function products() {
        $stmt = $this->pdo->query("SELECT * FROM products");
        $products = $stmt->fetchAll();
        $this->sendJson(['products' => $products]);
    }

    public function product($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();

        if (!$product) {
            $this->sendJson(['error' => 'Product not found'], 404);
        }

        $this->sendJson(['product' => $product]);
    }

    public function cart() {
        if (!isset($_SESSION['user_id'])) {
            $this->sendJson(['error' => 'Unauthorized'], 401);
        }

        if (isset($_SESSION['cart'])) {
            $this->sendJson(['cart' => $_SESSION['cart']]);
        }

        $this->sendJson(['cart' => []]);
    }
}