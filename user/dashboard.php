<?php
require_once __DIR__ . '/../includes/auth.php';
requireUserLogin();
$user = currentUser();
$db = getDB();
$pageTitle = 'My Dashboard';

// Stats
$orderCount = $db->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$orderCount->execute([$user['id']]);
$orderCount = $orderCount->fetchColumn();

$pendingOrders = $db->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status IN ('pending','confirmed','processing','shipped')");
$pendingOrders->execute([$user['id']]);
$pendingOrders = $pendingOrders->fetchColumn();

$wishlistCount = $db->prepare("SELECT COUNT(*) FROM wishlists WHERE user_id = ?");
$wishlistCount->execute([$user['id']]);
$wishlistCount = $wishlistCount->fetchColumn();

$recentOrders = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$recentOrders->execute([$user['id']]);
$recentOrders = $recentOrders->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php include __DIR__ . '/sidebar.php'; ?>
        </div>
        <div class="col-lg-9">
            <h4 class="fw-bold mb-4">Hello, <?= e(explode(' ', $user['full_name'])[0]) ?>!</h4>
            
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-value text-primary"><?= $orderCount ?></div>
                        <div class="stat-label">Total Orders</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-value text-warning"><?= $pendingOrders ?></div>
                        <div class="stat-label">Active Orders</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-value text-danger"><?= $wishlistCount ?></div>
                        <div class="stat-label">Wishlist Items</div>
                    </div>
                </div>
            </div>
            
            <div class="table-card">
                <div class="p-3 border-bottom">
                    <h6 class="mb-0 fw-semibold">Recent Orders</h6>
                </div>
                <?php if (empty($recentOrders)): ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <p>No orders yet.</p>
                    <a href="<?= SITE_URL ?>/products.php" class="btn btn-primary btn-sm">Start Shopping</a>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $o): ?>
                            <tr>
                                <td class="fw-semibold"><?= e($o['order_number']) ?></td>
                                <td class="small"><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
                                <td><?= formatPrice($o['total_amount']) ?></td>
                                <td><span class="badge badge-<?= e($o['status']) ?>"><?= ucfirst($o['status']) ?></span></td>
                                <td class="small"><?= strtoupper($o['payment_method']) ?> · <?= ucfirst(str_replace('_', ' ', $o['payment_status'])) ?></td>
                                <td><a href="<?= SITE_URL ?>/user/orders.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>