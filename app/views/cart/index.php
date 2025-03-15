<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
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
                        <th>Unit Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
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
                            <td>$<?= number_format($item['product']['price'], 2) ?></td>
                            <td>
                                <div class="quantity-control">
                                    <button class="btn btn-sm btn-outline-secondary btn-minus" 
                                            onclick="updateQuantity(<?= $item['product']['id'] ?>, <?= $item['quantity'] - 1 ?>)"
                                            <?= $item['quantity'] <= 1 ? 'disabled' : '' ?>>
                                        -
                                    </button>
                                    <span class="mx-2"><?= $item['quantity'] ?></span>
                                    <button class="btn btn-sm btn-outline-secondary btn-plus" 
                                            onclick="updateQuantity(<?= $item['product']['id'] ?>, <?= $item['quantity'] + 1 ?>)">
                                        +
                                    </button>
                                </div>
                            </td>
                            <td>$<?= number_format($subtotal, 2) ?></td>
                            <td>
                                <button class="btn btn-danger btn-sm" 
                                        onclick="removeFromCart(<?= $item['product']['id'] ?>)">Remove</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                        <td colspan="2"><strong id="cart-total">$<?= number_format($total, 2) ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="d-flex justify-content-between mt-4">
            <a href="/products" class="btn btn-secondary">Continue Shopping</a>
            <button onclick="processCheckout()" class="btn btn-success">Proceed to Checkout</button>
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