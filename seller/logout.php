<?php
require_once __DIR__ . '/../includes/auth.php';
logoutSeller();
setFlash('success', 'Logged out successfully.');
redirect(SITE_URL . '/seller/login.php');
