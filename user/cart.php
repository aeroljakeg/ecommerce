<?php
require_once __DIR__ . '/../includes/auth.php';
$pageTitle = 'Shopping Cart';
$db = getDB();
$cartId = getOrCreateCart();

// Handle actions
if (isPost() && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    if ($action === 'update') {
        foreach ($_POST['qty'] ?? [] as $itemId => $qty) {
            $qty = max(1, (int)$qty);
            $db->prepare("UPDATE cart_items SET quantity = ? WHERE id = ? AND cart_id = ?")->execute([$qty, (int)$itemId, $cartId]);
        }
        setFlash('success', 'Cart updated.');
        redirect(SITE_URL . '/user/cart.php');
    }
    if ($action === 'remove') {
        $db->prepare("DELETE FROM cart_items WHERE id = ? AND cart_id = ?")->execute([(int)$_POST['item_id'], $cartId]);
        setFlash('success', 'Item removed.');
        redirect(SITE_URL . '/user/cart.php');
    }
}

$items = $db->prepare("SELECT ci.*, p.name, p.stock, p.slug, p.discount_price as current_discount, p.price as current_price
    FROM cart_items ci 
    JOIN products p ON ci.product_id = p.id
    WHERE ci.cart_id = ?");
$items->execute([$cartId]);
$items = $items->fetchAll();

$subtotal = 0;
foreach ($items as &$item) {
    $item['line_total'] = $item['price'] * $item['quantity'];
    $subtotal += $item['line_total'];
}
unset($item);

$shippingFee = (float)getSetting('shipping_fee', 50);
$freeMin = (float)getSetting('free_shipping_min', 2000);
if ($subtotal >= $freeMin) {
    $shippingFee = 0;
}
$total = $subtotal + $shippingFee;

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <h4 class="fw-bold mb-4"><i class="fas fa-shopping-cart me-2"></i>Shopping Cart</h4>
    
    <?php if (empty($items)): ?>
    <div class="empty-state bg-white rounded-lg">
        <i class="fas fa-shopping-cart"></i>
        <h5>Your cart is empty</h5>
        <p>Looks like you haven't added anything yet.</p>
        <a href="<?= SITE_URL ?>/products.php" class="btn btn-primary">Browse Products</a>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="table-card">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                    <input type="hidden" name="action" value="update">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="<?= e(getProductImage($item['product_id'])) ?>" width="60" height="60" class="rounded" style="object-fit:cover;" alt="">
                                            <div>
                                                <a href="<?= SITE_URL ?>/product-details.php?id=<?= $item['product_id'] ?>" class="fw-semibold text-decoration-none text-dark small"><?= e($item['name']) ?></a>
                                                <?php if ($item['quantity'] > $item['stock']): ?>
                                                <div class="text-danger small">Only <?= $item['stock'] ?> left</div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= formatPrice($item['price']) ?></td>
                                    <td>
                                        <input type="number" name="qty[<?= $item['id'] ?>]" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>" class="form-control form-control-sm" style="width:70px;">
                                    </td>
                                    <td class="fw-semibold"><?= formatPrice($item['line_total']) ?></td>
                                    <td>
                                        <button type="submit" formaction="" formmethod="POST" name="action" value="remove" onclick="this.form.item_id.value=<?= $item['id'] ?>;" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <input type="hidden" name="item_id" value="">
                    <div class="p-3 border-top d-flex justify-content-between">
                        <a href="<?= SITE_URL ?>/products.php" class="btn btn-outline-secondary btn-sm">Continue Shopping</a>
                        <button type="submit" class="btn btn-outline-primary btn-sm">Update Cart</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="bg-white rounded-lg shadow-sm-custom p-4">
                <h6 class="fw-semibold mb-3">Order Summary</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal</span>
                    <span><?= formatPrice($subtotal) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Shipping</span>
                    <span><?= $shippingFee > 0 ? formatPrice($shippingFee) : '<span class="text-success">FREE</span>' ?></span>
                </div>
                <?php if ($subtotal < $freeMin): ?>
                <div class="small text-muted mb-2">Add <?= formatPrice($freeMin - $subtotal) ?> more for free shipping</div>
                <?php endif; ?>
                <hr>
                <div class="d-flex justify-content-between mb-3">
                    <span class="fw-bold">Total</span>
                    <span class="fw-bold text-primary fs-5"><?= formatPrice($total) ?></span>
                </div>
                <a href="<?= SITE_URL ?>/user/checkout.php" class="btn btn-primary w-100">Proceed to Checkout</a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>