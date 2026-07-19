<?php
/**
 * Konfigurasi dasar aplikasi Order Hoki Dimsum
 * Sesuaikan nilai di bawah ini dengan environment kamu (XAMPP lokal / Hostinger)
 */

// --- Database ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'ISI_NAMA_DATABASE');
define('DB_USER', 'ISI_USER_DATABASE');
define('DB_PASS', 'ISI_PASSWORD_DATABASE');

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
ini_set('display_errors', '1');
