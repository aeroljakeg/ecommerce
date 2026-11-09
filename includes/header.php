<?php
require_once __DIR__ . '/auth.php';
$siteName = getSetting('site_name', 'TechMoto PH');
$cartCount = getCartCount();
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? $siteName) ?> | <?= e($siteName) ?></title>
    <meta name="description" content="<?= e($pageDescription ?? 'Shop computer parts and motorcycle accessories at the best prices in the Philippines.') ?>">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    
    <?php if (!empty($extraCss)): ?>
        <?= $extraCss ?>
    <?php endif; ?>
</head>
<body>
<?php
$flash = getFlash();
if ($flash):
?>
<div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index:9999; min-width:300px;" role="alert">
    <?= e($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Top Bar -->
<div class="top-bar bg-dark text-white py-1 d-none d-md-block">
    <div class="container d-flex justify-content-between align-items-center small">
        <div>
            <i class="fas fa-phone-alt me-1"></i> <?= e(getSetting('site_phone', '+63 917 123 4567')) ?>
            <span class="mx-2">|</span>
            <i class="fas fa-envelope me-1"></i> <?= e(getSetting('site_email', 'support@techmoto.ph')) ?>
        </div>
        <div>
            <a href="<?= SITE_URL ?>/seller/register.php" class="text-white text-decoration-none me-3"><i class="fas fa-store me-1"></i> Become a Seller</a>
            <a href="<?= SITE_URL ?>/user/affiliate.php" class="text-white text-decoration-none"><i class="fas fa-handshake me-1"></i> Affiliate Program</a>
        </div>
    </div>
</div>

<!-- Main Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="<?= SITE_URL ?>/">
            <i class="fas fa-microchip me-1"></i> <?= e($siteName) ?>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?= SITE_URL ?>/">Home</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Computer Parts</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/products.php?category=computer-parts">All Computer Parts</a></li>
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/products.php?category=processors">Processors</a></li>
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/products.php?category=graphics-cards">Graphics Cards</a></li>
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/products.php?category=memory-storage">Memory & Storage</a></li>
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/products.php?category=pc-accessories">PC Accessories</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Motorcycle Parts</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/products.php?category=motorcycle-parts">All Motorcycle Parts</a></li>
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/products.php?category=helmets-safety">Helmets & Safety</a></li>
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/products.php?category=lighting">Lighting</a></li>
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/products.php?category=engine-performance">Engine & Performance</a></li>
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/products.php?category=motorcycle-accessories">Accessories</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= SITE_URL ?>/products.php">All Products</a>
                </li>
            </ul>
            
            <!-- Search -->
            <form class="d-flex me-3 position-relative" action="<?= SITE_URL ?>/products.php" method="GET" style="min-width:220px;">
                <input class="form-control form-control-sm rounded-pill pe-5" type="search" name="q" id="searchInput" placeholder="Search products..." value="<?= e($_GET['q'] ?? '') ?>" autocomplete="off">
                <button class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted pe-3" type="submit"><i class="fas fa-search"></i></button>
                <div id="searchSuggestions" class="search-suggestions d-none"></div>
            </form>
            
            <ul class="navbar-nav align-items-center">
                <li class="nav-item me-2">
                    <a class="nav-link position-relative" href="<?= SITE_URL ?>/user/wishlist.php" title="Wishlist">
                        <i class="fas fa-heart fa-lg"></i>
                    </a>
                </li>
                <li class="nav-item me-3">
                    <a class="nav-link position-relative" href="<?= SITE_URL ?>/user/cart.php" title="Cart">
                        <i class="fas fa-shopping-cart fa-lg"></i>
                        <?php if ($cartCount > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.65rem;"><?= $cartCount ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php if ($user): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle fa-lg me-1"></i>
                        <span class="d-none d-lg-inline"><?= e(explode(' ', $user['full_name'])[0]) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/user/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/user/orders.php"><i class="fas fa-box me-2"></i>My Orders</a></li>
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/user/profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/user/affiliate.php"><i class="fas fa-handshake me-2"></i>Affiliate</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= SITE_URL ?>/user/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="btn btn-outline-primary btn-sm me-1" href="<?= SITE_URL ?>/user/login.php">Login</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-primary btn-sm" href="<?= SITE_URL ?>/user/register.php">Register</a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>