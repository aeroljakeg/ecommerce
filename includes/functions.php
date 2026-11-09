<?php
/**
 * Core Helper Functions
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Start secure session
 */
function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => 86400 * 7,
            'path'     => '/',
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly'  => true,
            'samesite' => 'Lax'
        ]);
        session_start();
    }
}

/**
 * Generate CSRF token
 */
function generateCsrfToken(): string
{
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken(?string $token): bool
{
    if (empty($token) || empty($_SESSION[CSRF_TOKEN_NAME])) {
        return false;
    }
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Escape output for HTML
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Format price in Philippine Peso
 */
function formatPrice($amount): string
{
    return '₱' . number_format((float)$amount, 2);
}

/**
 * Generate unique order number
 */
function generateOrderNumber(): string
{
    return 'TM' . date('ymd') . strtoupper(substr(uniqid(), -6));
}

/**
 * Generate affiliate code
 */
function generateAffiliateCode(int $userId): string
{
    return strtoupper(substr(md5($userId . time() . random_bytes(4)), 0, 10));
}

/**
 * Generate unique slug
 */
function generateSlug(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

/**
 * Get setting value
 */
function getSetting(string $key, $default = null)
{
    static $settings = null;
    if ($settings === null) {
        $db = getDB();
        $stmt = $db->query("SELECT setting_key, setting_value FROM website_settings");
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $settings[$key] ?? $default;
}

/**
 * Flash message helpers
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Redirect helper
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * Check if request is POST
 */
function isPost(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Get current user (customer)
 */
function currentUser(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    static $user = null;
    if ($user === null) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND status = 'active'");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;
        if (!$user) {
            unset($_SESSION['user_id']);
        }
    }
    return $user;
}

/**
 * Get current seller
 */
function currentSeller(): ?array
{
    if (empty($_SESSION['seller_id'])) {
        return null;
    }
    static $seller = null;
    if ($seller === null) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM sellers WHERE id = ? AND status = 'approved'");
        $stmt->execute([$_SESSION['seller_id']]);
        $seller = $stmt->fetch() ?: null;
        if (!$seller) {
            unset($_SESSION['seller_id']);
        }
    }
    return $seller;
}

/**
 * Get current admin
 */
function currentAdmin(): ?array
{
    if (empty($_SESSION['admin_id'])) {
        return null;
    }
    static $admin = null;
    if ($admin === null) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM admins WHERE id = ? AND status = 'active'");
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch() ?: null;
        if (!$admin) {
            unset($_SESSION['admin_id']);
        }
    }
    return $admin;
}

/**
 * Require login for customer
 */
function requireUserLogin(): void
{
    if (!currentUser()) {
        setFlash('warning', 'Please login to continue.');
        redirect(SITE_URL . '/user/login.php');
    }
}

/**
 * Require seller login
 */
function requireSellerLogin(): void
{
    if (!currentSeller()) {
        setFlash('warning', 'Please login as a seller.');
        redirect(SITE_URL . '/seller/login.php');
    }
}

/**
 * Require admin login
 */
function requireAdminLogin(): void
{
    if (!currentAdmin()) {
        setFlash('warning', 'Admin access required.');
        redirect(SITE_URL . '/admin/login.php');
    }
}

/**
 * Get product primary image
 */
function getProductImage(int $productId): string
{
    $db = getDB();
    $stmt = $db->prepare("SELECT image_path FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC LIMIT 1");
    $stmt->execute([$productId]);
    $row = $stmt->fetch();
    if ($row && file_exists(ROOT_PATH . '/assets/' . $row['image_path'])) {
        return SITE_URL . '/assets/' . $row['image_path'];
    }
    // Placeholder
    return 'https://via.placeholder.com/400x400?text=No+Image';
}

/**
 * Calculate final price (discount if available)
 */
function getFinalPrice(array $product): float
{
    if (!empty($product['discount_price']) && $product['discount_price'] > 0 && $product['discount_price'] < $product['price']) {
        return (float)$product['discount_price'];
    }
    return (float)$product['price'];
}

/**
 * Get cart count for current user/session
 */
function getCartCount(): int
{
    $db = getDB();
    $user = currentUser();
    if ($user) {
        $stmt = $db->prepare("SELECT SUM(ci.quantity) as total FROM cart_items ci JOIN carts c ON ci.cart_id = c.id WHERE c.user_id = ?");
        $stmt->execute([$user['id']]);
    } else {
        $sessionId = session_id();
        $stmt = $db->prepare("SELECT SUM(ci.quantity) as total FROM cart_items ci JOIN carts c ON ci.cart_id = c.id WHERE c.session_id = ?");
        $stmt->execute([$sessionId]);
    }
    $row = $stmt->fetch();
    return (int)($row['total'] ?? 0);
}

/**
 * Get or create cart
 */
function getOrCreateCart(): int
{
    $db = getDB();
    $user = currentUser();
    if ($user) {
        $stmt = $db->prepare("SELECT id FROM carts WHERE user_id = ? LIMIT 1");
        $stmt->execute([$user['id']]);
        $cart = $stmt->fetch();
        if ($cart) {
            return (int)$cart['id'];
        }
        $stmt = $db->prepare("INSERT INTO carts (user_id) VALUES (?)");
        $stmt->execute([$user['id']]);
        return (int)$db->lastInsertId();
    } else {
        $sessionId = session_id();
        $stmt = $db->prepare("SELECT id FROM carts WHERE session_id = ? LIMIT 1");
        $stmt->execute([$sessionId]);
        $cart = $stmt->fetch();
        if ($cart) {
            return (int)$cart['id'];
        }
        $stmt = $db->prepare("INSERT INTO carts (session_id) VALUES (?)");
        $stmt->execute([$sessionId]);
        return (int)$db->lastInsertId();
    }
}

/**
 * Sanitize filename for uploads
 */
function sanitizeFilename(string $filename): string
{
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $name = bin2hex(random_bytes(8)) . '_' . time();
    return $name . '.' . $ext;
}

/**
 * Validate image upload
 */
function validateImageUpload(array $file, int $maxSize = 2097152): array
{
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $errors = [];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Upload error occurred.';
        return $errors;
    }
    if ($file['size'] > $maxSize) {
        $errors[] = 'File too large. Max 2MB.';
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, $allowed)) {
        $errors[] = 'Invalid file type. Only JPG, PNG, WEBP, GIF allowed.';
    }
    return $errors;
}

