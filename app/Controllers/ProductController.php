<?php

namespace App\Controllers;

class ProductController extends Controller {
    private $productModel;

    public function __construct() {
        parent::__construct();
        $this->productModel = $this->model('Product');
    }

    public function index() {
        $products = $this->productModel->getAll();
        $this->view('products/index', ['products' => $products]);
    }
    
    public function detail($id) {
        $product = $this->productModel->findById($id);
        
        if (!$product) {
            http_response_code(404);
            $this->view('404');
            return;
        }
        
        $this->view('products/detail', ['product' => $product]);
    }
}