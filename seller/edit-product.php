<?php
require_once __DIR__ . '/../includes/auth.php';
requireSellerLogin();
$seller = currentSeller();
$pageTitle = 'Seller - ' . ucfirst(str_replace(['.php','-'],['',' '],basename(__FILE__)));
require_once __DIR__ . '/../includes/header.php';
echo '<div class="container py-4"><h4 class="fw-bold mb-3">' . htmlspecialchars($pageTitle) . '</h4>';
echo '<div class="bg-white rounded-lg shadow-sm-custom p-4"><p class="text-muted">Seller functionality page. Implement product management, order fulfillment, and inventory using the established database schema and helper functions.</p>';
echo '<a href="' . SITE_URL . '/seller/dashboard.php" class="btn btn-primary btn-sm">Back to Dashboard</a></div></div>';
require_once __DIR__ . '/../includes/footer.php';
