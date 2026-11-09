<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();
$admin = currentAdmin();
$db = getDB();
$pageTitle = 'Admin Dashboard';

// Real stats
$stats = [
    'revenue' => $db->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE payment_status IN ('paid','pending') AND status != 'cancelled'")->fetchColumn(),
    'orders' => $db->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'customers' => $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'sellers' => $db->query("SELECT COUNT(*) FROM sellers WHERE status = 'approved'")->fetchColumn(),
    'products' => $db->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn(),
    'pending_orders' => $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn(),
    'pending_sellers' => $db->query("SELECT COUNT(*) FROM sellers WHERE status = 'pending'")->fetchColumn(),
    'pending_payments' => $db->query("SELECT COUNT(*) FROM payments WHERE status = 'pending' AND payment_method = 'gcash'")->fetchColumn(),
    'pending_withdrawals' => $db->query("SELECT COUNT(*) FROM affiliate_withdrawals WHERE status = 'pending'")->fetchColumn(),
];

$recentOrders = $db->query("SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 8")->fetchAll();

// Sales chart data (last 7 days)
$salesData = $db->query("SELECT DATE(created_at) as d, SUM(total_amount) as total FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND status != 'cancelled' GROUP BY DATE(created_at) ORDER BY d")->fetchAll();
$chartLabels = [];
$chartValues = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $chartLabels[] = date('M d', strtotime($d));
    $found = false;
    foreach ($salesData as $s) {
        if ($s['d'] === $d) {
            $chartValues[] = (float)$s['total'];
            $found = true;
            break;
        }
    }
    if (!$found) $chartValues[] = 0;
}

include __DIR__ . '/layout-header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Dashboard</h4>
    <span class="text-muted small">Welcome, <?= e($admin['full_name']) ?></span>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-value text-success"><?= formatPrice($stats['revenue']) ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-value text-primary"><?= number_format($stats['orders']) ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-value"><?= number_format($stats['customers']) ?></div>
            <div class="stat-label">Customers</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-value"><?= number_format($stats['sellers']) ?></div>
            <div class="stat-label">Sellers</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="stat-card border-warning">
            <div class="stat-value text-warning"><?= $stats['pending_orders'] ?></div>
            <div class="stat-label">Pending Orders</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card border-info">
            <div class="stat-value text-info"><?= $stats['pending_sellers'] ?></div>
            <div class="stat-label">Seller Applications</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card border-primary">
            <div class="stat-value text-primary"><?= $stats['pending_payments'] ?></div>
            <div class="stat-label">GCash to Verify</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card border-secondary">
            <div class="stat-value"><?= $stats['pending_withdrawals'] ?></div>
            <div class="stat-label">Affiliate Withdrawals</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="table-card p-3">
            <h6 class="fw-semibold mb-3">Sales (Last 7 Days)</h6>
            <canvas id="salesChart" height="120"></canvas>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="table-card">
            <div class="p-3 border-bottom"><h6 class="mb-0 fw-semibold">Quick Actions</h6></div>
            <div class="list-group list-group-flush">
                <a href="orders.php?status=pending" class="list-group-item list-group-item-action">Review Pending Orders</a>
                <a href="sellers.php?status=pending" class="list-group-item list-group-item-action">Seller Applications</a>
                <a href="payments.php" class="list-group-item list-group-item-action">Verify GCash Payments</a>
                <a href="products.php" class="list-group-item list-group-item-action">Manage Products</a>
                <a href="affiliates.php" class="list-group-item list-group-item-action">Affiliate Management</a>
            </div>
        </div>
    </div>
</div>

<div class="table-card mt-4">
    <div class="p-3 border-bottom d-flex justify-content-between">
        <h6 class="mb-0 fw-semibold">Recent Orders</h6>
        <a href="orders.php" class="small">View All</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentOrders as $o): ?>
                <tr>
                    <td><a href="orders.php?id=<?= $o['id'] ?>"><?= e($o['order_number']) ?></a></td>
                    <td><?= e($o['full_name']) ?></td>
                    <td><?= formatPrice($o['total_amount']) ?></td>
                    <td><span class="badge badge-<?= e($o['status']) ?>"><?= ucfirst($o['status']) ?></span></td>
                    <td class="small"><?= strtoupper($o['payment_method']) ?></td>
                    <td class="small"><?= date('M d, H:i', strtotime($o['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('salesChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [{
            label: 'Revenue (₱)',
            data: <?= json_encode($chartValues) ?>,
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13,110,253,0.1)',
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>

<?php include __DIR__ . '/layout-footer.php'; ?>