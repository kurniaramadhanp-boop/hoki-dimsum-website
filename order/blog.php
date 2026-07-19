<?php
require_once __DIR__ . '/includes/cart.php';
$currentPage = 'blog.php';
$pageTitle = 'Blog — ' . APP_NAME;

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 8;
$offset = ($page - 1) * $perPage;

$total = (int)db()->query("SELECT COUNT(*) FROM articles WHERE is_published = 1")->fetchColumn();
$stmt = db()->prepare("SELECT * FROM articles WHERE is_published = 1 ORDER BY published_at DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $perPage, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$articles = $stmt->fetchAll();
$totalPages = (int)ceil($total / $perPage);

require __DIR__ . '/includes/header.php';
?>

<div class="page-hero">
  <div class="container">
    <span class="eyebrow">Blog</span>
    <h1>Cerita &amp; Tips Seputar Dimsum</h1>
  </div>
</div>

<div class="section" style="padding-top:0;">
  <div class="container">
    <?php if (!$articles): ?>
      <div class="empty-state"><div class="ic">📰</div><p>Belum ada artikel dipublikasikan.</p></div>
    <?php else: ?>
    <div class="grid">
      <?php foreach ($articles as $a): ?>
      <a href="<?= BASE_URL ?>/blog-detail.php?slug=<?= urlencode($a['slug']) ?>" class="card article-card">
        <div class="article-thumb">
          <?php if ($a['gambar_cover']): ?><img src="<?= UPLOAD_URL . '/' . e($a['gambar_cover']) ?>" alt=""><?php else: ?>📰<?php endif; ?>
        </div>
        <div class="article-body">
          <div class="date"><?= date('d M Y', strtotime($a['published_at'])) ?></div>
          <h3><?= e($a['judul']) ?></h3>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php if ($totalPages > 1): ?>
    <div style="display:flex; gap:8px; justify-content:center; margin-top:24px;">
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i ?>" class="btn <?= $i === $page ? 'btn-primary' : 'btn-outline' ?> btn-sm"><?= $i ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
