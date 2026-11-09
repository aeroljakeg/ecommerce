<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();
$pageTitle = ucfirst(str_replace('.php','',basename(__FILE__)));
include __DIR__ . '/layout-header.php';
echo '<h4 class="fw-bold mb-4">' . htmlspecialchars($pageTitle) . '</h4>';
echo '<div class="table-card p-4"><p class="text-muted">This management page is ready for full CRUD operations. Connect to the database tables and implement list/add/edit/delete using the same patterns as the dashboard.</p>';
echo '<p>Database tables are fully defined in database.sql. Use getDB() and prepared statements.</p></div>';
include __DIR__ . '/layout-footer.php';
