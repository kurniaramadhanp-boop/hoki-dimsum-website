<?php
require_once __DIR__ . '/includes/cart.php';
$currentPage = 'blog.php';

$slug = $_GET['slug'] ?? '';
$stmt = db()->prepare("SELECT * FROM articles WHERE slug = ? AND is_published = 1");
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    http_response_code(404);
    $pageTitle = 'Artikel Tidak Ditemukan — ' . APP_NAME;
    require __DIR__ . '/includes/header.php';
    echo '<div class="section container text-center"><h1>404</h1><p>Artikel tidak ditemukan.</p><a href="' . BASE_URL . '/blog.php" class="btn btn-primary">Kembali ke Blog</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $article['judul'] . ' — ' . APP_NAME;
$pageDesc = mb_substr(strip_tags($article['konten']), 0, 160);

require __DIR__ . '/includes/header.php';
?>

<div class="section">
  <div class="container" style="max-width: 760px;">
    <a href="<?= BASE_URL ?>/blog.php" class="back-link">← Kembali ke Blog</a>
    <div class="article-cover">
      <?php if ($article['gambar_cover']): ?><img src="<?= UPLOAD_URL . '/' . e($article['gambar_cover']) ?>" alt=""><?php else: ?>📰<?php endif; ?>
    </div>
    <span class="eyebrow"><?= date('d M Y', strtotime($article['published_at'])) ?></span>
    <h1><?= e($article['judul']) ?></h1>
    <div class="article-content">
      <?= $article['konten'] ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
