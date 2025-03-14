<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="jumbotron text-center">
    <h1>Welcome to <?= APP_NAME ?></h1>
    <p class="lead">Discover our amazing collection of products</p>
    <a href="/products" class="btn btn-primary btn-lg">Browse Products</a>
</div>

<div class="container mt-5">
    <h3 class="mb-4">Latest Products</h3>
    <div class="row">
        <?php foreach (array_slice($products, 0, 3) as $product): ?>
            <div class="col-md-4">
                <div class="card mb-4">
                    <img src="<?= htmlspecialchars($product['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                        <p class="card-text"><?= htmlspecialchars($product['description']) ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h5 mb-0">$<?= number_format($product['price'], 2) ?></span>
                            <a href="/products/<?= $product['id'] ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>