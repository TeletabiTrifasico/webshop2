<?php
?>

<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-6">
            <img src="<?= htmlspecialchars($product['image']) ?>" 
                 alt="<?= htmlspecialchars($product['name']) ?>"
                 class="img-fluid product-detail-img">
        </div>
        <div class="col-md-6">
            <h1><?= htmlspecialchars($product['name']) ?></h1>
            <p class="lead">$<?= number_format($product['price'], 2) ?></p>
            <p><?= htmlspecialchars($product['description']) ?></p>
            <button class="btn btn-success btn-lg" 
                    onclick="handleAddToCart(<?= $product['id'] ?>, 1)"
                    type="button">
                Add to Cart
            </button>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>