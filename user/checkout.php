<?php
require_once __DIR__ . '/../includes/auth.php';
requireUserLogin();
$user = currentUser();
$db = getDB();
$pageTitle = 'Checkout';

// Buy now single product
$buyNowId = (int)($_GET['buy_now'] ?? 0);
$cartId = getOrCreateCart();

if ($buyNowId) {
    // Create temporary cart item for buy now
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ? AND status = 'active' AND stock > 0");
    $stmt->execute([$buyNowId]);
    $bnProduct = $stmt->fetch();
    if ($bnProduct) {
        $price = getFinalPrice($bnProduct);
        // Clear existing and add this one for simplicity, or just use for this order
        $items = [[
            'product_id' => $bnProduct['id'],
            'name' => $bnProduct['name'],
            'price' => $price,
            'quantity' => 1,
            'seller_id' => $bnProduct['seller_id'],
            'stock' => $bnProduct['stock'],
            'line_total' => $price
        ]];
    } else {
        setFlash('danger', 'Product not available.');
        redirect(SITE_URL . '/products.php');
    }
} else {
    $stmt = $db->prepare("SELECT ci.*, p.name, p.stock, p.seller_id FROM cart_items ci JOIN products p ON ci.product_id = p.id WHERE ci.cart_id = ?");
    $stmt->execute([$cartId]);
    $rawItems = $stmt->fetchAll();
    $items = [];
    foreach ($rawItems as $it) {
        $items[] = [
            'product_id' => $it['product_id'],
            'name' => $it['name'],
            'price' => $it['price'],
            'quantity' => $it['quantity'],
            'seller_id' => $it['seller_id'],
            'stock' => $it['stock'],
            'line_total' => $it['price'] * $it['quantity']
        ];
    }
}

if (empty($items)) {
    setFlash('warning', 'Your cart is empty.');
    redirect(SITE_URL . '/user/cart.php');
}

$subtotal = array_sum(array_column($items, 'line_total'));
$shippingFee = (float)getSetting('shipping_fee', 50);
$freeMin = (float)getSetting('free_shipping_min', 2000);
if ($subtotal >= $freeMin) $shippingFee = 0;

// Addresses
$addresses = $db->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC");
$addresses->execute([$user['id']]);
$addresses = $addresses->fetchAll();

$error = '';
$success = false;

