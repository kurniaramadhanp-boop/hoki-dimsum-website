<?php
define('ORDER_DB_HOST', '127.0.0.1');
define('ORDER_DB_NAME', 'u173485424_Order_Hoki');
define('ORDER_DB_USER', 'u173485424_Order_Hoki');
define('ORDER_DB_PASS', 'OrderHoki95');

function order_db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . ORDER_DB_HOST . ';dbname=' . ORDER_DB_NAME . ';charset=utf8mb4';
        try {
            $pdo = new PDO($dsn, ORDER_DB_USER, ORDER_DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die('Koneksi ke database order gagal. Detail: ' . htmlspecialchars($e->getMessage()));
        }
    }
    return $pdo;
}
