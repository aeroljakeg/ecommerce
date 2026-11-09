<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Home';
$pageDescription = 'Shop the best computer parts and motorcycle accessories in the Philippines.';
$db = getDB();
$featured = $db->query("SELECT p.*, s.shop_name FROM products p JOIN sellers s ON p.seller_id = s.id WHERE p.is_featured = 1 AND p.status = 'active' ORDER BY p.sold_count DESC LIMIT 8")->fetchAll();
$flash = $db->query("SELECT p.*, s.shop_name FROM products p JOIN sellers s ON p.seller_id = s.id WHERE p.discount_price IS NOT NULL AND p.discount_price > 0 AND p.status = 'active' ORDER BY ((p.price - p.discount_price) / p.price) DESC LIMIT 8")->fetchAll();
$bestsellers = $db->query("SELECT p.*, s.shop_name FROM products p JOIN sellers s ON p.seller_id = s.id WHERE p.status = 'active' ORDER BY p.sold_count DESC LIMIT 8")->fetchAll();
$categories = $db->query("SELECT * FROM categories WHERE parent_id IS NULL AND status = 'active' ORDER BY sort_order LIMIT 6")->fetchAll();
if (!empty($_GET['ref'])) trackAffiliateClick($_GET['ref']);
require_once __DIR__ . '/includes/header.php';
?>
<section class="hero-section">
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="mb-3">Power Up Your Ride &amp; Your Rig</h1>
                <p class="lead mb-4">Premium computer components and motorcycle parts at unbeatable prices. Free shipping on orders over &#8369;2,000. COD &amp; GCash available nationwide.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?= SITE_URL ?>/products.php?category=computer-parts" class="btn btn-light btn-hero text-primary"><i class="fas fa-microchip me-2"></i>Shop Computer Parts</a>
                    <a href="<?= SITE_URL ?>/products.php?category=motorcycle-parts" class="btn btn-outline-light btn-hero"><i class="fas fa-motorcycle me-2"></i>Shop Motorcycle Parts</a>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block text-center">
                <i class="fas fa-laptop-code" style="font-size:10rem;opacity:0.15;"></i>
            </div>
        </div>
    </div>
</section>
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div><h2 class="section-title">Shop by Category</h2><p class="section-subtitle mb-0">Find exactly what you need</p></div>
            <a href="<?= SITE_URL ?>/categories.php" class="btn btn-outline-primary btn-sm">View All</a>
        </div>
        <div class="row g-3">
            <?php
            $catIcons = ['computer-parts'=>'fa-desktop','motorcycle-parts'=>'fa-motorcycle','processors'=>'fa-microchip','graphics-cards'=>'fa-tv','memory-storage'=>'fa-hdd','pc-accessories'=>'fa-keyboard'];
            foreach ($categories as $cat):
                $icon = $catIcons[$cat['slug']] ?? 'fa-box';
            ?>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?= SITE_URL ?>/products.php?category=<?= e($cat['slug']) ?>" class="category-card">
                    <i class="fas <?= $icon ?>"></i>
                    <div class="fw-semibold small"><?= e($cat['name']) ?></div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php if ($flash): ?>
<section class="py-4 bg-white">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div><h2 class="section-title text-danger"><i class="fas fa-bolt me-2"></i>Flash Deals</h2></div>
            <a href="<?= SITE_URL ?>/products.php?sale=1" class="btn btn-outline-danger btn-sm">See All Deals</a>
        </div>
        <div class="row g-3">
            <?php foreach ($flash as $product): ?>
            <div class="col-6 col-md-4 col-lg-3"><?php include __DIR__ . '/includes/product-card.php'; ?></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div><h2 class="section-title">Featured Products</h2></div>
            <a href="<?= SITE_URL ?>/products.php?featured=1" class="btn btn-outline-primary btn-sm">View All</a>
        </div>
        <div class="row g-3">
            <?php foreach ($featured as $product): ?>
            <div class="col-6 col-md-4 col-lg-3"><?php include __DIR__ . '/includes/product-card.php'; ?></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<section class="py-4 bg-white">
    <div class="container">
        <h2 class="section-title mb-4">Best Sellers</h2>
        <div class="row g-3">
            <?php foreach ($bestsellers as $product): ?>
            <div class="col-6 col-md-4 col-lg-3"><?php include __DIR__ . '/includes/product-card.php'; ?></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<section class="py-5">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-5">
                <h2 class="section-title">Find Us &amp; Set Delivery Location</h2>
                <p class="section-subtitle">Visit our store or pin your delivery address.</p>
                <div class="mb-3">
                    <div class="d-flex align-items-start mb-2">
                        <i class="fas fa-map-marker-alt text-primary mt-1 me-2"></i>
                        <div><strong>Store Location</strong><br><span class="text-muted small"><?= e(getSetting('site_address')) ?></span></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Search your address</label>
                    <div class="input-group">
                        <input type="text" id="addressSearch" class="form-control" placeholder="Enter your address...">
                        <button class="btn btn-primary" type="button" id="btnSearchAddress"><i class="fas fa-search"></i></button>
                    </div>
                </div>
                <button class="btn btn-success" id="btnSaveLocation"><i class="fas fa-check me-1"></i> Confirm Delivery Location</button>
                <div id="locationStatus" class="mt-2 small"></div>
            </div>
            <div class="col-lg-7">
                <div class="map-container"><div id="storeMap"></div></div>
            </div>
        </div>
    </div>
