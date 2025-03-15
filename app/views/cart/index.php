<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <h1 class="mb-4">Shopping Cart</h1>

    <?php if (empty($cart)): ?>
        <div class="alert alert-info">
            Your cart is empty. <a href="/products">Continue shopping</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="text-end">Price</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-end">Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total = 0;
                    foreach ($cart as $item): 
                        $subtotal = $item['product']['price'] * $item['quantity'];
                        $total += $subtotal;
                    ?>
                        <tr data-product-id="<?= $item['product']['id'] ?>">
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="<?= htmlspecialchars($item['product']['image']) ?>" 
                                         alt="<?= htmlspecialchars($item['product']['name']) ?>" 
                                         class="cart-item-image me-3"
                                         style="width: 50px; height: 50px; object-fit: cover;">
                                    <span><?= htmlspecialchars($item['product']['name']) ?></span>
                                </div>
                            </td>
                            <td class="text-end">$<?= number_format($item['product']['price'], 2) ?></td>
                            <td class="text-center">
                                <div class="quantity-control">
                                    <button class="btn btn-sm btn-outline-secondary" 
                                            onclick="updateQuantity(<?= $item['product']['id'] ?>, <?= $item['quantity'] - 1 ?>)"
                                            <?= $item['quantity'] <= 1 ? 'disabled' : '' ?>>-</button>
                                    <span class="mx-2"><?= $item['quantity'] ?></span>
                                    <button class="btn btn-sm btn-outline-secondary" 
                                            onclick="updateQuantity(<?= $item['product']['id'] ?>, <?= $item['quantity'] + 1 ?>)">+</button>
                                </div>
                            </td>
                            <td class="text-end">$<?= number_format($subtotal, 2) ?></td>
                            <td>
                                <button onclick="removeFromCart(<?= $item['product']['id'] ?>)" 
                                        class="btn btn-danger btn-sm">Remove</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                        <td class="text-end"><strong>$<?= number_format($total, 2) ?></strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>

            <div class="d-flex justify-content-between mt-4">
                <a href="/products" class="btn btn-secondary">Continue Shopping</a>
                <button onclick="processCheckout()" class="btn btn-success">Proceed to Checkout</button>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function processCheckout() {
    if (confirm('Are you sure you want to complete your order?')) {
        fetch('/cart/checkout', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                setTimeout(() => {
                    window.location.href = '/';
                }, 2000);
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred while processing your order.', 'error');
        });
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>