<?php
$pageTitle = 'Promo';
$activeMenu = 'promo';
require __DIR__ . '/includes/admin-header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_promo']) && csrf_check()) {
    $id = (int)($_POST['id'] ?? 0);
    $judul = trim($_POST['judul'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $mulai = $_POST['tanggal_mulai'] ?: null;
    $selesai = $_POST['tanggal_selesai'] ?: null;
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    try {
        $gambar = null;
        if (!empty($_FILES['gambar']['name'])) {
            $gambar = upload_image($_FILES['gambar'], 'promotions');
        }
        if ($judul === '') {
            flash('error', 'Judul promo wajib diisi.');
        } elseif ($id > 0) {
            if ($gambar) {
                db()->prepare('UPDATE promotions SET judul=?, deskripsi=?, gambar=?, tanggal_mulai=?, tanggal_selesai=?, is_active=? WHERE id=?')
                    ->execute([$judul, $deskripsi, $gambar, $mulai, $selesai, $isActive, $id]);
            } else {
                db()->prepare('UPDATE promotions SET judul=?, deskripsi=?, tanggal_mulai=?, tanggal_selesai=?, is_active=? WHERE id=?')
                    ->execute([$judul, $deskripsi, $mulai, $selesai, $isActive, $id]);
            }
            flash('success', 'Promo berhasil diperbarui.');
        } else {
            db()->prepare('INSERT INTO promotions (judul, deskripsi, gambar, tanggal_mulai, tanggal_selesai, is_active) VALUES (?,?,?,?,?,?)')
                ->execute([$judul, $deskripsi, $gambar ?? '', $mulai, $selesai, $isActive]);
            flash('success', 'Promo berhasil ditambahkan.');
        }
    } catch (RuntimeException $e) {
        flash('error', $e->getMessage());
    }
    redirect(BASE_URL . '/admin/promo.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_promo']) && csrf_check()) {
    db()->prepare('DELETE FROM promotions WHERE id = ?')->execute([(int)$_POST['id']]);
    flash('success', 'Promo berhasil dihapus.');
    redirect(BASE_URL . '/admin/promo.php');
}

$promos = db()->query('SELECT * FROM promotions ORDER BY id DESC')->fetchAll();
$editPromo = null;
if (!empty($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM promotions WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $editPromo = $stmt->fetch();
}
?>

<div class="panel">
  <div class="panel-head">
    <h3><?= $editPromo ? 'Edit Promo' : 'Tambah Promo Baru' ?></h3>
    <?php if ($editPromo): ?><a href="<?= BASE_URL ?>/admin/promo.php" class="btn btn-outline btn-sm">Batal Edit</a><?php endif; ?>
  </div>
  <div class="panel-body">
    <form method="post" enctype="multipart/form-data" class="admin-form">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <?php if ($editPromo): ?><input type="hidden" name="id" value="<?= $editPromo['id'] ?>"><?php endif; ?>

      <div class="form-group">
        <label>Judul Promo</label>
        <input type="text" name="judul" class="form-control" required value="<?= e($editPromo['judul'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Deskripsi</label>
        <textarea name="deskripsi" class="form-control"><?= e($editPromo['deskripsi'] ?? '') ?></textarea>
      </div>
      <div class="form-row cols-2">
        <div class="form-group">
          <label>Tanggal Mulai</label>
          <input type="date" name="tanggal_mulai" class="form-control" value="<?= e($editPromo['tanggal_mulai'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Tanggal Selesai</label>
          <input type="date" name="tanggal_selesai" class="form-control" value="<?= e($editPromo['tanggal_selesai'] ?? '') ?>">
        </div>
      </div>
      <div class="form-group">
        <label>Gambar Promo</label>
        <input type="file" name="gambar" accept="image/png,image/jpeg,image/webp" class="form-control" data-image-input="gambarPreview">
      </div>
      <div class="image-preview" id="gambarPreview">
        <?php if (!empty($editPromo['gambar'])): ?><img src="<?= UPLOAD_URL . '/' . e($editPromo['gambar']) ?>" alt=""><?php else: ?>Tidak ada gambar<?php endif; ?>
      </div>
      <div class="form-group">
        <label><input type="checkbox" name="is_active" <?= ($editPromo['is_active'] ?? 1) ? 'checked' : '' ?>> Aktif</label>
      </div>
      <button type="submit" name="save_promo" value="1" class="btn btn-primary"><?= $editPromo ? 'Simpan Perubahan' : 'Tambah Promo' ?></button>
    </form>
  </div>
</div>

<div class="panel">
  <div class="panel-head"><h3>Daftar Promo (<?= count($promos) ?>)</h3></div>
  <div class="panel-body table-wrap">
    <table class="data-table">
      <thead><tr><th>Gambar</th><th>Judul</th><th>Periode</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php if (!$promos): ?><tr><td colspan="5">Belum ada promo.</td></tr><?php endif; ?>
        <?php foreach ($promos as $p): ?>
        <tr>
          <td><?php if ($p['gambar']): ?><img src="<?= UPLOAD_URL . '/' . e($p['gambar']) ?>" class="thumb-sm"><?php else: ?>🏷️<?php endif; ?></td>
          <td><?= e($p['judul']) ?></td>
          <td><?= $p['tanggal_mulai'] ? date('d/m/Y', strtotime($p['tanggal_mulai'])) : '-' ?> — <?= $p['tanggal_selesai'] ? date('d/m/Y', strtotime($p['tanggal_selesai'])) : '-' ?></td>
          <td><span class="status-pill <?= $p['is_active'] ? 'status-ready' : 'status-cancelled' ?>"><?= $p['is_active'] ? 'Aktif' : 'Nonaktif' ?></span></td>
          <td>
            <div class="table-actions">
              <a href="?edit=<?= $p['id'] ?>" class="icon-btn edit" title="Edit">✏️</a>
              <form method="post" data-confirm="Hapus promo '<?= e($p['judul']) ?>'?">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button type="submit" name="delete_promo" value="1" class="icon-btn danger" title="Hapus">🗑️</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
