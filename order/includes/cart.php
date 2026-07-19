<?php
require_once __DIR__ . '/auth.php';

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = []; // product_id => qty
}

function cart_add(int $productId, int $qty = 1): void
{
    $qty = max(1, $qty);
    $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + $qty;
}

function cart_set(int $productId, int $qty): void
{
    if ($qty <= 0) {
        unset($_SESSION['cart'][$productId]);
        return;
    }
    $_SESSION['cart'][$productId] = $qty;
}

function cart_remove(int $productId): void
{
    unset($_SESSION['cart'][$productId]);
}

function cart_qty(int $productId): int
{
    return (int)($_SESSION['cart'][$productId] ?? 0);
}

function cart_clear(): void
{
    $_SESSION['cart'] = [];
}

function cart_items(): array
{
    if (empty($_SESSION['cart'])) {
        return [];
    }
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();

    $items = [];
    foreach ($products as $p) {
        $qty = $_SESSION['cart'][$p['id']] ?? 0;
        if ($qty <= 0 || !$p['is_available']) {
            continue;
        }
        $items[] = [
            'product' => $p,
            'qty' => $qty,
            'subtotal' => $qty * (float)$p['harga'],
        ];
    }
    return $items;
}

function cart_count(): int
{
    return array_sum($_SESSION['cart'] ?? []);
}

function cart_total(): float
{
    $total = 0;
    foreach (cart_items() as $item) {
        $total += $item['subtotal'];
    }
    return $total;
}
