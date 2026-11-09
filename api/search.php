<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) {
    jsonResponse(['products' => []]);
}

$db = getDB();
$stmt = $db->prepare("SELECT p.id, p.name, p.price, p.discount_price 
    FROM products p 
    WHERE p.status = 'active' AND (p.name LIKE ? OR p.sku LIKE ?)
    ORDER BY p.sold_count DESC
    LIMIT 8");
$stmt->execute(["%$q%", "%$q%"]);
$products = $stmt->fetchAll();

$result = [];
foreach ($products as $p) {
    $result[] = [
        'id' => $p['id'],
        'name' => $p['name'],
        'price_formatted' => formatPrice(getFinalPrice($p)),
        'image' => getProductImage($p['id'])
    ];
}

jsonResponse(['products' => $result]);