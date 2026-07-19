<?php
$pageTitle = 'Produk';
$activeMenu = 'produk';
require __DIR__ . '/includes/admin-header.php';

// ---- Handle add category ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category']) && csrf_check()) {
    $nama = trim($_POST['category_nama'] ?? '');
    if ($nama !== '') {
        db()->prepare('INSERT INTO product_categories (nama) VALUES (?)')->execute([$nama]);
        flash('success', 'Kategori berhasil ditambahkan.');
    }
    redirect(BASE_URL . '/admin/produk.php');
}

// ---- Handle save product (add/edit) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product']) && csrf_check()) {
    $id = (int)($_POST['id'] ?? 0);
    $nama = trim($_POST['nama'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $harga = (float)($_POST['harga'] ?? 0);
    $categoryId = (int)($_POST['category_id'] ?? 0) ?: null;
    $isAvailable = isset($_POST['is_available']) ? 1 : 0;

    try {
        $foto = null;
        if (!empty($_FILES['foto']['name'])) {
            $foto = upload_image($_FILES['foto'], 'products');
        }

        if ($nama === '' || $harga <= 0) {
            flash('error', 'Nama dan harga produk wajib diisi dengan benar.');
        } elseif ($id > 0) {
            if ($foto) {
                db()->prepare('UPDATE products SET category_id=?, nama=?, deskripsi=?, harga=?, foto=?, is_available=? WHERE id=?')
                    ->execute([$categoryId, $nama, $deskripsi, $harga, $foto, $isAvailable, $id]);
            } else {
                db()->prepare('UPDATE products SET category_id=?, nama=?, deskripsi=?, harga=?, is_available=? WHERE id=?')
                    ->execute([$categoryId, $nama, $deskripsi, $harga, $isAvailable, $id]);
            }
            flash('success', 'Produk berhasil diperbarui.');
        } else {
            db()->prepare('INSERT INTO products (category_id, nama, deskripsi, harga, foto, is_available) VALUES (?,?,?,?,?,?)')
                ->execute([$categoryId, $nama, $deskripsi, $harga, $foto ?? '', $isAvailable]);
            flash('success', 'Produk berhasil ditambahkan.');
        }
    } catch (RuntimeException $e) {
        flash('error', $e->getMessage());
    }
    redirect(BASE_URL . '/admin/produk.php');
}

// ---- Handle delete ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product']) && csrf_check()) {
    db()->prepare('DELETE FROM products WHERE id = ?')->execute([(int)$_POST['id']]);
    flash('success', 'Produk berhasil dihapus.');
    redirect(BASE_URL . '/admin/produk.php');
}

$categories = db()->query('SELECT * FROM product_categories ORDER BY nama')->fetchAll();
$products = db()->query('SELECT p.*, c.nama AS category_nama FROM products p LEFT JOIN product_categories c ON c.id = p.category_id ORDER BY p.id DESC')->fetchAll();

$editProduct = null;
if (!empty($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $editProduct = $stmt->fetch();
}
?>

<div class="panel">
  <div class="panel-head">
    <h3><?= $editProduct ? 'Edit Produk' : 'Tambah Produk Baru' ?></h3>
    <?php if ($editProduct): ?><a href="<?= BASE_URL ?>/admin/produk.php" class="btn btn-outline btn-sm">Batal Edit</a><?php endif; ?>
  </div>
  <div class="panel-body">
    <form method="post" enctype="multipart/form-data" class="admin-form">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <?php if ($editProduct): ?><input type="hidden" name="id" value="<?= $editProduct['id'] ?>"><?php endif; ?>

      <div class="form-row cols-2">
        <div class="form-group">
          <label>Nama Produk</label>
          <input type="text" name="nama" class="form-control" required value="<?= e($editProduct['nama'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Kategori</label>
          <select name="category_id" class="form-control">
            <option value="">— Tanpa kategori —</option>
            <?php foreach ($categories as $c): ?>
            <option value="<?= $c['id'] ?>" <?= (($editProduct['category_id'] ?? null) == $c['id']) ? 'selected' : '' ?>><?= e($c['nama']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label>Deskripsi</label>
        <textarea name="deskripsi" class="form-control"><?= e($editProduct['deskripsi'] ?? '') ?></textarea>
      </div>

      <div class="form-row cols-2">
        <div class="form-group">
          <label>Harga (Rp)</label>
          <input type="number" name="harga" min="0" step="500" class="form-control" required value="<?= e((string)($editProduct['harga'] ?? '')) ?>">
        </div>
        <div class="form-group">
          <label>Foto Produk</label>
          <input type="file" name="foto" accept="image/png,image/jpeg,image/webp" class="form-control" data-image-input="fotoPreview">
          <div class="form-hint">Max 2MB. JPG/PNG/WEBP.</div>
        </div>
      </div>
      <div class="image-preview" id="fotoPreview">
        <?php if (!empty($editProduct['foto'])): ?><img src="<?= UPLOAD_URL . '/' . e($editProduct['foto']) ?>" alt=""><?php else: ?>Tidak ada foto<?php endif; ?>
      </div>

      <div class="form-group">
        <label><input type="checkbox" name="is_available" <?= ($editProduct['is_available'] ?? 1) ? 'checked' : '' ?>> Tersedia untuk dijual</label>
      </div>

      <button type="submit" name="save_product" value="1" class="btn btn-primary"><?= $editProduct ? 'Simpan Perubahan' : 'Tambah Produk' ?></button>
    </form>

    <details style="margin-top:18px;">
      <summary style="cursor:pointer; font-weight:600; font-size:13.5px; color:var(--red-600);">+ Tambah kategori baru</summary>
      <form method="post" style="display:flex; gap:8px; margin-top:12px;">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="text" name="category_nama" class="form-control" placeholder="Nama kategori" required>
        <button type="submit" name="add_category" value="1" class="btn btn-outline btn-sm">Tambah</button>
      </form>
    </details>
  </div>
</div>

<div class="panel">
  <div class="panel-head"><h3>Daftar Produk (<?= count($products) ?>)</h3></div>
  <div class="panel-body table-wrap">
    <table class="data-table">
      <thead><tr><th>Foto</th><th>Nama</th><th>Kategori</th><th>Harga</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php if (!$products): ?><tr><td colspan="6">Belum ada produk.</td></tr><?php endif; ?>
        <?php foreach ($products as $p): ?>
        <tr>
          <td><?php if ($p['foto']): ?><img src="<?= UPLOAD_URL . '/' . e($p['foto']) ?>" class="thumb-sm"><?php else: ?>🥟<?php endif; ?></td>
          <td><?= e($p['nama']) ?></td>
          <td><?= e($p['category_nama'] ?? '-') ?></td>
          <td><?= rupiah($p['harga']) ?></td>
          <td><span class="status-pill <?= $p['is_available'] ? 'status-ready' : 'status-cancelled' ?>"><?= $p['is_available'] ? 'Tersedia' : 'Habis' ?></span></td>
          <td>
            <div class="table-actions">
              <a href="?edit=<?= $p['id'] ?>" class="icon-btn edit" title="Edit">✏️</a>
              <form method="post" data-confirm="Hapus produk '<?= e($p['nama']) ?>'?">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button type="submit" name="delete_product" value="1" class="icon-btn danger" title="Hapus">🗑️</button>
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
