<?php
require_once __DIR__ . '/includes/auth.php';
$db = getDB();
$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect(SITE_URL . '/products.php');
if (!empty($_GET['ref'])) trackAffiliateClick($_GET['ref'], $id);
$stmt = $db->prepare("SELECT p.*, s.shop_name, s.id as seller_id, s.rating as seller_rating, c.name as category_name, b.name as brand_name FROM products p JOIN sellers s ON p.seller_id = s.id LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN brands b ON p.brand_id = b.id WHERE p.id = ? AND p.status = 'active'");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) { setFlash('danger', 'Product not found.'); redirect(SITE_URL . '/products.php'); }
$db->prepare("UPDATE products SET views = views + 1 WHERE id = ?")->execute([$id]);
$images = $db->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order");
$images->execute([$id]);
$images = $images->fetchAll();
if (empty($images)) $images = [['image_path' => null, 'is_primary' => 1]];
$reviews = $db->prepare("SELECT r.*, u.full_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? AND r.status = 'approved' ORDER BY r.created_at DESC LIMIT 20");
$reviews->execute([$id]);
$reviews = $reviews->fetchAll();
$related = $db->prepare("SELECT p.*, s.shop_name FROM products p JOIN sellers s ON p.seller_id = s.id WHERE p.category_id = ? AND p.id != ? AND p.status = 'active' ORDER BY p.sold_count DESC LIMIT 4");
$related->execute([$product['category_id'], $id]);
$related = $related->fetchAll();
$user = currentUser();
$sessionId = session_id();
if ($user) {
    $db->prepare("DELETE FROM recently_viewed WHERE user_id = ? AND product_id = ?")->execute([$user['id'], $id]);
    $db->prepare("INSERT INTO recently_viewed (user_id, product_id) VALUES (?, ?)")->execute([$user['id'], $id]);
} else {
    $db->prepare("DELETE FROM recently_viewed WHERE session_id = ? AND product_id = ?")->execute([$sessionId, $id]);
    $db->prepare("INSERT INTO recently_viewed (session_id, product_id) VALUES (?, ?)")->execute([$sessionId, $id]);
}
$finalPrice = getFinalPrice($product);
$hasDiscount = !empty($product['discount_price']) && $product['discount_price'] > 0 && $product['discount_price'] < $product['price'];
$pageTitle = $product['name'];
require_once __DIR__ . '/includes/header.php';
?>
<div class="container py-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/">Home</a></li>
        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/products.php"><?= e($product['category_name'] ?? 'Products') ?></a></li>
        <li class="breadcrumb-item active"><?= e($product['name']) ?></li>
    </ol></nav>
    <div class="row g-4 bg-white rounded-lg shadow-sm-custom p-3 p-md-4">
        <div class="col-md-5">
            <div class="product-gallery-main mb-3">
                <?php $mainImg = $images[0]['image_path'] ? SITE_URL . '/assets/' . $images[0]['image_path'] : 'https://via.placeholder.com/500x500?text=No+Image'; ?>
                <img id="mainImage" src="<?= e($mainImg) ?>" alt="<?= e($product['name']) ?>" class="img-fluid w-100" style="max-height:420px;object-fit:contain;">
            </div>
        </div>
        <div class="col-md-7">
            <h1 class="h3 fw-bold mb-2"><?= e($product['name']) ?></h1>
            <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
                <div class="rating text-warning">
                    <?php for ($i = 1; $i <= 5; $i++): ?><i class="fas fa-star<?= $i > $product['rating'] ? ' far' : '' ?>"></i><?php endfor; ?>
                    <span class="text-muted ms-1"><?= number_format($product['rating'], 1) ?> (<?= $product['total_reviews'] ?>)</span>
                </div>
                <span class="text-muted small"><?= number_format($product['sold_count']) ?> sold</span>
            </div>
            <div class="mb-3">
                <span class="fs-2 fw-bold text-primary"><?= formatPrice($finalPrice) ?></span>
                <?php if ($hasDiscount): ?>
                <span class="text-muted text-decoration-line-through ms-2"><?= formatPrice($product['price']) ?></span>
                <?php endif; ?>
            </div>
            <div class="mb-3 small">
                <div class="row g-2">
                    <div class="col-6"><span class="text-muted">Brand:</span> <strong><?= e($product['brand_name'] ?? 'N/A') ?></strong></div>
                    <div class="col-6"><span class="text-muted">SKU:</span> <strong><?= e($product['sku'] ?? 'N/A') ?></strong></div>
                    <div class="col-6"><span class="text-muted">Stock:</span>
                        <?php if ($product['stock'] > 0): ?><span class="text-success fw-semibold"><?= $product['stock'] ?> available</span>
                        <?php else: ?><span class="text-danger fw-semibold">Out of stock</span><?php endif; ?>
                    </div>
                    <div class="col-6"><span class="text-muted">Seller:</span> <strong><?= e($product['shop_name']) ?></strong></div>
                </div>
            </div>
            <p class="text-muted"><?= e($product['short_description'] ?? mb_substr(strip_tags($product['description']), 0, 200)) ?></p>
            <?php if ($product['stock'] > 0): ?>
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="qty-control">
                    <button type="button" onclick="updateQty(document.getElementById('qty'), -1)">−</button>
                    <input type="number" id="qty" value="1" min="1" max="<?= $product['stock'] ?>">
                    <button type="button" onclick="updateQty(document.getElementById('qty'), 1)">+</button>
                </div>
                <button class="btn btn-primary btn-lg flex-fill btn-add-cart" data-id="<?= $product['id'] ?>" onclick="this.dataset.qty=document.getElementById('qty').value">
                    <i class="fas fa-cart-plus me-2"></i>Add to Cart
                </button>
                <button class="btn btn-outline-danger btn-lg btn-wishlist" data-id="<?= $product['id'] ?>"><i class="fas fa-heart"></i></button>
            </div>
            <a href="<?= SITE_URL ?>/user/checkout.php?buy_now=<?= $product['id'] ?>" class="btn btn-success btn-lg w-100 mb-3"><i class="fas fa-bolt me-2"></i>Buy Now</a>
            <?php else: ?>
            <div class="alert alert-warning">This product is currently out of stock.</div>
            <?php endif; ?>
        </div>
    </div>
    <div class="row g-4 mt-2">
        <div class="col-lg-8">
            <div class="bg-white rounded-lg shadow-sm-custom p-4">
                <h6 class="fw-semibold mb-3">Description</h6>
                <div><?= nl2br(e($product['description'])) ?></div>
                <hr>
                <h6 class="fw-semibold mb-3">Reviews (<?= count($reviews) ?>)</h6>
                <?php if (empty($reviews)): ?><p class="text-muted">No reviews yet.</p>
                <?php else: foreach ($reviews as $rev): ?>
                <div class="border-bottom pb-3 mb-3">
                    <strong><?= e($rev['full_name']) ?></strong>
                    <div class="text-warning small"><?php for ($i=1;$i<=5;$i++): ?><i class="fas fa-star<?= $i > $rev['rating'] ? ' far' : '' ?>"></i><?php endfor; ?></div>
                    <p class="mb-0 small"><?= e($rev['comment']) ?></p>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="bg-white rounded-lg shadow-sm-custom p-4">
                <h6 class="fw-semibold mb-3">Seller</h6>
                <div class="fw-semibold"><?= e($product['shop_name']) ?></div>
                <div class="small text-muted">★ <?= number_format($product['seller_rating'], 1) ?></div>
            </div>
        </div>
    </div>
    <?php if ($related): ?>
    <div class="mt-5">
        <h4 class="fw-bold mb-3">Related Products</h4>
        <div class="row g-3">
            <?php foreach ($related as $product): ?>
            <div class="col-6 col-md-3"><?php include __DIR__ . '/includes/product-card.php'; ?></div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