if (isPost() && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $paymentMethod = $_POST['payment_method'] ?? '';
    $addressId = (int)($_POST['address_id'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');
    
    // Validate stock again
    foreach ($items as $it) {
        $check = $db->prepare("SELECT stock FROM products WHERE id = ?");
        $check->execute([$it['product_id']]);
        $stock = $check->fetchColumn();
        if ($stock < $it['quantity']) {
            $error = "Insufficient stock for {$it['name']}.";
            break;
        }
    }
    
    if (!$error && !in_array($paymentMethod, ['cod', 'gcash'])) {
        $error = 'Please select a valid payment method.';
    }
    
    // Get shipping address
    $shippingName = $user['full_name'];
    $shippingPhone = $user['phone'] ?? '';
    $shippingAddress = '';
    $shippingCity = '';
    $shippingProvince = '';
    $shippingLat = $_SESSION['delivery_lat'] ?? null;
    $shippingLng = $_SESSION['delivery_lng'] ?? null;
    
    if ($addressId) {
        $addr = $db->prepare("SELECT * FROM addresses WHERE id = ? AND user_id = ?");
        $addr->execute([$addressId, $user['id']]);
        $addr = $addr->fetch();
        if ($addr) {
            $shippingName = $addr['recipient_name'];
            $shippingPhone = $addr['phone'];
            $shippingAddress = $addr['address_line'];
            $shippingCity = $addr['city'];
            $shippingProvince = $addr['province'];
            $shippingLat = $addr['latitude'] ?? $shippingLat;
            $shippingLng = $addr['longitude'] ?? $shippingLng;
        }
    } else {
        $shippingName = trim($_POST['shipping_name'] ?? $user['full_name']);
        $shippingPhone = trim($_POST['shipping_phone'] ?? '');
        $shippingAddress = trim($_POST['shipping_address'] ?? '');
        $shippingCity = trim($_POST['shipping_city'] ?? '');
        $shippingProvince = trim($_POST['shipping_province'] ?? '');
    }
    
    if (!$error && (empty($shippingName) || empty($shippingPhone) || empty($shippingAddress))) {
        $error = 'Please provide complete shipping information.';
    }
    
    // Coupon
    $discountAmount = 0;
    $couponCode = trim($_POST['coupon_code'] ?? '');
    if ($couponCode && !$error) {
        $coupon = $db->prepare("SELECT * FROM coupons WHERE code = ? AND status = 'active'");
        $coupon->execute([strtoupper($couponCode)]);
        $coupon = $coupon->fetch();
        if ($coupon) {
            $valid = true;
            if ($coupon['expires_at'] && strtotime($coupon['expires_at']) < time()) $valid = false;
            if ($coupon['usage_limit'] && $coupon['used_count'] >= $coupon['usage_limit']) $valid = false;
            if ($subtotal < $coupon['min_order']) $valid = false;
            if ($valid) {
                if ($coupon['type'] === 'percentage') {
                    $discountAmount = $subtotal * ($coupon['value'] / 100);
                    if ($coupon['max_discount']) $discountAmount = min($discountAmount, $coupon['max_discount']);
                } else {
                    $discountAmount = $coupon['value'];
                }
            }
        }
    }
    
    $totalAmount = $subtotal + $shippingFee - $discountAmount;
    
    if (!$error) {
        try {
            $db->beginTransaction();
            
            $orderNumber = generateOrderNumber();
            $paymentStatus = $paymentMethod === 'cod' ? 'pending' : 'awaiting_verification';
            
            // Affiliate
            $affiliateId = $_SESSION['affiliate_id'] ?? null;
            $affiliateCode = $_SESSION['affiliate_code'] ?? null;
            // Prevent self-referral
            if ($affiliateId) {
                $checkAff = $db->prepare("SELECT user_id FROM affiliate_accounts WHERE id = ?");
                $checkAff->execute([$affiliateId]);
                if ($checkAff->fetchColumn() == $user['id']) {
                    $affiliateId = null;
                    $affiliateCode = null;
                }
            }
            
            // Create order (multi-seller: group by seller or create one order)
            // For simplicity, one order with multiple items; seller_id is primary seller
            $primarySeller = $items[0]['seller_id'];
            
            $stmt = $db->prepare("INSERT INTO orders (order_number, user_id, seller_id, status, payment_method, payment_status, subtotal, shipping_fee, discount_amount, total_amount, coupon_code, affiliate_id, affiliate_code, shipping_name, shipping_phone, shipping_address, shipping_city, shipping_province, shipping_lat, shipping_lng, notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([
                $orderNumber, $user['id'], $primarySeller, 'pending', $paymentMethod, $paymentStatus,
                $subtotal, $shippingFee, $discountAmount, $totalAmount, $couponCode ?: null,
                $affiliateId, $affiliateCode,
                $shippingName, $shippingPhone, $shippingAddress, $shippingCity, $shippingProvince,
                $shippingLat, $shippingLng, $notes
            ]);
            $orderId = (int)$db->lastInsertId();
            
            // Order items + inventory
            foreach ($items as $it) {
                $db->prepare("INSERT INTO order_items (order_id, product_id, seller_id, product_name, product_price, quantity, subtotal) VALUES (?,?,?,?,?,?,?)")
                   ->execute([$orderId, $it['product_id'], $it['seller_id'], $it['name'], $it['price'], $it['quantity'], $it['line_total']]);
                
                $db->prepare("UPDATE products SET stock = stock - ?, sold_count = sold_count + ? WHERE id = ?")
                   ->execute([$it['quantity'], $it['quantity'], $it['product_id']]);
                
                $db->prepare("INSERT INTO inventory_logs (product_id, seller_id, change_qty, reason, reference) VALUES (?,?,?,?,?)")
                   ->execute([$it['product_id'], $it['seller_id'], -$it['quantity'], 'order', $orderNumber]);
            }
            
            // Payment record
            $db->prepare("INSERT INTO payments (order_id, payment_method, amount, status) VALUES (?,?,?,?)")
               ->execute([$orderId, $paymentMethod, $totalAmount, $paymentMethod === 'cod' ? 'pending' : 'pending']);
            
            // Coupon usage
            if ($couponCode && $discountAmount > 0) {
                $db->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE code = ?")->execute([strtoupper($couponCode)]);
            }
            
            // Clear cart if not buy now
            if (!$buyNowId) {
                $db->prepare("DELETE FROM cart_items WHERE cart_id = ?")->execute([$cartId]);
            }
            
            // Handle GCash receipt upload
            if ($paymentMethod === 'gcash' && !empty($_FILES['gcash_receipt']['name'])) {
                $file = $_FILES['gcash_receipt'];
                $errors = validateImageUpload($file);
                if (empty($errors)) {
                    $filename = sanitizeFilename($file['name']);
                    $dest = UPLOAD_PATH . '/receipts/' . $filename;
                    if (move_uploaded_file($file['tmp_name'], $dest)) {
                        $payId = $db->lastInsertId(); // last payment
                        // Get payment id properly
                        $payStmt = $db->prepare("SELECT id FROM payments WHERE order_id = ?");
                        $payStmt->execute([$orderId]);
                        $payId = $payStmt->fetchColumn();
                        $db->prepare("INSERT INTO payment_receipts (payment_id, file_path) VALUES (?, ?)")
                           ->execute([$payId, 'uploads/receipts/' . $filename]);
                        if (!empty($_POST['gcash_reference'])) {
                            $db->prepare("UPDATE payments SET reference_number = ? WHERE id = ?")
                               ->execute([trim($_POST['gcash_reference']), $payId]);
                        }
                    }
                }
            }
            
            // Affiliate commission (pending until order delivered)
            if ($affiliateId) {
                $rate = (float)getSetting('affiliate_commission_rate', 5);
                $commission = $subtotal * ($rate / 100);
                $db->prepare("INSERT INTO affiliate_commissions (affiliate_id, order_id, amount, rate, status) VALUES (?,?,?,?, 'pending')")
                   ->execute([$affiliateId, $orderId, $commission, $rate]);
                $db->prepare("UPDATE affiliate_accounts SET total_orders = total_orders + 1, pending_earnings = pending_earnings + ? WHERE id = ?")
                   ->execute([$commission, $affiliateId]);
            }
            
            $db->commit();
            
            // Clear affiliate session after use
            unset($_SESSION['affiliate_id'], $_SESSION['affiliate_code']);
            
            setFlash('success', 'Order placed successfully! Order #' . $orderNumber);
            redirect(SITE_URL . '/user/orders.php?id=' . $orderId);
            
        } catch (Exception $e) {
            $db->rollBack();
            $error = 'Failed to place order. Please try again.';
        }
    }
    
    $total = $subtotal + $shippingFee - $discountAmount;
} else {
    $total = $subtotal + $shippingFee;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <h4 class="fw-bold mb-4">Checkout</h4>
    
    <?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
        <div class="row g-4">
            <div class="col-lg-7">
                <!-- Shipping -->
                <div class="bg-white rounded-lg shadow-sm-custom p-4 mb-4">
                    <h6 class="fw-semibold mb-3"><i class="fas fa-map-marker-alt me-2 text-primary"></i>Shipping Address</h6>
                    
                    <?php if ($addresses): ?>
                    <div class="mb-3">
                        <?php foreach ($addresses as $i => $addr): ?>
                        <div class="form-check border rounded p-3 mb-2">
                            <input class="form-check-input" type="radio" name="address_id" value="<?= $addr['id'] ?>" id="addr<?= $addr['id'] ?>" <?= $i === 0 ? 'checked' : '' ?>>
                            <label class="form-check-label" for="addr<?= $addr['id'] ?>">
                                <strong><?= e($addr['recipient_name']) ?></strong> · <?= e($addr['phone']) ?><br>
                                <span class="small text-muted"><?= e($addr['address_line']) ?>, <?= e($addr['city']) ?>, <?= e($addr['province']) ?></span>
                            </label>
                        </div>
                        <?php endforeach; ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="address_id" value="0" id="addrNew">
                            <label class="form-check-label" for="addrNew">Use a different address</label>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div id="newAddressFields" class="<?= $addresses ? 'd-none' : '' ?>">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small">Recipient Name</label>
                                <input type="text" name="shipping_name" class="form-control" value="<?= e($user['full_name']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Phone</label>
                                <input type="text" name="shipping_phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Address</label>
                                <textarea name="shipping_address" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">City</label>
                                <input type="text" name="shipping_city" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Province</label>
                                <input type="text" name="shipping_province" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Payment -->
                <div class="bg-white rounded-lg shadow-sm-custom p-4 mb-4">
                    <h6 class="fw-semibold mb-3"><i class="fas fa-credit-card me-2 text-primary"></i>Payment Method</h6>
                    
                    <div class="form-check border rounded p-3 mb-2">
                        <input class="form-check-input" type="radio" name="payment_method" value="cod" id="payCod" checked>
                        <label class="form-check-label" for="payCod">
                            <strong><i class="fas fa-money-bill-wave me-1 text-success"></i> Cash on Delivery (COD)</strong>
                            <div class="small text-muted">Pay when you receive your order</div>
                        </label>
                    </div>
                    
                    <div class="form-check border rounded p-3 mb-3">
                        <input class="form-check-input" type="radio" name="payment_method" value="gcash" id="payGcash">
                        <label class="form-check-label" for="payGcash">
                            <strong><i class="fas fa-wallet me-1 text-primary"></i> GCash</strong>
                            <div class="small text-muted">Send payment and upload receipt</div>
                        </label>
                    </div>
                    
                    <div id="gcashDetails" class="d-none border rounded p-3 bg-light">
                        <p class="small mb-2">Please send payment to:</p>
                        <div class="fw-bold"><?= e(getSetting('gcash_name', 'TechMoto PH Official')) ?></div>
                        <div class="fs-5 text-primary fw-bold mb-2"><?= e(getSetting('gcash_number', '09171234567')) ?></div>
                        <div class="mb-2">
                            <label class="form-label small">GCash Reference Number</label>
                            <input type="text" name="gcash_reference" class="form-control form-control-sm" placeholder="e.g. 1234567890">
                        </div>
                        <div>
                            <label class="form-label small">Upload Payment Screenshot</label>
                            <input type="file" name="gcash_receipt" class="form-control form-control-sm" accept="image/*">
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm-custom p-4">
                    <label class="form-label small fw-semibold">Order Notes (optional)</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Any special instructions..."></textarea>
                </div>
            </div>
            
            <div class="col-lg-5">
                <div class="bg-white rounded-lg shadow-sm-custom p-4 sticky-top" style="top:80px;">
                    <h6 class="fw-semibold mb-3">Order Summary</h6>
                    <?php foreach ($items as $it): ?>
                    <div class="d-flex justify-content-between small mb-2">
                        <span><?= e($it['name']) ?> × <?= $it['quantity'] ?></span>
                        <span><?= formatPrice($it['line_total']) ?></span>
                    </div>
                    <?php endforeach; ?>
                    <hr>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Subtotal</span>
                        <span><?= formatPrice($subtotal) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Shipping</span>
                        <span><?= $shippingFee > 0 ? formatPrice($shippingFee) : 'FREE' ?></span>
                    </div>
                    <div class="mb-2">
                        <div class="input-group input-group-sm">
                            <input type="text" name="coupon_code" class="form-control" placeholder="Coupon code">
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-bold">Total</span>
                        <span class="fw-bold text-primary fs-5"><?= formatPrice($total) ?></span>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 btn-lg">Place Order</button>
                    <p class="small text-muted text-center mt-2 mb-0">By placing this order you agree to our Terms &amp; Conditions</p>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.querySelectorAll('input[name="payment_method"]').forEach(el => {
    el.addEventListener('change', function() {
        document.getElementById('gcashDetails').classList.toggle('d-none', this.value !== 'gcash');
    });
});
document.getElementById('addrNew')?.addEventListener('change', function() {
    document.getElementById('newAddressFields').classList.toggle('d-none', !this.checked);
});
document.querySelectorAll('input[name="address_id"]').forEach(el => {
    el.addEventListener('change', function() {
        if (this.value !== '0') {
            document.getElementById('newAddressFields').classList.add('d-none');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>