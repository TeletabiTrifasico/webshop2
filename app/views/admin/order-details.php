<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="mb-0">Order #<?= $order['id'] ?></h2>
            <a href="/admin/orders" class="btn btn-secondary">Back to Orders</a>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h4><strong>Customer Details</strong></h4>
                    <p><strong>Name:</strong> <?= htmlspecialchars($order['username']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
                    <p><strong>Order Date:</strong> <?= date('F j, Y g:i A', strtotime($order['created_at'])) ?></p>
                </div>
                <div class="col-md-6">
                    <h4><strong>Order Information</strong></h4>
                    <p>
                        <strong>Status:</strong> 
                        <span class="badge bg-<?= getStatusColor($order['status']) ?>">
                            <?= ucfirst(htmlspecialchars($order['status'])) ?>
                        </span>
                    </p>
                </div>
            </div>

            <h4><strong>Order Items</strong></h4>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orderItems as $item): 
                            $itemTotal = $item['price'] * $item['quantity'];
                        ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?= htmlspecialchars($item['image']) ?>" 
                                             alt="<?= htmlspecialchars($item['name']) ?>"
                                             class="me-2"
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                        <div>
                                            <div><?= htmlspecialchars($item['name']) ?></div>
                                            <small class="text-muted">Product ID: <?= $item['product_id'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end">$<?= number_format($item['price'], 2) ?></td>
                                <td class="text-center"><?= $item['quantity'] ?></td>
                                <td class="text-end">$<?= number_format($itemTotal, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                            <td class="text-end"><strong>$<?= number_format($order['total_amount'], 2) ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
function getStatusColor($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'processing':
            return 'info';
        case 'completed':
            return 'success';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}
?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>