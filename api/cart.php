<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$action = $input['action'] ?? '';

$db = getDB();
$cartId = getOrCreateCart();

switch ($action) {
    case 'add':
        $productId = (int)($input['product_id'] ?? 0);
        $qty = max(1, (int)($input['quantity'] ?? 1));
        
        $stmt = $db->prepare("SELECT id, price, discount_price, stock, status FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        
        if (!$product || $product['status'] !== 'active') {
            jsonResponse(['success' => false, 'message' => 'Product not available.']);
        }
        if ($product['stock'] < $qty) {
            jsonResponse(['success' => false, 'message' => 'Insufficient stock.']);
        }
        
        $price = getFinalPrice($product);
        
        $check = $db->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
        $check->execute([$cartId, $productId]);
        $existing = $check->fetch();
        
        if ($existing) {
            $newQty = $existing['quantity'] + $qty;
            if ($newQty > $product['stock']) {
                jsonResponse(['success' => false, 'message' => 'Cannot exceed available stock.']);
            }
            $db->prepare("UPDATE cart_items SET quantity = ?, price = ? WHERE id = ?")
               ->execute([$newQty, $price, $existing['id']]);
        } else {
            $db->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, price) VALUES (?, ?, ?, ?)")
               ->execute([$cartId, $productId, $qty, $price]);
        }
        
        jsonResponse(['success' => true, 'message' => 'Added to cart', 'cart_count' => getCartCount()]);
        break;
        
    case 'update':
        $itemId = (int)($input['item_id'] ?? 0);
        $qty = max(1, (int)($input['quantity'] ?? 1));
        
        $stmt = $db->prepare("SELECT ci.*, p.stock FROM cart_items ci JOIN products p ON ci.product_id = p.id WHERE ci.id = ? AND ci.cart_id = ?");
        $stmt->execute([$itemId, $cartId]);
        $item = $stmt->fetch();
        
        if (!$item) {
            jsonResponse(['success' => false, 'message' => 'Item not found.']);
        }
        if ($qty > $item['stock']) {
            jsonResponse(['success' => false, 'message' => 'Insufficient stock.']);
        }
        
        $db->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?")->execute([$qty, $itemId]);
        jsonResponse(['success' => true, 'cart_count' => getCartCount()]);
        break;
        
    case 'remove':
        $itemId = (int)($input['item_id'] ?? 0);
        $db->prepare("DELETE FROM cart_items WHERE id = ? AND cart_id = ?")->execute([$itemId, $cartId]);
        jsonResponse(['success' => true, 'cart_count' => getCartCount()]);
        break;
        
    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action.'], 400);
}