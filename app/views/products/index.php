<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <h1 class="mb-4">Our Products</h1>

    <div class="row">
        <?php if (isset($products) && !empty($products)): ?>
            <?php foreach ($products as $product): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="<?= htmlspecialchars($product['image']) ?>" 
                             class="card-img-top" 
                             alt="<?= htmlspecialchars($product['name']) ?>">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                            <p class="card-text flex-grow-1"><?= htmlspecialchars($product['description']) ?></p>
                            <div class="mt-auto">
                                <p class="card-text"><strong>$<?= number_format($product['price'], 2) ?></strong></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="/products/<?= $product['id'] ?>" class="btn btn-primary">View Details</a>
                                    <button class="btn btn-success" 
                                            onclick="handleAddToCart(<?= $product['id'] ?>, 1)"
                                            type="button">
                                        Add to Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <p class="text-center">No products available.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>