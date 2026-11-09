<?php
/**
 * Reusable Product Card Component
 * Expects $product array with product data
 */
$finalPrice = getFinalPrice($product);
$hasDiscount = !empty($product['discount_price']) && $product['discount_price'] > 0 && $product['discount_price'] < $product['price'];
$discountPct = $hasDiscount ? round((($product['price'] - $product['discount_price']) / $product['price']) * 100) : 0;
$img = getProductImage($product['id']);
?>
<div class="product-card">
    <div class="card-img-wrap">
        <?php if ($hasDiscount): ?>
        <span class="badge-discount">-<?= $discountPct ?>%</span>
        <?php endif; ?>
        <button class="btn-wishlist" data-id="<?= $product['id'] ?>" title="Add to Wishlist">
            <i class="fas fa-heart"></i>
        </button>
        <a href="<?= SITE_URL ?>/product-details.php?id=<?= $product['id'] ?>">
            <img src="<?= e($img) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
        </a>
    </div>
    <div class="card-body">
        <div class="product-name">
            <a href="<?= SITE_URL ?>/product-details.php?id=<?= $product['id'] ?>"><?= e($product['name']) ?></a>
        </div>
        <div class="rating">
            <?php
            $rating = (float)$product['rating'];
            for ($i = 1; $i <= 5; $i++):
                if ($i <= floor($rating)):
            ?>
                <i class="fas fa-star"></i>
            <?php elseif ($i - 0.5 <= $rating): ?>
                <i class="fas fa-star-half-alt"></i>
            <?php else: ?>
                <i class="far fa-star"></i>
            <?php endif; endfor; ?>
            <span class="text-muted ms-1">(<?= (int)$product['total_reviews'] ?>)</span>
        </div>
        <div class="price-wrap">
            <span class="price"><?= formatPrice($finalPrice) ?></span>
            <?php if ($hasDiscount): ?>
            <span class="price-original"><?= formatPrice($product['price']) ?></span>
            <?php endif; ?>
            <div class="sold-count"><?= number_format($product['sold_count']) ?> sold</div>
        </div>
    </div>
    <div class="card-footer d-flex gap-2">
        <button class="btn btn-primary btn-sm flex-fill btn-add-cart" data-id="<?= $product['id'] ?>">
            <i class="fas fa-cart-plus me-1"></i> Add
        </button>
        <a href="<?= SITE_URL ?>/product-details.php?id=<?= $product['id'] ?>" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-eye"></i>
        </a>
    </div>
</div>