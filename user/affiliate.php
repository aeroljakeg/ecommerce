<?php
require_once __DIR__ . '/../includes/auth.php';
requireUserLogin();
$user = currentUser();
$db = getDB();
$pageTitle = 'Affiliate Program';
$stmt = $db->prepare("SELECT * FROM affiliate_accounts WHERE user_id = ?");
$stmt->execute([$user['id']]);
$aff = $stmt->fetch();
if (!$aff && isPost() && ($_POST['action'] ?? '') === 'join' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $code = generateAffiliateCode($user['id']);
    $rate = (float)getSetting('affiliate_commission_rate', 5);
    $db->prepare("INSERT INTO affiliate_accounts (user_id, affiliate_code, commission_rate, status) VALUES (?,?,?,'active')")->execute([$user['id'], $code, $rate]);
    $db->prepare("UPDATE users SET is_affiliate = 1 WHERE id = ?")->execute([$user['id']]);
    setFlash('success', 'Welcome to the Affiliate Program!');
    redirect(SITE_URL . '/user/affiliate.php');
}
if ($aff) {
    $commissions = $db->prepare("SELECT * FROM affiliate_commissions WHERE affiliate_id = ? ORDER BY created_at DESC LIMIT 20");
    $commissions->execute([$aff['id']]);
    $commissions = $commissions->fetchAll();
    $products = $db->query("SELECT id, name FROM products WHERE status = 'active' ORDER BY sold_count DESC LIMIT 10")->fetchAll();
}
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3"><?php include __DIR__ . '/sidebar.php'; ?></div>
        <div class="col-lg-9">
            <h4 class="fw-bold mb-4"><i class="fas fa-handshake me-2"></i>Affiliate Program</h4>
            <?php if (!$aff): ?>
            <div class="bg-white rounded-lg shadow-sm-custom p-5 text-center">
                <i class="fas fa-hand-holding-usd fa-3x text-primary mb-3"></i>
                <h5 class="fw-bold">Earn by Sharing</h5>
                <p class="text-muted mb-4">Earn <?= getSetting('affiliate_commission_rate', 5) ?>% commission on successful referrals.</p>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                    <input type="hidden" name="action" value="join">
                    <button type="submit" class="btn btn-primary btn-lg">Join Affiliate Program</button>
                </form>
            </div>
            <?php else: ?>
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6"><div class="stat-card text-center"><div class="stat-value text-primary"><?= number_format($aff['total_clicks']) ?></div><div class="stat-label">Clicks</div></div></div>
                <div class="col-md-3 col-6"><div class="stat-card text-center"><div class="stat-value"><?= number_format($aff['total_orders']) ?></div><div class="stat-label">Orders</div></div></div>
                <div class="col-md-3 col-6"><div class="stat-card text-center"><div class="stat-value text-warning"><?= formatPrice($aff['pending_earnings']) ?></div><div class="stat-label">Pending</div></div></div>
                <div class="col-md-3 col-6"><div class="stat-card text-center"><div class="stat-value text-success"><?= formatPrice($aff['available_earnings']) ?></div><div class="stat-label">Available</div></div></div>
            </div>
            <div class="bg-white rounded-lg shadow-sm-custom p-4 mb-4">
                <h6 class="fw-semibold mb-2">Your Referral Code</h6>
                <div class="input-group mb-2">
                    <input type="text" class="form-control" id="affCode" value="<?= e($aff['affiliate_code']) ?>" readonly>
                    <button class="btn btn-outline-primary" onclick="copyToClipboard(document.getElementById('affCode').value)"><i class="fas fa-copy"></i></button>
                </div>
                <div class="input-group">
                    <input type="text" class="form-control" id="affLink" value="<?= SITE_URL ?>/?ref=<?= e($aff['affiliate_code']) ?>" readonly>
                    <button class="btn btn-outline-primary" onclick="copyToClipboard(document.getElementById('affLink').value)"><i class="fas fa-copy"></i> Copy Link</button>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm-custom p-4 mb-4">
                <h6 class="fw-semibold mb-3">Product Referral Links</h6>
                <?php foreach ($products as $p): $link = SITE_URL . '/product-details.php?id=' . $p['id'] . '&ref=' . $aff['affiliate_code']; ?>
                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                    <span class="small"><?= e($p['name']) ?></span>
                    <button class="btn btn-sm btn-outline-primary" onclick="copyToClipboard('<?= e($link) ?>')"><i class="fas fa-copy"></i></button>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="table-card">
                <div class="p-3 border-bottom"><h6 class="mb-0 fw-semibold">Commission History</h6></div>
                <?php if (empty($commissions)): ?>
                <div class="p-4 text-center text-muted">No commissions yet.</div>
                <?php else: ?>
                <div class="table-responsive"><table class="table mb-0">
                    <thead class="table-light"><tr><th>Date</th><th>Order</th><th>Amount</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($commissions as $c): ?>
                    <tr>
                        <td class="small"><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                        <td>#<?= $c['order_id'] ?></td>
                        <td><?= formatPrice($c['amount']) ?></td>
                        <td><span class="badge bg-<?= $c['status']==='paid'||$c['status']==='approved'?'success':($c['status']==='cancelled'?'danger':'warning') ?>"><?= ucfirst($c['status']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
