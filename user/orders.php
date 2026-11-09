<?php
require_once __DIR__ . '/../includes/auth.php';
requireUserLogin();
$user = currentUser();
$db = getDB();
$pageTitle = 'My Orders';
$orderId = (int)($_GET['id'] ?? 0);

if ($orderId) {
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$orderId, $user['id']]);
    $order = $stmt->fetch();
    if (!$order) { setFlash('danger', 'Order not found.'); redirect(SITE_URL . '/user/orders.php'); }
    $items = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $items->execute([$orderId]);
    $items = $items->fetchAll();
    if (isPost() && ($_POST['action'] ?? '') === 'cancel' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        if (in_array($order['status'], ['pending', 'confirmed'])) {
            $db->prepare("UPDATE orders SET status = 'cancelled', cancelled_at = NOW() WHERE id = ?")->execute([$orderId]);
            foreach ($items as $it) {
                $db->prepare("UPDATE products SET stock = stock + ?, sold_count = GREATEST(0, sold_count - ?) WHERE id = ?")->execute([$it['quantity'], $it['quantity'], $it['product_id']]);
            }
            $db->prepare("UPDATE affiliate_commissions SET status = 'cancelled' WHERE order_id = ?")->execute([$orderId]);
            setFlash('success', 'Order cancelled.');
            redirect(SITE_URL . '/user/orders.php?id=' . $orderId);
        }
    }
    require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3"><?php include __DIR__ . '/sidebar.php'; ?></div>
        <div class="col-lg-9">
            <a href="<?= SITE_URL ?>/user/orders.php" class="small text-muted">&larr; Back</a>
            <h4 class="fw-bold mt-2 mb-3">Order <?= e($order['order_number']) ?></h4>
            <div class="row g-3 mb-4">
                <div class="col-md-4"><div class="stat-card"><div class="stat-label">Status</div><div class="fw-bold"><span class="badge badge-<?= e($order['status']) ?>"><?= ucfirst($order['status']) ?></span></div></div></div>
                <div class="col-md-4"><div class="stat-card"><div class="stat-label">Payment</div><div class="fw-bold"><?= strtoupper($order['payment_method']) ?></div></div></div>
                <div class="col-md-4"><div class="stat-card"><div class="stat-label">Total</div><div class="fw-bold text-primary"><?= formatPrice($order['total_amount']) ?></div></div></div>
            </div>
            <div class="bg-white rounded-lg shadow-sm-custom p-4 mb-4">
                <h6 class="fw-semibold mb-3">Items</h6>
                <?php foreach ($items as $it): ?>
                <div class="d-flex justify-content-between border-bottom py-2">
                    <div><div class="fw-semibold"><?= e($it['product_name']) ?></div><small class="text-muted"><?= formatPrice($it['product_price']) ?> × <?= $it['quantity'] ?></small></div>
                    <div class="fw-semibold"><?= formatPrice($it['subtotal']) ?></div>
                </div>
                <?php endforeach; ?>
                <div class="d-flex justify-content-between fw-bold fs-5 pt-3"><span>Total</span><span class="text-primary"><?= formatPrice($order['total_amount']) ?></span></div>
            </div>
            <div class="bg-white rounded-lg shadow-sm-custom p-4 mb-4">
                <h6 class="fw-semibold mb-2">Shipping To</h6>
                <p class="mb-0"><?= e($order['shipping_name']) ?> · <?= e($order['shipping_phone']) ?></p>
                <p class="text-muted small mb-0"><?= e($order['shipping_address']) ?></p>
            </div>
            <?php if (in_array($order['status'], ['pending','confirmed'])): ?>
            <form method="POST" onsubmit="return confirm('Cancel this order?');">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="action" value="cancel">
                <button type="submit" class="btn btn-outline-danger">Cancel Order</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; exit; }

$orders = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$orders->execute([$user['id']]);
$orders = $orders->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3"><?php include __DIR__ . '/sidebar.php'; ?></div>
        <div class="col-lg-9">
            <h4 class="fw-bold mb-4">My Orders</h4>
            <?php if (empty($orders)): ?>
            <div class="empty-state bg-white rounded-lg"><i class="fas fa-box-open"></i><h5>No orders yet</h5><a href="<?= SITE_URL ?>/products.php" class="btn btn-primary">Start Shopping</a></div>
            <?php else: ?>
            <div class="table-card"><div class="table-responsive"><table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>Order #</th><th>Date</th><th>Total</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td class="fw-semibold"><?= e($o['order_number']) ?></td>
                    <td class="small"><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
                    <td><?= formatPrice($o['total_amount']) ?></td>
                    <td><span class="badge badge-<?= e($o['status']) ?>"><?= ucfirst($o['status']) ?></span></td>
                    <td><a href="?id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-primary">Details</a></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div></div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
