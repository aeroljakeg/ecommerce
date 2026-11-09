<?php
/**
 * Authentication helpers
 */
require_once __DIR__ . '/functions.php';

startSecureSession();

/**
 * Login customer
 */
function loginUser(string $email, string $password): array
{
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([strtolower(trim($email))]);
    $user = $stmt->fetch();

    if (!$user) {
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }
    if ($user['status'] !== 'active') {
        return ['success' => false, 'message' => 'Your account is inactive or banned.'];
    }
    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }

    // Regenerate session ID for security
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['full_name'];

    // Merge guest cart if exists
    mergeGuestCart($user['id']);

    return ['success' => true, 'message' => 'Login successful.', 'user' => $user];
}

/**
 * Register customer
 */
function registerUser(array $data): array
{
    $db = getDB();
    $email = strtolower(trim($data['email']));
    $fullName = trim($data['full_name']);
    $phone = trim($data['phone'] ?? '');
    $password = $data['password'];
    $confirm = $data['confirm_password'];

    if (empty($fullName) || empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'Please fill in all required fields.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email address.'];
    }
    if (strlen($password) < 8) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters.'];
    }
    if ($password !== $confirm) {
        return ['success' => false, 'message' => 'Passwords do not match.'];
    }

    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email is already registered.'];
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (full_name, email, phone, password) VALUES (?, ?, ?, ?)");
    $stmt->execute([$fullName, $email, $phone, $hash]);
    $userId = (int)$db->lastInsertId();

    // Auto login
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $fullName;

    return ['success' => true, 'message' => 'Registration successful!', 'user_id' => $userId];
}

/**
 * Login seller
 */
function loginSeller(string $email, string $password): array
{
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM sellers WHERE email = ? LIMIT 1");
    $stmt->execute([strtolower(trim($email))]);
    $seller = $stmt->fetch();

    if (!$seller) {
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }
    if ($seller['status'] === 'pending') {
        return ['success' => false, 'message' => 'Your seller account is pending approval.'];
    }
    if ($seller['status'] === 'rejected') {
        return ['success' => false, 'message' => 'Your seller application was rejected.'];
    }
    if ($seller['status'] === 'suspended') {
        return ['success' => false, 'message' => 'Your seller account has been suspended.'];
    }
    if (!password_verify($password, $seller['password'])) {
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }

    session_regenerate_id(true);
    $_SESSION['seller_id'] = $seller['id'];
    $_SESSION['seller_name'] = $seller['shop_name'];

    return ['success' => true, 'message' => 'Login successful.', 'seller' => $seller];
}

/**
 * Login admin
 */
function loginAdmin(string $username, string $password): array
{
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM admins WHERE (username = ? OR email = ?) LIMIT 1");
    $stmt->execute([trim($username), trim($username)]);
    $admin = $stmt->fetch();

    if (!$admin || $admin['status'] !== 'active') {
        return ['success' => false, 'message' => 'Invalid credentials.'];
    }
    if (!password_verify($password, $admin['password'])) {
        return ['success' => false, 'message' => 'Invalid credentials.'];
    }

    session_regenerate_id(true);
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_name'] = $admin['full_name'];
    $_SESSION['admin_role'] = $admin['role'];

    $db->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?")->execute([$admin['id']]);

    return ['success' => true, 'message' => 'Welcome back!', 'admin' => $admin];
}

/**
 * Logout helpers
 */
function logoutUser(): void
{
    unset($_SESSION['user_id'], $_SESSION['user_name']);
}

function logoutSeller(): void
{
    unset($_SESSION['seller_id'], $_SESSION['seller_name']);
}

function logoutAdmin(): void
{
    unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_role']);
}

/**
 * Merge guest cart into user cart after login
 */
function mergeGuestCart(int $userId): void
{
    $db = getDB();
    $sessionId = session_id();
    $stmt = $db->prepare("SELECT id FROM carts WHERE session_id = ?");
    $stmt->execute([$sessionId]);
    $guestCart = $stmt->fetch();
    if (!$guestCart) return;

    $stmt = $db->prepare("SELECT id FROM carts WHERE user_id = ?");
    $stmt->execute([$userId]);
    $userCart = $stmt->fetch();

    if (!$userCart) {
        $db->prepare("UPDATE carts SET user_id = ?, session_id = NULL WHERE id = ?")
           ->execute([$userId, $guestCart['id']]);
        return;
    }

    // Merge items
    $items = $db->prepare("SELECT * FROM cart_items WHERE cart_id = ?");
    $items->execute([$guestCart['id']]);
    foreach ($items->fetchAll() as $item) {
        $check = $db->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
        $check->execute([$userCart['id'], $item['product_id']]);
        $existing = $check->fetch();
        if ($existing) {
            $db->prepare("UPDATE cart_items SET quantity = quantity + ? WHERE id = ?")
               ->execute([$item['quantity'], $existing['id']]);
        } else {
            $db->prepare("UPDATE cart_items SET cart_id = ? WHERE id = ?")
               ->execute([$userCart['id'], $item['id']]);
        }
    }
    $db->prepare("DELETE FROM carts WHERE id = ?")->execute([$guestCart['id']]);
}