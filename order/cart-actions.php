<?php
require_once __DIR__ . '/includes/cart.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$productId = (int)($_POST['product_id'] ?? 0);
$qty = (int)($_POST['qty'] ?? 1);

if ($productId <= 0) {
    echo json_encode(['ok' => false, 'message' => 'Produk tidak valid.']);
    exit;
}

switch ($action) {
    case 'add':
        cart_add($productId, $qty > 0 ? $qty : 1);
        break;
    case 'update':
        cart_set($productId, $qty);
        break;
    case 'remove':
        cart_remove($productId);
        break;
    default:
        echo json_encode(['ok' => false, 'message' => 'Aksi tidak dikenal.']);
        exit;
}

echo json_encode([
    'ok' => true,
    'cart_count' => cart_count(),
    'cart_total' => cart_total(),
]);
