<?php
require_once __DIR__ . '/includes/cart.php';
$currentPage = 'promo.php';
$pageTitle = 'Promo — ' . APP_NAME;

$promos = db()->query("SELECT * FROM promotions WHERE is_active = 1 AND (tanggal_selesai IS NULL OR tanggal_selesai >= CURDATE()) ORDER BY tanggal_mulai DESC")->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div class="page-hero">
  <div class="container">
    <span class="eyebrow">Promo Aktif</span>
    <h1>Lagi Hoki, Ada Promo Spesial!</h1>
    <p>Cek promo yang masih berlaku sebelum checkout.</p>
  </div>
</div>

<div class="section" style="padding-top:0;">
  <div class="container">
    <?php if (!$promos): ?>
      <div class="empty-state"><div class="ic">🏷️</div><p>Belum ada promo aktif saat ini. Cek lagi nanti ya!</p></div>
    <?php else: ?>
    <div class="grid">
      <?php foreach ($promos as $p): ?>
      <div class="card promo-card" style="grid-column: span 2;">
        <div class="promo-thumb">
          <?php if ($p['gambar']): ?><img src="<?= UPLOAD_URL . '/' . e($p['gambar']) ?>" alt="<?= e($p['judul']) ?>"><?php else: ?>🏷️<?php endif; ?>
        </div>
        <div class="promo-body">
          <div class="promo-dates">
            📅 <?= date('d M Y', strtotime($p['tanggal_mulai'])) ?> — <?= $p['tanggal_selesai'] ? date('d M Y', strtotime($p['tanggal_selesai'])) : 'Selesai' ?>
          </div>
          <h3><?= e($p['judul']) ?></h3>
          <p><?= nl2br(e($p['deskripsi'])) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
