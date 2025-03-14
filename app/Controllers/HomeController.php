<?php

namespace App\Controllers;

class HomeController extends Controller {
    public function index() {
        $stmt = $this->pdo->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 3");
        $products = $stmt->fetchAll();
        
        $this->view('home/index', ['products' => $products]);
    }
}