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
            // Get pagination parameters
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
            
            // Use the parent class's handleException method
            parent::handleException($e, 'Failed to fetch products');
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
            parent::handleException($e, 'Failed to fetch product details');
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
            parent::handleException($e, 'Failed to fetch latest products');
        }
    }

    public function featured() {
        $products = $this->productModel->getFeatured();
        $this->jsonResponse(['products' => $products]);
    }

    // Admin methods for product management
    public function create() {
        parent::requireAdmin();
        
        $data = $this->getRequestData();
        
        // Basic validation
        if (empty($data['name']) || empty($data['price'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Name and price are required'
            ], 400);
            return;
        }
        
        try {
            // Handle image upload if provided
            $imagePath = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imagePath = $this->handleImageUpload($_FILES['image']);
                if (!$imagePath) {
                    throw new \Exception('Failed to upload image');
                }
                $data['image'] = $imagePath;
            }
            
            // Create product
            $productId = $this->productModel->create($data);
            
            if ($productId) {
                $product = $this->productModel->findById($productId);
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Product created successfully',
                    'product' => $product
                ]);
            } else {
                throw new \Exception('Failed to create product');
            }
        } catch (\Exception $e) {
            parent::handleException($e, 'Failed to create product');
        }
    }
    
    public function update($id) {
        parent::requireAdmin();
        
        $product = $this->productModel->findById($id);
        if (!$product) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Product not found'
            ], 404);
            return;
        }
        
        $data = $this->getRequestData();
        
        // Basic validation
        if (empty($data['name']) || empty($data['price'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Name and price are required'
            ], 400);
            return;
        }
        
        try {
            // Handle image upload if provided
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imagePath = $this->handleImageUpload($_FILES['image']);
                if (!$imagePath) {
                    throw new \Exception('Failed to upload image');
                }
                $data['image'] = $imagePath;
                
                // Delete old image if there was one
                if ($product['image'] && $product['image'] !== 'default.jpg') {
                    $oldImagePath = __DIR__ . '/../../public/uploads/' . $product['image'];
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
            }
            
            // Update product
            $result = $this->productModel->update($id, $data);
            
            if ($result) {
                $updatedProduct = $this->productModel->findById($id);
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Product updated successfully',
                    'product' => $updatedProduct
                ]);
            } else {
                throw new \Exception('Failed to update product');
            }
        } catch (\Exception $e) {
            parent::handleException($e, 'Failed to update product');
        }
    }
    
    public function delete($id) {
        parent::requireAdmin();
        
        $product = $this->productModel->findById($id);
        if (!$product) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Product not found'
            ], 404);
            return;
        }
        
        try {
            // Delete product image if it's not the default one
            if ($product['image'] && $product['image'] !== 'default.jpg') {
                $imagePath = __DIR__ . '/../../public/uploads/' . $product['image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            $result = $this->productModel->delete($id);
            
            if ($result) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Product deleted successfully'
                ]);
            } else {
                throw new \Exception('Failed to delete product');
            }
        } catch (\Exception $e) {
            parent::handleException($e, 'Failed to delete product');
        }
    }
    
    // Helper method for image upload
    private function handleImageUpload($file) {
        $uploadDir = __DIR__ . '/../../public/uploads/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Generate unique filename
        $filename = md5(uniqid() . time()) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $targetFile = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            return $filename;
        }
        
        return null;
    }
}