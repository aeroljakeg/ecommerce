<?php
require_once __DIR__ . '/../includes/auth.php';
requireSellerLogin();
$seller = currentSeller();
$db = getDB();
$pageTitle = 'Seller Dashboard';

$productCount = $db->prepare("SELECT COUNT(*) FROM products WHERE seller_id = ?");
$productCount->execute([$seller['id']]);
$productCount = $productCount->fetchColumn();

$totalOrders = $db->prepare("SELECT COUNT(DISTINCT o.id) FROM orders o JOIN order_items oi ON o.id = oi.order_id WHERE oi.seller_id = ?");
$totalOrders->execute([$seller['id']]);
$totalOrders = $totalOrders->fetchColumn();

$pendingOrders = $db->prepare("SELECT COUNT(DISTINCT o.id) FROM orders o JOIN order_items oi ON o.id = oi.order_id WHERE oi.seller_id = ? AND o.status IN ('pending','confirmed')");
$pendingOrders->execute([$seller['id']]);
$pendingOrders = $pendingOrders->fetchColumn();

$completedOrders = $db->prepare("SELECT COUNT(DISTINCT o.id) FROM orders o JOIN order_items oi ON o.id = oi.order_id WHERE oi.seller_id = ? AND o.status = 'delivered'");
$completedOrders->execute([$seller['id']]);
$completedOrders = $completedOrders->fetchColumn();

$revenue = $db->prepare("SELECT COALESCE(SUM(oi.subtotal),0) FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE oi.seller_id = ? AND o.status != 'cancelled'");
$revenue->execute([$seller['id']]);
$revenue = $revenue->fetchColumn();

$bestSellers = $db->prepare("SELECT p.name, p.sold_count, p.price FROM products p WHERE p.seller_id = ? ORDER BY p.sold_count DESC LIMIT 5");
$bestSellers->execute([$seller['id']]);
$bestSellers = $bestSellers->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0"><?= e($seller['shop_name']) ?></h4>
            <small class="text-muted">Seller Dashboard</small>
        </div>
        <div>
            <a href="<?= SITE_URL ?>/seller/add-product.php" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Add Product</a>
            <a href="<?= SITE_URL ?>/seller/logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-2 col-6">
            <div class="stat-card text-center">
                <div class="stat-value text-primary"><?= $productCount ?></div>
                <div class="stat-label">Products</div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="stat-card text-center">
                <div class="stat-value"><?= $totalOrders ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="stat-card text-center">
                <div class="stat-value text-warning"><?= $pendingOrders ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="stat-card text-center">
                <div class="stat-value text-success"><?= $completedOrders ?></div>
                <div class="stat-label">Completed</div>
            </div>
        </div>
        <div class="col-md-4 col-12">
            <div class="stat-card text-center">
                <div class="stat-value text-success"><?= formatPrice($revenue) ?></div>
                <div class="stat-label">Total Revenue · Balance: <?= formatPrice($seller['available_balance']) ?></div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="table-card">
                <div class="p-3 border-bottom"><h6 class="mb-0 fw-semibold">Best Selling Products</h6></div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light"><tr><th>Product</th><th>Sold</th><th>Price</th></tr></thead>
                        <tbody>
                            <?php if (empty($bestSellers)): ?>
                            <tr><td colspan="3" class="text-center text-muted">No sales yet</td></tr>
                            <?php else: foreach ($bestSellers as $p): ?>
                            <tr>
                                <td><?= e($p['name']) ?></td>
                                <td><?= $p['sold_count'] ?></td>
                                <td><?= formatPrice($p['price']) ?></td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="table-card p-3">
                <h6 class="fw-semibold mb-3">Quick Links</h6>
                <div class="d-grid gap-2">
                    <a href="<?= SITE_URL ?>/seller/products.php" class="btn btn-outline-primary">Manage Products</a>
                    <a href="<?= SITE_URL ?>/seller/orders.php" class="btn btn-outline-primary">View Orders</a>
                    <a href="<?= SITE_URL ?>/seller/inventory.php" class="btn btn-outline-primary">Inventory</a>
                    <a href="<?= SITE_URL ?>/seller/earnings.php" class="btn btn-outline-primary">Earnings</a>
                    <a href="<?= SITE_URL ?>/seller/profile.php" class="btn btn-outline-secondary">Shop Profile</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>