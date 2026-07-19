<?php
$pageTitle = 'Artikel';
$activeMenu = 'artikel';
require __DIR__ . '/includes/admin-header.php';

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_article']) && csrf_check()) {
    $id = (int)($_POST['id'] ?? 0);
    $judul = trim($_POST['judul'] ?? '');
    $konten = $_POST['konten'] ?? '';
    $isPublished = isset($_POST['is_published']) ? 1 : 0;

    // Basic sanitize: strip script tags from admin-authored HTML content
    $konten = preg_replace('#<script\b[^>]*>(.*?)</script>#is', '', $konten);

    try {
        $gambar = null;
        if (!empty($_FILES['gambar_cover']['name'])) {
            $gambar = upload_image($_FILES['gambar_cover'], 'articles');
        }
        if ($judul === '' || trim(strip_tags($konten)) === '') {
            flash('error', 'Judul dan konten artikel wajib diisi.');
        } elseif ($id > 0) {
            if ($gambar) {
                db()->prepare('UPDATE articles SET judul=?, konten=?, gambar_cover=?, is_published=?, published_at=COALESCE(published_at, NOW()) WHERE id=?')
                    ->execute([$judul, $konten, $gambar, $isPublished, $id]);
            } else {
                db()->prepare('UPDATE articles SET judul=?, konten=?, is_published=?, published_at=COALESCE(published_at, NOW()) WHERE id=?')
                    ->execute([$judul, $konten, $isPublished, $id]);
            }
            flash('success', 'Artikel berhasil diperbarui.');
        } else {
            $slugBase = slugify($judul);
            $slug = $slugBase;
            $i = 1;
            $check = db()->prepare('SELECT COUNT(*) FROM articles WHERE slug = ?');
            do {
                $check->execute([$slug]);
                if ((int)$check->fetchColumn() === 0) break;
                $slug = $slugBase . '-' . (++$i);
            } while (true);

            db()->prepare('INSERT INTO articles (judul, slug, konten, gambar_cover, is_published, published_at) VALUES (?,?,?,?,?,?)')
                ->execute([$judul, $slug, $konten, $gambar ?? '', $isPublished, $isPublished ? date('Y-m-d H:i:s') : null]);
            flash('success', 'Artikel berhasil ditambahkan.');
        }
    } catch (RuntimeException $e) {
        flash('error', $e->getMessage());
    }
    redirect(BASE_URL . '/admin/artikel.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_article']) && csrf_check()) {
    db()->prepare('DELETE FROM articles WHERE id = ?')->execute([(int)$_POST['id']]);
    flash('success', 'Artikel berhasil dihapus.');
    redirect(BASE_URL . '/admin/artikel.php');
}

