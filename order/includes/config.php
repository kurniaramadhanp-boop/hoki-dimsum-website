<?php
/**
 * Konfigurasi dasar aplikasi Order Hoki Dimsum
 * Sesuaikan nilai di bawah ini dengan environment kamu (XAMPP lokal / Hostinger)
 */

// --- Database ---
// Auto-detect lokal (XAMPP) vs production (Hostinger) - konvensi sama seperti api.php
$isDev = (
    ($_SERVER['HTTP_HOST'] ?? '') === 'localhost' ||
    ($_SERVER['HTTP_HOST'] ?? '') === '127.0.0.1' ||
    substr($_SERVER['HTTP_HOST'] ?? '', 0, 8) === '192.168.' ||
    strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false ||
    strpos($_SERVER['HTTP_HOST'] ?? '', ':') !== false ||
    file_exists(__DIR__ . '/../../dev.flag')
);

if ($isDev) {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'hokidimsum_order');
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'u173485424_Order_Hoki');
    define('DB_USER', 'u173485424_Order_Hoki');
    define('DB_PASS', 'OrderHoki95');
}

// --- URL Dasar ---
// Lokal (XAMPP): http://localhost/Order Hoki Dimsum
// Produksi: https://order.pos-hokidimsum.com
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$scriptDir = rtrim($scriptDir, '/');
// Jika file dipanggil dari dalam /admin, base url harus naik satu folder
if (basename($scriptDir) === 'admin') {
    $scriptDir = dirname($scriptDir);
}
define('BASE_URL', $scheme . '://' . $_SERVER['HTTP_HOST'] . $scriptDir);

// --- Aplikasi ---
define('APP_NAME', 'Hoki Dimsum');
define('UPLOAD_DIR', __DIR__ . '/../uploads');
define('UPLOAD_URL', BASE_URL . '/uploads');
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024); // 2MB

// --- Timezone ---
date_default_timezone_set('Asia/Jakarta');

// --- Error reporting (matikan display_errors di produksi) ---
error_reporting(E_ALL);
ini_set('display_errors', $isDev ? '1' : '0');
