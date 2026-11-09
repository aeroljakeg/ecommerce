<?php
$adminPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Admin') ?> | TechMoto PH Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <style>
        body { background: #f0f2f5; }
        .admin-sidebar { width: 250px; min-height: 100vh; background: #1a1a2e; color: white; position: fixed; left: 0; top: 0; z-index: 100; }
        .admin-sidebar .nav-link { color: rgba(255,255,255,0.7); padding: 0.65rem 1.25rem; border-radius: 0; font-size: 0.9rem; }
        .admin-sidebar .nav-link:hover, .admin-sidebar .nav-link.active { color: white; background: rgba(255,255,255,0.1); }
        .admin-sidebar .nav-link i { width: 22px; }
        .admin-content { margin-left: 250px; padding: 1.5rem; }
        @media (max-width: 768px) {
            .admin-sidebar { width: 100%; min-height: auto; position: relative; }
            .admin-content { margin-left: 0; }
        }
    </style>
</head>
<body>
<div class="d-flex flex-column flex-md-row">
    <aside class="admin-sidebar">
        <div class="p-3 border-bottom border-secondary">
            <a href="<?= SITE_URL ?>/admin/dashboard.php" class="text-white text-decoration-none fw-bold">
                <i class="fas fa-shield-alt me-1"></i> TechMoto Admin
            </a>
        </div>
        <nav class="nav flex-column py-2">
            <a class="nav-link <?= $adminPage === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a class="nav-link <?= $adminPage === 'products.php' ? 'active' : '' ?>" href="products.php"><i class="fas fa-box"></i> Products</a>
            <a class="nav-link <?= $adminPage === 'orders.php' ? 'active' : '' ?>" href="orders.php"><i class="fas fa-shopping-bag"></i> Orders</a>
            <a class="nav-link <?= $adminPage === 'users.php' ? 'active' : '' ?>" href="users.php"><i class="fas fa-users"></i> Customers</a>
            <a class="nav-link <?= $adminPage === 'sellers.php' ? 'active' : '' ?>" href="sellers.php"><i class="fas fa-store"></i> Sellers</a>
            <a class="nav-link <?= $adminPage === 'affiliates.php' ? 'active' : '' ?>" href="affiliates.php"><i class="fas fa-handshake"></i> Affiliates</a>
            <a class="nav-link <?= $adminPage === 'categories.php' ? 'active' : '' ?>" href="categories.php"><i class="fas fa-tags"></i> Categories</a>
            <a class="nav-link <?= $adminPage === 'payments.php' ? 'active' : '' ?>" href="payments.php"><i class="fas fa-credit-card"></i> Payments</a>
            <a class="nav-link <?= $adminPage === 'reports.php' ? 'active' : '' ?>" href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
            <a class="nav-link <?= $adminPage === 'settings.php' ? 'active' : '' ?>" href="settings.php"><i class="fas fa-cog"></i> Settings</a>
            <hr class="border-secondary mx-3">
            <a class="nav-link" href="<?= SITE_URL ?>/" target="_blank"><i class="fas fa-external-link-alt"></i> View Store</a>
            <a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>
    <main class="admin-content flex-fill">
