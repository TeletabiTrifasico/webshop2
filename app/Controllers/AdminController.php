<?php

namespace App\Controllers;

class AdminController extends Controller {
    private $productModel;
    private $userModel;
    private $orderModel;

    public function __construct() {
        parent::__construct();
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: /');
            exit;
        }
        $this->productModel = $this->model('Product');
        $this->userModel = $this->model('User');
        $this->orderModel = $this->model('Order');
    }

    public function dashboard() {
        $products = $this->productModel->getAll();
        $users = $this->userModel->getAllExceptAdmin();
        $orders = $this->orderModel->getAll();

        $this->view('admin/dashboard', [
            'productCount' => count($products),
            'userCount' => count($users),
            'orderCount' => count($orders),
            'recentOrders' => array_slice($orders, 0, 5),
            'recentUsers' => array_slice($users, 0, 5)
        ]);
    }

    public function products() {
        $this->view('admin/products', [
            'products' => $this->productModel->getAll()
        ]);
    }

    public function createProduct() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $uploadResult = $this->handleImageUpload();
            
            if (isset($uploadResult['error'])) {
                $this->view('admin/product-form', [
                    'error' => $uploadResult['error'],
                    'product' => $_POST
                ]);
                return;
            }

            try {
                $this->productModel->create([
                    'name' => $_POST['name'],
                    'description' => $_POST['description'],
                    'price' => $_POST['price'],
                    'image' => $uploadResult['imageUrl']
                ]);
                header('Location: /admin/products');
                exit;
            } catch (\Exception $e) {
                $this->view('admin/product-form', [
                    'error' => 'Failed to create product',
                    'product' => $_POST
                ]);
            }
        }

        $this->view('admin/product-form');
    }

    public function editProduct($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productData = [
                'name' => $_POST['name'],
                'description' => $_POST['description'],
                'price' => $_POST['price']
            ];

            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->handleImageUpload();
                if (isset($uploadResult['error'])) {
                    $this->view('admin/product-form', [
                        'error' => $uploadResult['error'],
                        'product' => array_merge($_POST, ['id' => $id])
                    ]);
                    return;
                }
                $productData['image'] = $uploadResult['imageUrl'];
            }

            try {
                $this->productModel->update($id, $productData);
                header('Location: /admin/products');
                exit;
            } catch (\Exception $e) {
                $this->view('admin/product-form', [
                    'error' => 'Failed to update product',
                    'product' => array_merge($_POST, ['id' => $id])
                ]);
                return;
            }
        }

        $product = $this->productModel->getById($id);
        if (!$product) {
            header('Location: /admin/products');
            exit;
        }

        $this->view('admin/product-form', ['product' => $product]);
    }

    public function deleteProduct($id) {
        $this->productModel->delete($id);
        $this->jsonResponse(['success' => true]);
    }

    public function users() {
        $this->view('admin/users', [
            'users' => $this->userModel->getAllExceptAdmin()
        ]);
    }

    public function createUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userData = [
                'username' => $_POST['username'],
                'email' => $_POST['email'],
                'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                'role' => $_POST['role']
            ];

            try {
                $this->userModel->create($userData);
                header('Location: /admin/users');
                exit;
            } catch (\Exception $e) {
                $this->view('admin/user-form', ['error' => 'Email already exists']);
            }
        }

        $this->view('admin/user-form');
    }

    public function editUser($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userData = [
                'username' => $_POST['username'],
                'email' => $_POST['email'],
                'role' => $_POST['role']
            ];

            try {
                $this->userModel->update($id, $userData);
                header('Location: /admin/users');
                exit;
            } catch (\Exception $e) {
                $user = $this->userModel->getById($id);
                $this->view('admin/user-form', [
                    'error' => 'Email already exists',
                    'user' => array_merge($user, $_POST)
                ]);
                return;
            }
        }

        $user = $this->userModel->getById($id);
        if (!$user) {
            header('Location: /admin/users');
            exit;
        }

        $this->view('admin/user-form', ['user' => $user]);
    }

    public function updateUserRole($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $role = $_POST['role'];
            $this->userModel->updateRole($id, $role);

            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }
    }

    public function deleteUser($id) {
        $this->userModel->delete($id);

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }

        header('Location: /admin/users');
        exit;
    }

    public function orders() {
        $this->view('admin/orders', [
            'orders' => $this->orderModel->getAllWithUserDetails()
        ]);
    }

    public function editOrder($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->orderModel->updateStatus($id, $_POST['status']);
            header('Location: /admin/orders');
            exit;
        }

        $this->view('admin/order-form', [
            'order' => $this->orderModel->getByIdWithUserDetails($id),
            'orderItems' => $this->orderModel->getOrderItems($id)
        ]);
    }

    public function viewOrder($id) {
        $order = $this->orderModel->getByIdWithUserDetails($id);

        if (!$order) {
            header('Location: /admin/orders');
            exit;
        }

        $orderItems = $this->orderModel->getOrderItems($id);

        $this->view('admin/order-details', [
            'order' => $order,
            'orderItems' => $orderItems
        ]);
    }

    private function handleImageUpload() {
        $uploadDir = __DIR__ . '/../../public/images/products';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fileInfo = pathinfo($_FILES['image']['name']);
            $extension = strtolower($fileInfo['extension']);
            
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                return ['error' => 'Invalid file type. Only JPG, PNG and GIF are allowed.'];
            }

            $filename = uniqid() . '.' . $extension;
            $uploadPath = $uploadDir . '/' . $filename;
            $imageUrl = '/images/products/' . $filename;

            return move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath) 
                ? ['imageUrl' => $imageUrl]
                : ['error' => 'Failed to upload image'];
        }

        return ['error' => 'No image uploaded'];
    }

    private function jsonResponse($data) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Content-Type: application/json');
            echo json_encode($data);
            exit;
        }
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
}