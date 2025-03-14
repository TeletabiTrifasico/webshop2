<?php

namespace App\Controllers;

class ProductController extends Controller {
    public function index() {
        $stmt = $this->pdo->query("SELECT * FROM products");
        $products = $stmt->fetchAll();
        
        $this->view('products/index', ['products' => $products]);
    }
    
    public function detail($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        
        if (!$product) {
            http_response_code(404);
            $this->view('404');
            return;
        }
        
        $this->view('products/detail', ['product' => $product]);
    }
}