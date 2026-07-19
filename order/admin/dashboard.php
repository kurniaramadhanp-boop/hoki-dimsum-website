<?php
$pageTitle = 'Dashboard';
$activeMenu = 'dashboard';
require __DIR__ . '/includes/admin-header.php';

$todayCount = (int)db()->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()")->fetchColumn();
$pendingCount = (int)db()->query("SELECT COUNT(*) FROM orders WHERE status = 'pending_payment'")->fetchColumn();
$monthRevenue = (float)db()->query("SELECT COALESCE(SUM(total_bayar),0) FROM orders WHERE status != 'cancelled' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())")->fetchColumn();
$totalProducts = (int)db()->query("SELECT COUNT(*) FROM products WHERE is_available = 1")->fetchColumn();

$recentOrders = db()->query("SELECT o.*, b.nama AS branch_nama FROM orders o JOIN branches b ON b.id = o.branch_id ORDER BY o.created_at DESC LIMIT 8")->fetchAll();

$statusLabels = [
    'pending_payment' => 'Menunggu Bayar', 'paid' => 'Dibayar', 'preparing' => 'Disiapkan',
    'ready' => 'Siap', 'completed' => 'Selesai', 'cancelled' => 'Batal',
];
?>

<div class="stat-grid">
  <div class="stat-card">
    <div class="ic">🧾</div>
    <div class="val"><?= $todayCount ?></div>
    <div class="label">Order Hari Ini</div>
  </div>
  <div class="stat-card">
    <div class="ic">⏳</div>
    <div class="val"><?= $pendingCount ?></div>
    <div class="label">Menunggu Pembayaran</div>
  </div>
  <div class="stat-card">
    <div class="ic">💰</div>
    <div class="val"><?= rupiah($monthRevenue) ?></div>
    <div class="label">Revenue Bulan Ini</div>
  </div>
  <div class="stat-card">
    <div class="ic">🥟</div>
    <div class="val"><?= $totalProducts ?></div>
    <div class="label">Produk Aktif</div>
  </div>
</div>

<div class="panel">
  <div class="panel-head">
    <h3>Pesanan Terbaru</h3>
    <a href="<?= BASE_URL ?>/admin/pesanan.php" class="btn btn-outline btn-sm">Lihat Semua</a>
  </div>
  <div class="panel-body table-wrap">
    <table class="data-table">
      <thead><tr><th>Kode</th><th>Customer</th><th>Cabang</th><th>Total</th><th>Status</th><th>Waktu</th></tr></thead>
      <tbody>
        <?php if (!$recentOrders): ?>
        <tr><td colspan="6">Belum ada pesanan.</td></tr>
        <?php endif; ?>
        <?php foreach ($recentOrders as $o): ?>
        <tr>
          <td><a href="<?= BASE_URL ?>/admin/pesanan.php?view=<?= $o['id'] ?>"><?= e($o['order_code']) ?></a></td>
          <td><?= e($o['nama_customer']) ?></td>
          <td><?= e($o['branch_nama']) ?></td>
          <td><?= rupiah($o['total_bayar']) ?></td>
          <td><span class="status-pill status-<?= e($o['status']) ?>"><?= e($statusLabels[$o['status']] ?? $o['status']) ?></span></td>
          <td><?= date('d/m H:i', strtotime($o['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
