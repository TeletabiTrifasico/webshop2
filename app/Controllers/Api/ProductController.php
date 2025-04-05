<?php

namespace App\Controllers\Api;

class ProductController extends BaseApiController {
    private $productModel;

    public function __construct() {
        parent::__construct();
        $this->productModel = $this->model('Product');
    }

    // List all products with pagination
    public function index() {
        // Get pagination parameters
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = isset($_GET['per_page']) ? min(100, max(1, (int)$_GET['per_page'])) : 10;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        try {
            $products = $this->productModel->getAll($page, $perPage, $search);
            $total = $this->productModel->getTotal($search);
            
            $this->jsonResponse([
                'success' => true,
                'products' => $products,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => ceil($total / $perPage)
                ]
            ]);
        } catch (\Exception $e) {
            error_log("Error fetching products: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to fetch products'
            ], 500);
        }
    }

    // Get a single product
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
            error_log("Error fetching product: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to fetch product'
            ], 500);
        }
    }

    // Get latest products
    public function latest() {
        try {
            $limit = isset($_GET['limit']) ? min(10, max(1, (int)$_GET['limit'])) : 4;
            $products = $this->productModel->getLatest($limit);
            
            $this->jsonResponse([
                'success' => true,
                'products' => $products
            ]);
        } catch (\Exception $e) {
            error_log("Error fetching latest products: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to load products'
            ], 500);
        }
    }

    // Create a new product (admin only)
    public function create() {
        $this->requireAdmin();
        
        try {
            $data = $this->getRequestData();
            
            // Basic validation
            if (empty($data['name']) || !isset($data['price'])) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Name and price are required'
                ], 400);
                return;
            }
            
            // Prepare product data
            $productData = [
                'name' => trim($data['name']),
                'description' => $data['description'] ?? '',
                'price' => (float)$data['price'],
                'image' => $data['image'] ?? null
            ];
            
            // Handle image upload (if applicable for your implementation)
            
            $productId = $this->productModel->create($productData);
            
            if ($productId) {
                $product = $this->productModel->findById($productId);
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Product created successfully',
                    'product' => $product
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Failed to create product'
                ], 500);
            }
        } catch (\Exception $e) {
            error_log("Error creating product: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to create product: ' . $e->getMessage()
            ], 500);
        }
    }

    // Update an existing product (admin only)
    public function update($id) {
        $this->requireAdmin();
        
        try {
            // Check if product exists
            $product = $this->productModel->findById($id);
            
            if (!$product) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Product not found'
                ], 404);
                return;
            }
            
            $data = $this->getRequestData();
            $productData = [];
            
            // Update name if provided
            if (isset($data['name'])) {
                $productData['name'] = trim($data['name']);
            }
            
            // Update description if provided
            if (isset($data['description'])) {
                $productData['description'] = $data['description'];
            }
            
            // Update price if provided
            if (isset($data['price'])) {
                $productData['price'] = (float)$data['price'];
            }
            
            // Update image if provided
            if (isset($data['image'])) {
                $productData['image'] = $data['image'];
            }
            
            // Only update if there are changes
            if (empty($productData)) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'No changes to update',
                    'product' => $product
                ]);
                return;
            }
            
            $success = $this->productModel->update($id, $productData);
            
            if ($success) {
                $updatedProduct = $this->productModel->findById($id);
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Product updated successfully',
                    'product' => $updatedProduct
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Failed to update product'
                ], 500);
            }
        } catch (\Exception $e) {
            error_log("Error updating product: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to update product: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function featured() {
        $products = $this->productModel->getFeatured();
        $this->jsonResponse(['products' => $products]);
    }

    // Admin methods for product management
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