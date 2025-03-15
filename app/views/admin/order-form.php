<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0">Edit Order #<?= $order['id'] ?></h2>
                </div>
                <div class="card-body">
                    <form action="/admin/orders/edit/<?= $order['id'] ?>" method="POST">
                        <div class="mb-3">
                            <label class="form-label"><strong>Customer</strong></label>
                            <p class="form-control-static"><?= htmlspecialchars($order['username']) ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><strong>Email</strong></label>
                            <p class="form-control-static"><?= htmlspecialchars($order['email']) ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><strong>Total Amount</strong></label>
                            <p class="form-control-static">$<?= number_format($order['total_amount'], 2) ?></p>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label"><strong>Status</strong></label>
                            <select class="form-select" id="status" name="status">
                                <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="/admin/orders" class="btn btn-secondary">Back to Orders</a>
                            <button type="submit" class="btn btn-primary">Update Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">Order Items</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                    <?php foreach ($orderItems as $item): ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0"><?= htmlspecialchars($item['name']) ?></h6>
                                    <small class="text-muted">Quantity: <?= $item['quantity'] ?></small>
                                </div>
                                <span>$<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>