<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<div class="dashboard-sidebar">
    <div class="d-flex align-items-center gap-2 mb-3 pb-3 border-bottom">
        <div class="bg-primary-soft rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
            <i class="fas fa-user text-primary"></i>
        </div>
        <div>
            <div class="fw-semibold small"><?= e(currentUser()['full_name'] ?? '') ?></div>
            <div class="text-muted" style="font-size:0.75rem;"><?= e(currentUser()['email'] ?? '') ?></div>
        </div>
    </div>
    <nav class="nav flex-column">
        <a class="nav-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/user/dashboard.php">
            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
        </a>
        <a class="nav-link <?= $currentPage === 'orders.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/user/orders.php">
            <i class="fas fa-box me-2"></i>My Orders
        </a>
        <a class="nav-link <?= $currentPage === 'cart.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/user/cart.php">
            <i class="fas fa-shopping-cart me-2"></i>Cart
        </a>
        <a class="nav-link <?= $currentPage === 'wishlist.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/user/wishlist.php">
            <i class="fas fa-heart me-2"></i>Wishlist
        </a>
        <a class="nav-link <?= $currentPage === 'profile.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/user/profile.php">
            <i class="fas fa-user-edit me-2"></i>Profile
        </a>
        <a class="nav-link <?= $currentPage === 'affiliate.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/user/affiliate.php">
            <i class="fas fa-handshake me-2"></i>Affiliate
        </a>
        <hr>
        <a class="nav-link text-danger" href="<?= SITE_URL ?>/user/logout.php">
            <i class="fas fa-sign-out-alt me-2"></i>Logout
        </a>
    </nav>
</div>