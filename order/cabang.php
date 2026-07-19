<?php
require_once __DIR__ . '/includes/cart.php';
$currentPage = 'cabang.php';
$pageTitle = 'Cabang — ' . APP_NAME;

$branches = db()->query("SELECT * FROM branches WHERE is_active = 1 ORDER BY nama")->fetchAll();
$waPusat = get_setting('wa_pusat', '');

require __DIR__ . '/includes/header.php';
?>

<div class="page-hero">
  <div class="container">
    <span class="eyebrow">Cabang Kami</span>
    <h1>Temukan Hoki Dimsum Terdekat</h1>
    <p>Datang langsung, order via GoFood/GrabFood/ShopeeFood, atau order online lewat website ini.</p>
  </div>
</div>

<div class="section" style="padding-top:0;">
  <div class="container">
    <?php if (!$branches): ?>
      <div class="empty-state"><div class="ic">📍</div><p>Belum ada cabang aktif.</p></div>
    <?php else: ?>
    <div class="grid">
      <?php foreach ($branches as $b): ?>
      <div class="card branch-card" style="grid-column: span 2;">
        <h3><?= e($b['nama']) ?></h3>
        <div class="branch-meta"><span class="ic">📍</span><span><?= e($b['alamat']) ?></span></div>
        <?php if ($b['jam_operasional']): ?><div class="branch-meta"><span class="ic">🕒</span><span><?= e($b['jam_operasional']) ?></span></div><?php endif; ?>
        <?php $bwa = $b['wa_number'] ?: $waPusat; ?>
        <?php if ($bwa): ?>
        <a href="<?= e(wa_link($bwa, 'Halo, saya mau tanya tentang cabang ' . $b['nama'])) ?>" class="btn btn-wa btn-sm" target="_blank" rel="noopener">💬 Chat Cabang Ini</a>
        <?php endif; ?>
        <?php if ($b['gofood_link'] || $b['grabfood_link'] || $b['shopeefood_link']): ?>
        <div class="branch-platforms">
          <?php if ($b['gofood_link']): ?><a href="<?= e($b['gofood_link']) ?>" target="_blank" rel="noopener" class="platform-link">🛵 GoFood</a><?php endif; ?>
          <?php if ($b['grabfood_link']): ?><a href="<?= e($b['grabfood_link']) ?>" target="_blank" rel="noopener" class="platform-link">🟢 GrabFood</a><?php endif; ?>
          <?php if ($b['shopeefood_link']): ?><a href="<?= e($b['shopeefood_link']) ?>" target="_blank" rel="noopener" class="platform-link">🧡 ShopeeFood</a><?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
