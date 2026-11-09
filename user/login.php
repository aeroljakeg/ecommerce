<?php
require_once __DIR__ . '/../includes/auth.php';

if (currentUser()) {
    redirect(SITE_URL . '/user/dashboard.php');
}

$error = '';
if (isPost()) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $result = loginUser($_POST['email'] ?? '', $_POST['password'] ?? '');
        if ($result['success']) {
            $redirect = $_GET['redirect'] ?? SITE_URL . '/user/dashboard.php';
            setFlash('success', 'Welcome back!');
            redirect($redirect);
        }
        $error = $result['message'];
    }
}

$pageTitle = 'Login';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="form-card">
                <div class="text-center mb-4">
                    <h3 class="fw-bold">Welcome Back</h3>
                    <p class="text-muted small">Login to your TechMoto account</p>
                </div>
                
                <?php if ($error): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" required value="<?= e($_POST['email'] ?? '') ?>" autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mb-3">Login</button>
                </form>
                
                <div class="text-center small">
                    Don't have an account? <a href="<?= SITE_URL ?>/user/register.php">Register</a>
                </div>
                <hr>
                <div class="text-center small text-muted">
                    Are you a seller? <a href="<?= SITE_URL ?>/seller/login.php">Seller Login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>