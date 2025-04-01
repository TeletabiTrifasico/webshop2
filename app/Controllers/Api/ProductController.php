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
            // Get pagination parameters with explicit defaults and type casting
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 10;
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            
            // Log the request parameters for debugging
            error_log("Products index: page={$page}, limit={$limit}, search={$search}");
            
            // Get paginated products
            $products = $this->productModel->getAll($page, $limit, $search);
            
            // Get total for pagination metadata
            $total = $this->productModel->getTotal($search);
            
            // Calculate pagination values with basic sanity checks
            $lastPage = $total > 0 ? ceil($total / $limit) : 1;
            $from = $total > 0 ? ($page - 1) * $limit + 1 : 0;
            $to = min($page * $limit, $total);
            
            $this->jsonResponse([
                'success' => true,
                'products' => $products,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $limit,
                    'current_page' => $page,
                    'last_page' => $lastPage,
                    'from' => $from,
                    'to' => $to
                ]
            ]);
        } catch (\Exception $e) {
            error_log("Products index error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            $this->handleException($e, 'Failed to fetch products');
        }
    }

    public function show($id) {
        try {
            $product = $this->productModel->findById($id);
            if (!$product) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Product not found'
                ], 404);
                return;
            }
            
            $this->jsonResponse([
                'success' => true,
                'product' => $product
            ]);
        } catch (\Exception $e) {
            $this->handleException($e, 'Failed to fetch product details');
        }
    }

    public function latest() {
        try {
            $limit = isset($_GET['limit']) ? min(20, max(1, (int)$_GET['limit'])) : 4;
            
            $products = $this->productModel->getLatest($limit);
            
            $this->jsonResponse([
                'success' => true,
                'products' => $products
            ]);
        } catch (\Exception $e) {
            $this->handleException($e, 'Failed to fetch latest products');
        }
    }

    public function featured() {
        $products = $this->productModel->getFeatured();
        $this->jsonResponse(['products' => $products]);
    }

    private function handleException(\Exception $e, $defaultMessage) {
        error_log($defaultMessage . ": " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        $this->jsonResponse([
            'success' => false,
            'error' => $defaultMessage,
            'message' => $e->getMessage(),
            'trace' => APP_ENV === 'development' ? $e->getTraceAsString() : null
        ], 500);
    }
}