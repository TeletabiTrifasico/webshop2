<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">
</head>
<body data-user-logged-in="<?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>">
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;"></div>
    
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/"><?= APP_NAME ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="/">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="/products">Products</a></li>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link text-warning" href="/admin/dashboard">Admin Dashboard</a></li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a href="/user/profile" class="nav-link">
                                <?= htmlspecialchars($_SESSION['username']) ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/cart">
                                Cart
                                <span class="badge bg-danger rounded-pill cart-count position-absolute" 
                                      style="display: <?= isset($_SESSION['cart']) && !empty($_SESSION['cart']) ? 'inline-block' : 'none' ?>">
                                    <?php
                                    $cartCount = 0;
                                    if (isset($_SESSION['cart'])) {
                                        $cartCount = array_reduce($_SESSION['cart'], function($sum, $item) {
                                            return $sum + $item['quantity'];
                                        }, 0);
                                    }
                                    echo $cartCount;
                                    ?>
                                </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-danger btn-sm text-white ms-2" href="/auth/logout">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="/auth/login">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="/auth/register">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="main-content">
    <div class="container mt-4">