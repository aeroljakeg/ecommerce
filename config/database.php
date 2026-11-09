<?php
/**
 * Database Configuration
 * Update these values according to your XAMPP / hosting environment
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'ecommerce_mv');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default XAMPP password is empty
define('DB_CHARSET', 'utf8mb4');

// Site configuration
define('SITE_URL', 'http://localhost/ecommerce'); // Change to your domain
define('SITE_NAME', 'TechMoto PH');
define('SITE_EMAIL', 'support@techmoto.ph');

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/assets/uploads');
define('UPLOAD_URL', SITE_URL . '/assets/uploads');

// Security
define('SESSION_NAME', 'TECHMOTO_SESS');
define('CSRF_TOKEN_NAME', 'csrf_token');

// Pagination
define('ITEMS_PER_PAGE', 12);

// Affiliate default rate
define('DEFAULT_AFFILIATE_RATE', 5.00);

// Timezone
date_default_timezone_set('Asia/Manila');

/**
 * Get PDO connection (singleton)
 */
function getDB(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // In production, log this instead of displaying
            die('Database connection failed. Please check your configuration.');
        }
    }
    return $pdo;
}