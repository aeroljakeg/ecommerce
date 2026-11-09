<?php
require_once __DIR__ . '/../includes/auth.php';
logoutAdmin();
redirect(SITE_URL . '/admin/login.php');
