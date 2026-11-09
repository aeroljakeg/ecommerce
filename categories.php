<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Categories';
$db = getDB();
$categories = $db->query("SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.status = 'active') as product_count FROM categories c WHERE c.status = 'active' ORDER BY c.sort_order")->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>
<div class="container py-5">
    <h2 class="fw-bold mb-4">All Categories</h2>
    <div class="row g-3">
        <?php
        $icons = ['fa-desktop','fa-motorcycle','fa-microchip','fa-tv','fa-hdd','fa-keyboard','fa-hard-hat','fa-lightbulb','fa-cogs','fa-toolbox'];
        foreach ($categories as $i => $cat):
            $icon = $icons[$i % count($icons)];
        ?>
        <div class="col-6 col-md-4 col-lg-3">
            <a href="<?= SITE_URL ?>/products.php?category=<?= e($cat['slug']) ?>" class="category-card">
                <i class="fas <?= $icon ?>"></i>
                <div class="fw-semibold"><?= e($cat['name']) ?></div>
                <div class="small text-muted"><?= $cat['product_count'] ?> products</div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
