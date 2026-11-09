<!-- Footer -->
<footer class="footer bg-dark text-white pt-5 pb-3 mt-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <h5 class="fw-bold mb-3"><i class="fas fa-microchip me-1"></i> <?= e(getSetting('site_name', 'TechMoto PH')) ?></h5>
                <p class="text-white-50 small"><?= e(getSetting('site_tagline', 'Your One-Stop Shop for Computer & Motorcycle Parts')) ?></p>
                <p class="small mb-1"><i class="fas fa-map-marker-alt me-2"></i><?= e(getSetting('site_address', '123 Tech Avenue, Makati City')) ?></p>
                <p class="small mb-1"><i class="fas fa-phone me-2"></i><?= e(getSetting('site_phone')) ?></p>
                <p class="small"><i class="fas fa-envelope me-2"></i><?= e(getSetting('site_email')) ?></p>
            </div>
            <div class="col-lg-2 col-md-6">
                <h6 class="fw-semibold mb-3">Shop</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= SITE_URL ?>/products.php?category=computer-parts" class="text-white-50 text-decoration-none">Computer Parts</a></li>
                    <li class="mb-2"><a href="<?= SITE_URL ?>/products.php?category=motorcycle-parts" class="text-white-50 text-decoration-none">Motorcycle Parts</a></li>
                    <li class="mb-2"><a href="<?= SITE_URL ?>/products.php" class="text-white-50 text-decoration-none">All Products</a></li>
                    <li class="mb-2"><a href="<?= SITE_URL ?>/products.php?featured=1" class="text-white-50 text-decoration-none">Featured</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-md-6">
                <h6 class="fw-semibold mb-3">Account</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= SITE_URL ?>/user/login.php" class="text-white-50 text-decoration-none">Login</a></li>
                    <li class="mb-2"><a href="<?= SITE_URL ?>/user/register.php" class="text-white-50 text-decoration-none">Register</a></li>
                    <li class="mb-2"><a href="<?= SITE_URL ?>/user/orders.php" class="text-white-50 text-decoration-none">My Orders</a></li>
                    <li class="mb-2"><a href="<?= SITE_URL ?>/seller/register.php" class="text-white-50 text-decoration-none">Become a Seller</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-md-6">
                <h6 class="fw-semibold mb-3">Support</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= SITE_URL ?>/about.php" class="text-white-50 text-decoration-none">About Us</a></li>
                    <li class="mb-2"><a href="<?= SITE_URL ?>/contact.php" class="text-white-50 text-decoration-none">Contact</a></li>
                    <li class="mb-2"><a href="<?= SITE_URL ?>/faq.php" class="text-white-50 text-decoration-none">FAQ</a></li>
                    <li class="mb-2"><a href="<?= SITE_URL ?>/terms.php" class="text-white-50 text-decoration-none">Terms</a></li>
                    <li class="mb-2"><a href="<?= SITE_URL ?>/privacy.php" class="text-white-50 text-decoration-none">Privacy</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-md-6">
                <h6 class="fw-semibold mb-3">Payment</h6>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge bg-success"><i class="fas fa-money-bill-wave me-1"></i>COD</span>
                    <span class="badge bg-primary"><i class="fas fa-wallet me-1"></i>GCash</span>
                </div>
                <h6 class="fw-semibold mb-2 small">Follow Us</h6>
                <div class="d-flex gap-2">
                    <a href="#" class="text-white"><i class="fab fa-facebook fa-lg"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-instagram fa-lg"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-tiktok fa-lg"></i></a>
                </div>
            </div>
        </div>
        <hr class="border-secondary my-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small text-white-50">
            <p class="mb-0">&copy; <?= date('Y') ?> <?= e(getSetting('site_name', 'TechMoto PH')) ?>. All rights reserved.</p>
            <p class="mb-0">Made with <i class="fas fa-heart text-danger"></i> in the Philippines</p>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- Custom JS -->
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>

<?php if (!empty($extraJs)): ?>
    <?= $extraJs ?>
<?php endif; ?>
</body>
</html>