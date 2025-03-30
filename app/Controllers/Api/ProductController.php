<?php

namespace App\Controllers\Api;

class ProductController extends BaseController {
    private $productModel;

    public function __construct() {
        parent::__construct();
        $this->productModel = $this->model('Product');
    }

    public function index() {
        $products = $this->productModel->getAll();
        $this->successResponse(['products' => $products]);
    }

    public function show($id) {
        $product = $this->productModel->findById($id);
        if (!$product) {
            $this->errorResponse('Product not found', 404);
        }
        $this->successResponse(['product' => $product]);
    }

    public function store() {
        if (!$this->isAdmin()) {
            $this->errorResponse('Unauthorized', 401);
        }

        $data = $this->getRequestData();
        
        try {
            $productId = $this->productModel->create($data);
            $product = $this->productModel->findById($productId);
            $this->successResponse(
                ['product' => $product], 
                'Product created successfully'
            );
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    public function update($id) {
        if (!$this->isAdmin()) {
            $this->errorResponse('Unauthorized', 401);
        }

        $data = $this->getRequestData();
        
        try {
            $this->productModel->update($id, $data);
            $product = $this->productModel->findById($id);
            $this->successResponse(
                ['product' => $product], 
                'Product updated successfully'
            );
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    public function destroy($id) {
        if (!$this->isAdmin()) {
            $this->errorResponse('Unauthorized', 401);
        }

        try {
            $this->productModel->delete($id);
            $this->successResponse(null, 'Product deleted successfully');
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    private function getRequestData() {
        return json_decode(file_get_contents('php://input'), true);
    }

    private function isAdmin() {
        return isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin';
    }
}