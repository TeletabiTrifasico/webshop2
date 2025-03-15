<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0"><?= isset($product) ? 'Edit Product' : 'Add New Product' ?></h2>
                </div>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <div class="card-body">
                    <form action="<?= isset($product) ? "/admin/products/edit/{$product['id']}" : "/admin/products/create" ?>" 
                          method="POST" 
                          enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= isset($product) ? htmlspecialchars($product['name']) : '' ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required><?= isset($product) ? htmlspecialchars($product['description']) : '' ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Price</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" 
                                   value="<?= isset($product) ? $product['price'] : '' ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Product Image</label>
                            <?php if (isset($product) && $product['image']): ?>
                                <div class="mb-2">
                                    <img src="<?= htmlspecialchars($product['image']) ?>" 
                                         alt="Current product image" 
                                         style="max-width: 200px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="image" name="image" 
                                   <?= isset($product) ? '' : 'required' ?>>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="/admin/products" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <?= isset($product) ? 'Update Product' : 'Add Product' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>