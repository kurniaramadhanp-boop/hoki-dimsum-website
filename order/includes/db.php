<?php
require_once __DIR__ . '/config.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die('Koneksi database gagal. Pastikan database "' . DB_NAME . '" sudah dibuat dan import database/schema.sql. Detail: ' . htmlspecialchars($e->getMessage()));
        }

        // Migrasi aman untuk DB lama: tambahkan kolom yang belum ada tanpa perlu import ulang schema.sql.
        if (!$pdo->query("SHOW COLUMNS FROM products LIKE 'urutan'")->fetch()) {
            $pdo->exec('ALTER TABLE products ADD COLUMN urutan INT DEFAULT 0');
            $pdo->exec('UPDATE products SET urutan = id');
        }
        if (!$pdo->query("SHOW COLUMNS FROM products LIKE 'pos_sku'")->fetch()) {
            $pdo->exec('ALTER TABLE products ADD COLUMN pos_sku VARCHAR(20) NULL');
        }
        if (!$pdo->query("SHOW COLUMNS FROM branches LIKE 'qris_image'")->fetch()) {
            $pdo->exec('ALTER TABLE branches ADD COLUMN qris_image VARCHAR(255) NULL');
        }
    }
    return $pdo;
}
