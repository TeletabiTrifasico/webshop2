<?php

namespace App\Controllers;

class AdminController extends Controller {
    public function __construct() {
        parent::__construct();
        // Check if user is admin
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: /');
            exit;
        }
    }

    public function dashboard() {
        // Get counts
        $stmt = $this->pdo->query("SELECT COUNT(*) as products FROM products");
        $productCount = $stmt->fetch()['products'];

        $stmt = $this->pdo->query("SELECT COUNT(*) as users FROM users WHERE email != 'admin@webshop.com'");
        $userCount = $stmt->fetch()['users'];

        $stmt = $this->pdo->query("SELECT COUNT(*) as orders FROM orders");
        $orderCount = $stmt->fetch()['orders'];

        // Get 5 most recent orders
        $stmt = $this->pdo->query("
            SELECT o.id, o.total_amount as total, o.created_at, u.username 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC 
            LIMIT 5
        ");
        $recentOrders = $stmt->fetchAll();

        // Get 5 most recent users
        $stmt = $this->pdo->query("
            SELECT username, created_at 
            FROM users 
            WHERE email != 'admin@webshop.com' 
            ORDER BY created_at DESC 
            LIMIT 5
        ");
        $recentUsers = $stmt->fetchAll();

        $this->view('admin/dashboard', [
            'productCount' => $productCount,
            'userCount' => $userCount,
            'orderCount' => $orderCount,
            'recentOrders' => $recentOrders,
            'recentUsers' => $recentUsers
        ]);
    }

    // Product Management
    public function products() {
        $stmt = $this->pdo->query("SELECT * FROM products ORDER BY created_at DESC");
        $products = $stmt->fetchAll();
        $this->view('admin/products', ['products' => $products]);
    }

    public function createProduct() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            
            // Create upload directory if it doesn't exist
            $uploadDir = __DIR__ . '/../../public/images/products';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $fileInfo = pathinfo($_FILES['image']['name']);
                $extension = strtolower($fileInfo['extension']);
                
                // Validate file extension
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array($extension, $allowedExtensions)) {
                    $this->view('admin/product-form', [
                        'error' => 'Invalid file type. Only JPG, PNG and GIF are allowed.',
                        'product' => [
                            'name' => $name,
                            'description' => $description,
                            'price' => $price
                        ]
                    ]);
                    return;
                }

                // Generate unique filename
                $filename = uniqid() . '.' . $extension;
                $uploadPath = $uploadDir . '/' . $filename;
                $imageUrl = '/images/products/' . $filename;

                // Move uploaded file
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                    try {
                        $stmt = $this->pdo->prepare(
                            "INSERT INTO products (name, description, price, image) VALUES (?, ?, ?, ?)"
                        );
                        $stmt->execute([$name, $description, $price, $imageUrl]);

                        header('Location: /admin/products');
                        exit;
                    } catch (\PDOException $e) {
                        // Remove uploaded file if database insert fails
                        unlink($uploadPath);
                        throw $e;
                    }
                } else {
                    $this->view('admin/product-form', [
                        'error' => 'Failed to upload image. Please try again.',
                        'product' => [
                            'name' => $name,
                            'description' => $description,
                            'price' => $price
                        ]
                    ]);
                    return;
                }
            } else {
                $this->view('admin/product-form', [
                    'error' => 'Please select an image file.',
                    'product' => [
                        'name' => $name,
                        'description' => $description,
                        'price' => $price
                    ]
                ]);
                return;
            }
        }
        
        $this->view('admin/product-form');
    }

    public function editProduct($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            
            $uploadDir = __DIR__ . '/../../public/images/products';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // If a new image was uploaded
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $fileInfo = pathinfo($_FILES['image']['name']);
                $extension = strtolower($fileInfo['extension']);
                
                // Validate file extension
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array($extension, $allowedExtensions)) {
                    $this->view('admin/product-form', [
                        'error' => 'Invalid file type. Only JPG, PNG and GIF are allowed.',
                        'product' => [
                            'id' => $id,
                            'name' => $name,
                            'description' => $description,
                            'price' => $price
                        ]
                    ]);
                    return;
                }

                $filename = uniqid() . '.' . $extension;
                $uploadPath = $uploadDir . '/' . $filename;
                $imageUrl = '/images/products/' . $filename;

                // Get old image to delete it later
                $stmt = $this->pdo->prepare("SELECT image FROM products WHERE id = ?");
                $stmt->execute([$id]);
                $oldImage = $stmt->fetch()['image'];

                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                    try {
                        $stmt = $this->pdo->prepare(
                            "UPDATE products SET name = ?, description = ?, price = ?, image = ? WHERE id = ?"
                        );
                        $stmt->execute([$name, $description, $price, $imageUrl, $id]);

                        // Delete old image if it exists
                        if ($oldImage && file_exists(__DIR__ . '/../../public' . $oldImage)) {
                            unlink(__DIR__ . '/../../public' . $oldImage);
                        }

                        header('Location: /admin/products');
                        exit;
                    } catch (\PDOException $e) {
                        unlink($uploadPath);
                        throw $e;
                    }
                }
            } else {
                // Update without changing the image
                $stmt = $this->pdo->prepare(
                    "UPDATE products SET name = ?, description = ?, price = ? WHERE id = ?"
                );
                $stmt->execute([$name, $description, $price, $id]);
                header('Location: /admin/products');
                exit;
            }
        }

        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        
        $this->view('admin/product-form', ['product' => $product]);
    }

    public function deleteProduct($id) {
        $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }

        header('Location: /admin/products');
        exit;
    }

    // User Management
    public function users() {
        $stmt = $this->pdo->query("SELECT * FROM users WHERE email != 'admin@webshop.com' ORDER BY created_at DESC");
        $users = $stmt->fetchAll();
        $this->view('admin/users', ['users' => $users]);
    }

    public function createUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $email = $_POST['email'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $role = $_POST['role'];

            try {
                $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $email, $password, $role]);
                header('Location: /admin/users');
                exit;
            } catch (\PDOException $e) {
                $this->view('admin/user-form', ['error' => 'Email already exists']);
                return;
            }
        }
        $this->view('admin/user-form');
    }

    public function editUser($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $email = $_POST['email'];
            $role = $_POST['role'];

            try {
                $stmt = $this->pdo->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
                $stmt->execute([$username, $email, $role, $id]);
                header('Location: /admin/users');
                exit;
            } catch (\PDOException $e) {
                $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $user = $stmt->fetch();
                $this->view('admin/user-form', ['user' => $user, 'error' => 'Email already exists']);
                return;
            }
        }

        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if (!$user) {
            header('Location: /admin/users');
            exit;
        }

        $this->view('admin/user-form', ['user' => $user]);
    }

    public function updateUserRole($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $role = $_POST['role'];
            $stmt = $this->pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$role, $id]);

            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }
    }

    public function deleteUser($id) {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }

        header('Location: /admin/users');
        exit;
    }

    // Order Management
    public function orders() {
        $stmt = $this->pdo->query("
            SELECT o.*, u.username, u.email 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC
        ");
        $orders = $stmt->fetchAll();
        $this->view('admin/orders', ['orders' => $orders]);
    }

    public function editOrder($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $status = $_POST['status'];
            
            $stmt = $this->pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);

            header('Location: /admin/orders');
            exit;
        }

        $stmt = $this->pdo->prepare("
            SELECT o.*, u.username, u.email 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?
        ");
        $stmt->execute([$id]);
        $order = $stmt->fetch();

        $stmt = $this->pdo->prepare("
            SELECT oi.*, p.name 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$id]);
        $orderItems = $stmt->fetchAll();

        $this->view('admin/order-form', [
            'order' => $order,
            'orderItems' => $orderItems
        ]);
    }

    public function viewOrder($id) {
        // Get order details with user information
        $stmt = $this->pdo->prepare("
            SELECT o.*, u.username, u.email 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?
        ");
        $stmt->execute([$id]);
        $order = $stmt->fetch();

        if (!$order) {
            header('Location: /admin/orders');
            exit;
        }

        // Get order items with product details
        $stmt = $this->pdo->prepare("
            SELECT oi.*, p.name, p.image 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$id]);
        $orderItems = $stmt->fetchAll();

        $this->view('admin/order-details', [
            'order' => $order,
            'orderItems' => $orderItems
        ]);
    }
}