</section>
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5"><h2 class="section-title">Why Shop With Us?</h2></div>
        <div class="row g-4 text-center">
            <div class="col-6 col-md-3"><div class="p-3"><div class="bg-primary-soft rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:64px;height:64px;"><i class="fas fa-shipping-fast fa-lg text-primary"></i></div><h6 class="fw-semibold">Fast Delivery</h6></div></div>
            <div class="col-6 col-md-3"><div class="p-3"><div class="bg-primary-soft rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:64px;height:64px;"><i class="fas fa-shield-alt fa-lg text-primary"></i></div><h6 class="fw-semibold">Secure Payment</h6></div></div>
            <div class="col-6 col-md-3"><div class="p-3"><div class="bg-primary-soft rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:64px;height:64px;"><i class="fas fa-undo fa-lg text-primary"></i></div><h6 class="fw-semibold">Easy Returns</h6></div></div>
            <div class="col-6 col-md-3"><div class="p-3"><div class="bg-primary-soft rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:64px;height:64px;"><i class="fas fa-headset fa-lg text-primary"></i></div><h6 class="fw-semibold">Support</h6></div></div>
        </div>
    </div>
</section>
<?php
$storeLat = getSetting('store_lat', '14.5547');
$storeLng = getSetting('store_lng', '121.0244');
$extraJs = <<<JS
<script>
document.addEventListener('DOMContentLoaded', function() {
    const storeLat = {$storeLat}, storeLng = {$storeLng};
    const map = L.map('storeMap').setView([storeLat, storeLng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {attribution:'&copy; OpenStreetMap'}).addTo(map);
    L.marker([storeLat, storeLng]).addTo(map).bindPopup('Our Store');
    L.circle([storeLat, storeLng], {color:'#0d6efd',fillColor:'#0d6efd',fillOpacity:0.08,radius:15000}).addTo(map);
    let userMarker = L.marker([storeLat, storeLng], {draggable:true,autoPan:true}).addTo(map);
    userMarker.bindPopup('Drag me to your delivery location').openPopup();
    document.getElementById('btnSearchAddress').addEventListener('click', function() {
        const q = document.getElementById('addressSearch').value.trim();
        if (!q) return;
        fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(q + ', Philippines') + '&limit=1')
            .then(r => r.json()).then(data => {
                if (data && data.length) {
                    const lat = parseFloat(data[0].lat), lng = parseFloat(data[0].lon);
                    map.setView([lat, lng], 15); userMarker.setLatLng([lat, lng]);
                    document.getElementById('locationStatus').innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> ' + data[0].display_name + '</span>';
                } else {
                    document.getElementById('locationStatus').innerHTML = '<span class="text-danger">Address not found.</span>';
                }
            });
    });
    document.getElementById('btnSaveLocation').addEventListener('click', function() {
        const pos = userMarker.getLatLng();
        fetch(SITE_URL + '/api/save-location.php', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({latitude:pos.lat, longitude:pos.lng, address:document.getElementById('addressSearch').value})
        }).then(r => r.json()).then(data => {
            document.getElementById('locationStatus').innerHTML = data.success
                ? '<span class="text-success"><i class="fas fa-check-circle"></i> Delivery location saved!</span>'
                : '<span class="text-danger">Failed to save</span>';
        });
    });
});
</script>
JS;
require_once __DIR__ . '/includes/footer.php';
?>
