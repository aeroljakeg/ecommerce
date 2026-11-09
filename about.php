<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'About Us';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="fw-bold mb-4">About TechMoto PH</h1>
            <p class="lead">Your trusted destination for computer components and motorcycle parts in the Philippines.</p>
            <p>We connect customers with verified sellers offering quality products at competitive prices. Whether you're building a new PC or upgrading your motorcycle, we've got you covered.</p>
            <h5 class="fw-semibold mt-4">What We Offer</h5>
            <ul>
                <li>Wide selection of computer parts — processors, GPUs, RAM, storage, peripherals</li>
                <li>Motorcycle helmets, lights, tires, engine parts and accessories</li>
                <li>Secure payments via Cash on Delivery and GCash</li>
                <li>Nationwide delivery through trusted courier partners</li>
                <li>Affiliate program so you can earn by sharing products you love</li>
            </ul>
            <h5 class="fw-semibold mt-4">Contact</h5>
            <p class="mb-1"><i class="fas fa-map-marker-alt me-2 text-primary"></i><?= e(getSetting('site_address')) ?></p>
            <p class="mb-1"><i class="fas fa-phone me-2 text-primary"></i><?= e(getSetting('site_phone')) ?></p>
            <p><i class="fas fa-envelope me-2 text-primary"></i><?= e(getSetting('site_email')) ?></p>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
