<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Products';
$db = getDB();

$categorySlug = $_GET['category'] ?? '';
$search = trim($_GET['q'] ?? '');
$sort = $_GET['sort'] ?? 'newest';
$minPrice = isset($_GET['min']) ? (float)$_GET['min'] : null;
$maxPrice = isset($_GET['max']) ? (float)$_GET['max'] : null;
$featured = isset($_GET['featured']);
$sale = isset($_GET['sale']);
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = ITEMS_PER_PAGE;
$offset = ($page - 1) * $perPage;

$where = ["p.status = 'active'"];
$params = [];

if ($categorySlug) {
    $where[] = "(c.slug = ? OR c2.slug = ?)";
    $params[] = $categorySlug;
    $params[] = $categorySlug;
}
if ($search) {
    $where[] = "(p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($minPrice !== null) {
    $where[] = "COALESCE(p.discount_price, p.price) >= ?";
    $params[] = $minPrice;
}
if ($maxPrice !== null) {
    $where[] = "COALESCE(p.discount_price, p.price) <= ?";
    $params[] = $maxPrice;
}
if ($featured) $where[] = "p.is_featured = 1";
if ($sale) $where[] = "p.discount_price IS NOT NULL AND p.discount_price > 0";

$whereSql = implode(' AND ', $where);
$orderMap = [
    'newest' => 'p.created_at DESC',
    'price_asc' => 'COALESCE(p.discount_price, p.price) ASC',
    'price_desc' => 'COALESCE(p.discount_price, p.price) DESC',
    'popular' => 'p.sold_count DESC',
    'rating' => 'p.rating DESC'
];
$orderSql = $orderMap[$sort] ?? $orderMap['newest'];

$countSql = "SELECT COUNT(*) FROM products p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN categories c2 ON c.parent_id = c2.id WHERE $whereSql";
$stmt = $db->prepare($countSql);
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();
$totalPages = max(1, ceil($total / $perPage));

$sql = "SELECT p.*, s.shop_name, c.name as category_name FROM products p JOIN sellers s ON p.seller_id = s.id LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN categories c2 ON c.parent_id = c2.id WHERE $whereSql ORDER BY $orderSql LIMIT $perPage OFFSET $offset";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
$allCategories = $db->query("SELECT * FROM categories WHERE status = 'active' ORDER BY sort_order")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<div class="container py-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= SITE_URL ?>/">Home</a></li><li class="breadcrumb-item active">Products</li></ol></nav>
    <div class="row g-4">
        <div class="col-lg-3">
            <div class="bg-white rounded-lg shadow-sm-custom p-3 sticky-top" style="top:80px;">
                <h6 class="fw-semibold mb-3">Filters</h6>
                <form method="GET" action="">
                    <?php if ($search): ?><input type="hidden" name="q" value="<?= e($search) ?>"><?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Category</label>
                        <select name="category" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            <?php foreach ($allCategories as $cat): ?>
                            <option value="<?= e($cat['slug']) ?>" <?= $categorySlug === $cat['slug'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Price Range</label>
                        <div class="d-flex gap-2">
                            <input type="number" name="min" class="form-control form-control-sm" placeholder="Min" value="<?= $minPrice !== null ? e($minPrice) : '' ?>">
                            <input type="number" name="max" class="form-control form-control-sm" placeholder="Max" value="<?= $maxPrice !== null ? e($maxPrice) : '' ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Sort By</label>
                        <select name="sort" class="form-select form-select-sm">
                            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
                            <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Most Popular</option>
                            <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                            <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Highest Rated</option>
                        </select>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="featured" value="1" id="feat" <?= $featured ? 'checked' : '' ?>>
                        <label class="form-check-label small" for="feat">Featured only</label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="sale" value="1" id="sale" <?= $sale ? 'checked' : '' ?>>
                        <label class="form-check-label small" for="sale">On Sale</label>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">Apply Filters</button>
                    <a href="<?= SITE_URL ?>/products.php" class="btn btn-outline-secondary btn-sm w-100 mt-2">Reset</a>
                </form>
            </div>
        </div>
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="mb-0 fw-bold"><?php if ($search): ?>Search: "<?= e($search) ?>"<?php elseif ($categorySlug): ?><?= e(ucwords(str_replace('-', ' ', $categorySlug))) ?><?php else: ?>All Products<?php endif; ?></h4>
                    <small class="text-muted"><?= number_format($total) ?> product(s) found</small>
                </div>
            </div>
            <?php if (empty($products)): ?>
            <div class="empty-state bg-white rounded-lg"><i class="fas fa-box-open"></i><h5>No products found</h5><p>Try adjusting your filters.</p><a href="<?= SITE_URL ?>/products.php" class="btn btn-primary">Browse All</a></div>
            <?php else: ?>
            <div class="row g-3">
                <?php foreach ($products as $product): ?>
                <div class="col-6 col-md-4"><?php include __DIR__ . '/includes/product-card.php'; ?></div>
                <?php endforeach; ?>
            </div>
            <?php if ($totalPages > 1): ?>
            <nav class="mt-4"><ul class="pagination justify-content-center">
                <?php $queryParams = $_GET; for ($i = 1; $i <= $totalPages; $i++): $queryParams['page'] = $i; ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="?<?= http_build_query($queryParams) ?>"><?= $i ?></a></li>
                <?php endfor; ?>
            </ul></nav>
            <?php endif; endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