$articles = db()->query('SELECT * FROM articles ORDER BY id DESC')->fetchAll();
$editArticle = null;
if (!empty($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM articles WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $editArticle = $stmt->fetch();
}
?>

<div class="panel">
  <div class="panel-head">
    <h3><?= $editArticle ? 'Edit Artikel' : 'Tulis Artikel Baru' ?></h3>
    <?php if ($editArticle): ?><a href="<?= BASE_URL ?>/admin/artikel.php" class="btn btn-outline btn-sm">Batal Edit</a><?php endif; ?>
  </div>
  <div class="panel-body">
    <form method="post" enctype="multipart/form-data" class="admin-form" id="articleForm">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <?php if ($editArticle): ?><input type="hidden" name="id" value="<?= $editArticle['id'] ?>"><?php endif; ?>

      <div class="form-group">
        <label>Judul Artikel</label>
        <input type="text" name="judul" class="form-control" required value="<?= e($editArticle['judul'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label>Gambar Cover</label>
        <input type="file" name="gambar_cover" accept="image/png,image/jpeg,image/webp" class="form-control" data-image-input="coverPreview">
      </div>
      <div class="image-preview" id="coverPreview">
        <?php if (!empty($editArticle['gambar_cover'])): ?><img src="<?= UPLOAD_URL . '/' . e($editArticle['gambar_cover']) ?>" alt=""><?php else: ?>Tidak ada gambar<?php endif; ?>
      </div>

      <div class="form-group">
        <label>Konten Artikel</label>
        <div class="editor-toolbar" id="editorToolbar">
          <button type="button" data-cmd="bold"><b>B</b></button>
          <button type="button" data-cmd="italic"><i>I</i></button>
          <button type="button" data-cmd="underline"><u>U</u></button>
          <button type="button" data-cmd="insertUnorderedList">• List</button>
          <button type="button" data-cmd="insertOrderedList">1. List</button>
          <button type="button" data-cmd="formatBlock" data-value="H3">Judul</button>
          <button type="button" data-cmd="formatBlock" data-value="P">Paragraf</button>
          <button type="button" id="editorLinkBtn">🔗 Link</button>
        </div>
        <div id="editorArea" class="form-control editor-area" contenteditable="true"><?= $editArticle['konten'] ?? '<p>Tulis artikel di sini...</p>' ?></div>
        <textarea name="konten" id="editorHidden" style="display:none;"></textarea>
      </div>

      <div class="form-group">
        <label><input type="checkbox" name="is_published" <?= ($editArticle['is_published'] ?? 0) ? 'checked' : '' ?>> Publikasikan</label>
      </div>

      <button type="submit" name="save_article" value="1" class="btn btn-primary" id="articleSubmitBtn"><?= $editArticle ? 'Simpan Perubahan' : 'Simpan Artikel' ?></button>
    </form>
  </div>
</div>

<div class="panel">
  <div class="panel-head"><h3>Daftar Artikel (<?= count($articles) ?>)</h3></div>
  <div class="panel-body table-wrap">
    <table class="data-table">
      <thead><tr><th>Cover</th><th>Judul</th><th>Status</th><th>Tanggal</th><th></th></tr></thead>
      <tbody>
        <?php if (!$articles): ?><tr><td colspan="5">Belum ada artikel.</td></tr><?php endif; ?>
        <?php foreach ($articles as $a): ?>
        <tr>
          <td><?php if ($a['gambar_cover']): ?><img src="<?= UPLOAD_URL . '/' . e($a['gambar_cover']) ?>" class="thumb-sm"><?php else: ?>📰<?php endif; ?></td>
          <td><?= e($a['judul']) ?></td>
          <td><span class="status-pill <?= $a['is_published'] ? 'status-ready' : 'status-pending_payment' ?>"><?= $a['is_published'] ? 'Published' : 'Draft' ?></span></td>
          <td><?= $a['published_at'] ? date('d/m/Y', strtotime($a['published_at'])) : '-' ?></td>
          <td>
            <div class="table-actions">
              <a href="?edit=<?= $a['id'] ?>" class="icon-btn edit" title="Edit">✏️</a>
              <form method="post" data-confirm="Hapus artikel '<?= e($a['judul']) ?>'?">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                <button type="submit" name="delete_article" value="1" class="icon-btn danger" title="Hapus">🗑️</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
(function () {
  var toolbar = document.getElementById('editorToolbar');
  var area = document.getElementById('editorArea');
  var hidden = document.getElementById('editorHidden');
  var form = document.getElementById('articleForm');
  if (!toolbar || !area) return;

  toolbar.querySelectorAll('[data-cmd]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      area.focus();
      document.execCommand(btn.getAttribute('data-cmd'), false, btn.getAttribute('data-value') || null);
    });
  });
  document.getElementById('editorLinkBtn').addEventListener('click', function () {
    var url = window.prompt('Masukkan URL link:');
    if (url) { area.focus(); document.execCommand('createLink', false, url); }
  });
  form.addEventListener('submit', function () {
    hidden.value = area.innerHTML;
  });
})();
</script>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
