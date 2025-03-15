<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Orders</h1>
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>User</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= $order['id'] ?></td>
                        <td><?= htmlspecialchars($order['username']) ?></td>
                        <td>$<?= number_format($order['total_amount'], 2) ?></td>
                        <td><span class="badge bg-<?= getStatusColor($order['status']) ?>">
                            <?= ucfirst(htmlspecialchars($order['status'])) ?>
                        </span></td>
                        <td><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></td>
                        <td>
                            <a href="/admin/orders/edit/<?= $order['id'] ?>" 
                               class="btn btn-sm btn-primary me-2">Edit</a>
                            <a href="/admin/orders/view/<?= $order['id'] ?>" 
                               class="btn btn-sm btn-info">View Details</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
function getStatusColor($status) {
    switch ($status) {
        case 'pending': return 'warning';
        case 'processing': return 'info';
        case 'completed': return 'success';
        case 'cancelled': return 'danger';
        default: return 'secondary';
    }
}
?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>