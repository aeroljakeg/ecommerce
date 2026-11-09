<?php
require_once __DIR__ . '/../includes/auth.php';
logoutUser();
setFlash('success', 'You have been logged out.');
redirect(SITE_URL . '/');
?>