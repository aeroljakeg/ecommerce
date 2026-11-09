<?php
require_once __DIR__ . '/../includes/auth.php';
$pageTitle = 'Become a Seller';
$error = '';
$success = false;

if (isPost() && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $db = getDB();
    $shopName = trim($_POST['shop_name'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['shop_address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($shopName) || empty($email) || empty($password) || empty($phone) || empty($address)) {
        $error = 'Please fill all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $check = $db->prepare("SELECT id FROM sellers WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = 'Email already registered as seller.';
        } else {
            $logoPath = null;
            $idPath = null;
            
            if (!empty($_FILES['shop_logo']['name'])) {
                $errs = validateImageUpload($_FILES['shop_logo']);
                if (empty($errs)) {
                    $fname = sanitizeFilename($_FILES['shop_logo']['name']);
                    if (move_uploaded_file($_FILES['shop_logo']['tmp_name'], UPLOAD_PATH . '/sellers/' . $fname)) {
                        $logoPath = 'uploads/sellers/' . $fname;
                    }
                }
            }
            if (!empty($_FILES['valid_id']['name'])) {
                $errs = validateImageUpload($_FILES['valid_id']);
                if (empty($errs)) {
                    $fname = sanitizeFilename($_FILES['valid_id']['name']);
                    if (move_uploaded_file($_FILES['valid_id']['tmp_name'], UPLOAD_PATH . '/ids/' . $fname)) {
                        $idPath = 'uploads/ids/' . $fname;
                    }
                }
            }
            
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $db->prepare("INSERT INTO sellers (shop_name, email, phone, password, shop_address, shop_logo, valid_id, status) VALUES (?,?,?,?,?,?,?,'pending')")
               ->execute([$shopName, $email, $phone, $hash, $address, $logoPath, $idPath]);
            
            $sellerId = $db->lastInsertId();
            $db->prepare("INSERT INTO seller_applications (seller_id, notes) VALUES (?, ?)")
               ->execute([$sellerId, 'New application from ' . $fullName]);
            
            $success = true;
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="form-card">
                <?php if ($success): ?>
                <div class="text-center py-4">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h4 class="fw-bold">Application Submitted!</h4>
                    <p class="text-muted">Your seller application is pending admin approval. You will be able to login once approved.</p>
                    <a href="<?= SITE_URL ?>/" class="btn btn-primary">Back to Store</a>
                </div>
                <?php else: ?>
                <div class="text-center mb-4">
                    <h3 class="fw-bold">Become a Seller</h3>
                    <p class="text-muted small">Sell your products on TechMoto PH</p>
                </div>
                <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Shop Name *</label>
                            <input type="text" name="shop_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone *</label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Shop Address *</label>
                            <textarea name="shop_address" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Shop Logo</label>
                            <input type="file" name="shop_logo" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Valid ID *</label>
                            <input type="file" name="valid_id" class="form-control" accept="image/*">
                            <div class="form-text">Government-issued ID for verification</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password *</label>
                            <input type="password" name="password" class="form-control" required minlength="8">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password *</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary w-100">Submit Application</button>
                        </div>
                    </div>
                </form>
                <div class="text-center small mt-3">
                    Already a seller? <a href="<?= SITE_URL ?>/seller/login.php">Login</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>