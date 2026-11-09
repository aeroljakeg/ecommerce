<?php
require_once __DIR__ . '/../includes/auth.php';

if (currentUser()) {
    redirect(SITE_URL . '/user/dashboard.php');
}

$error = '';
$success = '';
if (isPost()) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request.';
    } else {
        $result = registerUser([
            'full_name' => $_POST['full_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? ''
        ]);
        if ($result['success']) {
            // Optionally save address
            if (!empty($_POST['address'])) {
                $db = getDB();
                $db->prepare("INSERT INTO addresses (user_id, recipient_name, phone, address_line, city, province, is_default) VALUES (?, ?, ?, ?, ?, ?, 1)")
                   ->execute([
                       $result['user_id'],
                       $_POST['full_name'],
                       $_POST['phone'] ?? '',
                       $_POST['address'],
                       $_POST['city'] ?? '',
                       $_POST['province'] ?? ''
                   ]);
            }
            setFlash('success', 'Account created successfully!');
            redirect(SITE_URL . '/user/dashboard.php');
        }
        $error = $result['message'];
    }
}

$pageTitle = 'Register';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="form-card">
                <div class="text-center mb-4">
                    <h3 class="fw-bold">Create Account</h3>
                    <p class="text-muted small">Join TechMoto PH today</p>
                </div>
                
                <?php if ($error): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" class="form-control" required value="<?= e($_POST['full_name'] ?? '') ?>">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" required value="<?= e($_POST['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="09XXXXXXXXX" value="<?= e($_POST['phone'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Complete Address</label>
                        <textarea name="address" class="form-control" rows="2"><?= e($_POST['address'] ?? '') ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" value="<?= e($_POST['city'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Province</label>
                            <input type="text" name="province" class="form-control" value="<?= e($_POST['province'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password *</label>
                            <input type="password" name="password" class="form-control" required minlength="8">
                            <div class="form-text">Minimum 8 characters</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm Password *</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mb-3">Create Account</button>
                </form>
                
                <div class="text-center small">
                    Already have an account? <a href="<?= SITE_URL ?>/user/login.php">Login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>