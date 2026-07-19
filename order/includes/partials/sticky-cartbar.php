<div class="sticky-cartbar <?= cart_count() > 0 ? 'show' : '' ?>" id="stickyCartbar">
  <div class="info">
    <b id="stickyCartTotal"><?= rupiah(cart_total()) ?></b>
    <span id="stickyCartCount"><?= cart_count() ?> item</span>
  </div>
  <a href="<?= BASE_URL ?>/checkout.php" class="btn btn-primary btn-sm">Checkout →</a>
</div>
