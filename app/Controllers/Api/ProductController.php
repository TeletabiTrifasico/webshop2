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
            // Get product data from POST request
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
            
            // Handle image upload
            $imageUrl = $this->handleImageUpload();
            if ($imageUrl) {
                $productData['image'] = $imageUrl;
            } else if (empty($productData['image'])) {
                $productData['image'] = '/images/products/placeholder.jpg'; // Default image
            }
            
            // Debug image path
            error_log("Image path for new product: " . ($productData['image'] ?? 'none'));
            
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
            
            // Log the incoming data for debugging
            error_log("Update product request data: " . json_encode($data));
            error_log("Files: " . json_encode($_FILES));
            
            // Initialize productData with existing product values
            $productData = [
                'name' => $product['name'],
                'description' => $product['description'],
                'price' => $product['price'],
                'image' => $product['image'] // Keep existing image by default
            ];
            
            // Update fields if provided
            if (isset($data['name'])) {
                $productData['name'] = trim($data['name']);
            }
            
            if (isset($data['description'])) {
                $productData['description'] = $data['description'];
            }
            
            if (isset($data['price'])) {
                $productData['price'] = (float)$data['price'];
            }
            
            // Only process image if a new one was uploaded
            $imageUrl = $this->handleImageUpload();
            if ($imageUrl) {
                $productData['image'] = $imageUrl;
                error_log("New image uploaded for product $id: $imageUrl");
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
        try {
            $this->requireAdmin();
            
            // Check if product exists
            $product = $this->productModel->findById($id);
            if (!$product) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Product not found'
                ], 404);
                return;
            }
            
            // Delete product from database
            $result = $this->productModel->delete($id);
            
            if ($result) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Product deleted successfully'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Failed to delete product'
                ], 500);
            }
        } catch (\Exception $e) {
            error_log("Error deleting product: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to delete product: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // Helper method to handle image uploads
    private function handleImageUpload() {
        // Check if an image was uploaded
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            error_log("No image uploaded or upload error: " . ($_FILES['image']['error'] ?? 'No file'));
            return null;
        }
        
        $file = $_FILES['image'];
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            error_log("Invalid image type: {$file['type']}");
            return null;
        }
        
        // Create products directory if it doesn't exist
        $uploadDir = __DIR__ . '/../../../public/images/products/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                error_log("Failed to create directory: $uploadDir");
                return null;
            }
        }
        
        // Generate unique filename to prevent overwrites
        $filename = uniqid() . '_' . basename($file['name']);
        $uploadPath = $uploadDir . $filename;
        
        error_log("Attempting to move uploaded file to: $uploadPath");
        
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Return the relative path for database storage
            return '/images/products/' . $filename;
        } else {
            $uploadError = error_get_last();
            error_log("Failed to move uploaded file: " . ($uploadError['message'] ?? 'Unknown error'));
            return null;
        }
    }
}