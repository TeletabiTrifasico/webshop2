<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <h1 class="mb-4">Admin Dashboard</h1>
    
    <div class="row">
        <!-- Products Management Card -->
        <div class="col-md-4 mb-4">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <h5 class="card-title">Products</h5>
                    <p class="card-text">Total Products: <?= $productCount ?></p>
                    <div class="mt-3">
                        <a href="/admin/products" class="btn btn-light me-2">View All</a>
                        <a href="/admin/products/create" class="btn btn-outline-light">Add New</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Management Card -->
        <div class="col-md-4 mb-4">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <h5 class="card-title">Users</h5>
                    <p class="card-text">Total Users: <?= $userCount ?></p>
                    <div class="mt-3">
                        <a href="/admin/users" class="btn btn-light me-2">Manage Users</a>
                        <a href="/admin/users/create" class="btn btn-outline-light">Add User</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Management Card -->
        <div class="col-md-4 mb-4">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <h5 class="card-title">Orders</h5>
                    <p class="card-text">Total Orders: <?= $orderCount ?></p>
                    <div class="mt-3">
                        <a href="/admin/orders" class="btn btn-light">View All Orders</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="row mt-4">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Orders</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($recentOrders)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($recentOrders as $order): ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>Order #<?= $order['id'] ?></strong>
                                            <br>
                                            <small class="text-muted">by <?= htmlspecialchars($order['username']) ?></small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-primary rounded-pill">$<?= number_format($order['total'], 2) ?></span>
                                            <br>
                                            <small class="text-muted"><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></small>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted mb-0">No recent orders</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Latest Users</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($recentUsers)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($recentUsers as $user): ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span><?= htmlspecialchars($user['username']) ?></span>
                                        <small class="text-muted"><?= date('M j, Y g:i A', strtotime($user['created_at'])) ?></small>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted mb-0">No recent users</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>