/**
 * JSON response helper
 */
function jsonResponse(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Track affiliate click
 */
function trackAffiliateClick(string $code, ?int $productId = null): void
{
    $db = getDB();
    $stmt = $db->prepare("SELECT id, affiliate_id FROM affiliate_links WHERE unique_code = ?");
    $stmt->execute([$code]);
    $link = $stmt->fetch();
    if ($link) {
        $db->prepare("UPDATE affiliate_links SET clicks = clicks + 1 WHERE id = ?")->execute([$link['id']]);
        $db->prepare("UPDATE affiliate_accounts SET total_clicks = total_clicks + 1 WHERE id = ?")->execute([$link['affiliate_id']]);
        $db->prepare("INSERT INTO affiliate_clicks (affiliate_id, link_id, product_id, ip_address, user_agent, referrer) VALUES (?, ?, ?, ?, ?, ?)")
           ->execute([
               $link['affiliate_id'],
               $link['id'],
               $productId,
               $_SERVER['REMOTE_ADDR'] ?? null,
               substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
               substr($_SERVER['HTTP_REFERER'] ?? '', 0, 255)
           ]);
        // Store in session for attribution
        $_SESSION['affiliate_code'] = $code;
        $_SESSION['affiliate_id'] = $link['affiliate_id'];
    }
}

/**
 * Time ago helper
 */
function timeAgo(string $datetime): string
{
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hrs ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('M d, Y', $time);
}