<?php

namespace App\Controllers\Api;

class AdminProductController extends BaseApiController {
    private $productModel;

    public function __construct() {
        parent::__construct();
        $this->productModel = $this->model('Product');
    }

    // Check if the user is admin
    private function checkAdmin() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Unauthorized access'
            ], 403);
            exit;
        }
    }

    // Create product
    public function create() {
        $this->checkAdmin();

        $data = $this->getRequestData();
        
        if (empty($data['name']) || empty($data['price'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Name and price are required'
            ], 400);
            return;
        }

        // Handle image upload
        $imageUrl = $this->handleImageUpload();
        
        if ($imageUrl) {
            $data['image'] = $imageUrl;
        } else {
            $data['image'] = '/images/placeholder.jpg';  // Default image
        }

        $productId = $this->productModel->create($data);
        
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
    }

    // Update product
    public function update($id) {
        $this->checkAdmin();

        $data = $this->getRequestData();

        // Debug output to see what's being received
        error_log('Update product data: ' . print_r($data, true));
        error_log('Files: ' . print_r($_FILES, true));
        
        if (empty($data['name']) || empty($data['price'])) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Name and price are required'
            ], 400);
            return;
        }

        // Handle image upload (if new image provided)
        $imageUrl = $this->handleImageUpload();
        
        if ($imageUrl) {
            $data['image'] = $imageUrl;
        } else {
            // Keep existing image if no new one uploaded
            $existingProduct = $this->productModel->findById($id);
            if ($existingProduct) {
                $data['image'] = $existingProduct['image'];
            }
        }

        $success = $this->productModel->update($id, $data);
        
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
    }

    // Delete product
    public function delete($id) {
        $this->checkAdmin();

        $product = $this->productModel->findById($id);
        
        if (!$product) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Product not found'
            ], 404);
            return;
        }

        // Delete the product image if it's not the default image
        if ($product['image'] && $product['image'] !== '/images/placeholder.jpg') {
            $imagePath = $_SERVER['DOCUMENT_ROOT'] . $product['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $success = $this->productModel->delete($id);
        
        if ($success) {
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
    }

    // Helper method to handle image uploads
    private function handleImageUpload() {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $file = $_FILES['image'];
        
        // Validate file
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return null;
        }
        
        // Create upload directory if it doesn't exist
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/products/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Generate unique filename
        $filename = uniqid() . '_' . basename($file['name']);
        $targetPath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return '/uploads/products/' . $filename;
        }
        
        return null;
    }
}