<?php
/**
 * One-time admin password setup
 * Visit once after importing database.sql, then DELETE this file.
 */
require_once __DIR__ . '/config/database.php';

$newPassword = 'Admin@12345';
$hash = password_hash($newPassword, PASSWORD_DEFAULT);

try {
    $db = getDB();
    $stmt = $db->prepare("UPDATE admins SET password = ? WHERE username = 'admin'");
    $stmt->execute([$hash]);
    
    // Also update sample sellers for convenience
    $sellerHash = password_hash('Seller@12345', PASSWORD_DEFAULT);
    $db->prepare("UPDATE sellers SET password = ? WHERE email IN ('seller1@techmoto.ph','seller2@techmoto.ph')")->execute([$sellerHash]);
    
    echo '<h2 style="font-family:sans-serif;color:green;">Setup complete!</h2>';
    echo '<p style="font-family:sans-serif;"><strong>Admin:</strong> username <code>admin</code> / password <code>Admin@12345</code></p>';
    echo '<p style="font-family:sans-serif;"><strong>Sample Sellers:</strong> password <code>Seller@12345</code></p>';
    echo '<p style="font-family:sans-serif;color:red;"><strong>DELETE this file (setup_admin.php) now for security.</strong></p>';
    echo '<p><a href="admin/login.php">Go to Admin Login</a> | <a href="seller/login.php">Seller Login</a> | <a href="./">Storefront</a></p>';
} catch (Exception $e) {
    echo 'Error: Make sure the database is imported first. ' . htmlspecialchars($e->getMessage());
}