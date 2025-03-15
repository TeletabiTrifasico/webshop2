<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0">My Profile</h2>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $error ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $success ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form action="/user/profile/update" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label"><strong>Username</strong></label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?= htmlspecialchars($user['username']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label"><strong>Email</strong></label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>

                        <h4 class="mt-4 mb-3">Change Password</h4>
                        <div class="mb-3">
                            <label for="current_password" class="form-label"><strong>Current Password</strong></label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                            <small class="text-muted">Leave blank if you don't want to change your password</small>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label"><strong>New Password</strong></label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>