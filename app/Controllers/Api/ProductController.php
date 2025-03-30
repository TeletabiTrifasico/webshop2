<?php

namespace App\Controllers\Api;

class ProductController extends BaseApiController {
    private $productModel;

    public function __construct() {
        parent::__construct();
        $this->productModel = $this->model('Product');
    }

    public function index() {
        try {
            $products = $this->productModel->getAll();
            $this->jsonResponse(['products' => $products]);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => 'Failed to fetch products'], 500);
        }
    }

    public function show($id) {
        $product = $this->productModel->findById($id);
        if (!$product) {
            $this->jsonResponse(['error' => 'Product not found'], 404);
        }
        $this->jsonResponse(['product' => $product]);
    }

    public function latest() {
        try {
            $products = $this->productModel->getLatest(4);
            
            if (!$products) {
                throw new \Exception("No products found");
            }

            $this->jsonResponse([
                'success' => true,
                'products' => $products
            ]);
        } catch (\Exception $e) {
            error_log("Latest products error: " . $e->getMessage());
            
            $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to fetch latest products',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function featured() {
        $products = $this->productModel->getFeatured();
        $this->jsonResponse(['products' => $products]);
    }
}