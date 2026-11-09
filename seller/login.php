<?php
require_once __DIR__ . '/../includes/auth.php';

if (currentSeller()) {
    redirect(SITE_URL . '/seller/dashboard.php');
}

$error = '';
if (isPost()) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request.';
    } else {
        $result = loginSeller($_POST['email'] ?? '', $_POST['password'] ?? '');
        if ($result['success']) {
            redirect(SITE_URL . '/seller/dashboard.php');
        }
        $error = $result['message'];
    }
}
$pageTitle = 'Seller Login';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="form-card">
                <div class="text-center mb-4">
                    <i class="fas fa-store fa-2x text-primary mb-2"></i>
                    <h3 class="fw-bold">Seller Login</h3>
                    <p class="text-muted small">Access your seller dashboard</p>
                </div>
                <?php if ($error): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mb-3">Login</button>
                </form>
                <div class="text-center small">
                    Want to sell? <a href="<?= SITE_URL ?>/seller/register.php">Register as Seller</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>