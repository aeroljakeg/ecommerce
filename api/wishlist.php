<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');

$user = currentUser();
if (!$user) {
    jsonResponse(['success' => false, 'message' => 'login_required']);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$action = $input['action'] ?? 'toggle';
$productId = (int)($input['product_id'] ?? 0);

if (!$productId) {
    jsonResponse(['success' => false, 'message' => 'Invalid product.']);
}

$db = getDB();

if ($action === 'toggle') {
    $check = $db->prepare("SELECT id FROM wishlists WHERE user_id = ? AND product_id = ?");
    $check->execute([$user['id'], $productId]);
    $existing = $check->fetch();
    
    if ($existing) {
        $db->prepare("DELETE FROM wishlists WHERE id = ?")->execute([$existing['id']]);
        jsonResponse(['success' => true, 'in_wishlist' => false]);
    } else {
        $db->prepare("INSERT INTO wishlists (user_id, product_id) VALUES (?, ?)")->execute([$user['id'], $productId]);
        jsonResponse(['success' => true, 'in_wishlist' => true]);
    }
}

jsonResponse(['success' => false, 'message' => 'Invalid action.'], 400);