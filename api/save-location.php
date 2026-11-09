<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$lat = (float)($input['latitude'] ?? 0);
$lng = (float)($input['longitude'] ?? 0);
$address = trim($input['address'] ?? '');

if (!$lat || !$lng) {
    jsonResponse(['success' => false, 'message' => 'Invalid coordinates.']);
}

$db = getDB();
$user = currentUser();
$sessionId = session_id();

if ($user) {
    $db->prepare("INSERT INTO delivery_locations (user_id, latitude, longitude, address) VALUES (?, ?, ?, ?)")
       ->execute([$user['id'], $lat, $lng, $address]);
} else {
    $db->prepare("INSERT INTO delivery_locations (session_id, latitude, longitude, address) VALUES (?, ?, ?, ?)")
       ->execute([$sessionId, $lat, $lng, $address]);
}

// Also store in session for checkout
$_SESSION['delivery_lat'] = $lat;
$_SESSION['delivery_lng'] = $lng;
$_SESSION['delivery_address'] = $address;

jsonResponse(['success' => true]);