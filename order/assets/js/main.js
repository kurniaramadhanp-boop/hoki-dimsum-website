document.addEventListener('DOMContentLoaded', function () {
  // ---- Mobile drawer ----
  var drawer = document.getElementById('mobileDrawer');
  var menuToggle = document.getElementById('menuToggle');
  var drawerClose = document.getElementById('drawerClose');
  var drawerOverlay = document.getElementById('drawerOverlay');

  function openDrawer() { drawer && drawer.classList.add('open'); document.body.style.overflow = 'hidden'; }
  function closeDrawer() { drawer && drawer.classList.remove('open'); document.body.style.overflow = ''; }

  menuToggle && menuToggle.addEventListener('click', openDrawer);
  drawerClose && drawerClose.addEventListener('click', closeDrawer);
  drawerOverlay && drawerOverlay.addEventListener('click', closeDrawer);

  // ---- Category filter chips (menu.php) ----
  var chips = document.querySelectorAll('[data-filter-chip]');
  var productCards = document.querySelectorAll('[data-category]');
  chips.forEach(function (chip) {
    chip.addEventListener('click', function () {
      chips.forEach(function (c) { c.classList.remove('active'); });
      chip.classList.add('active');
      var cat = chip.getAttribute('data-filter-chip');
      productCards.forEach(function (card) {
        var show = cat === 'all' || card.getAttribute('data-category') === cat;
        card.style.display = show ? '' : 'none';
      });
    });
  });

  // ---- Cart: add to cart via fetch ----
  function formatRupiah(n) {
    return 'Rp' + Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

  function updateCartBadge(count) {
    var badge = document.getElementById('cartBadge');
    if (count > 0) {
      if (!badge) {
        var cartBtn = document.querySelector('.cart-btn');
        if (cartBtn) {
          badge = document.createElement('span');
          badge.className = 'cart-badge';
          badge.id = 'cartBadge';
          cartBtn.appendChild(badge);
        }
      }
      if (badge) badge.textContent = count;
    } else if (badge) {
      badge.remove();
    }
  }

  function updateStickyBar(count, total) {
    var stickyCount = document.getElementById('stickyCartCount');
    if (stickyCount) stickyCount.textContent = count + ' item';
    var stickyTotal = document.getElementById('stickyCartTotal');
    if (stickyTotal && typeof total === 'number') stickyTotal.textContent = formatRupiah(total);
    var stickyBar = document.getElementById('stickyCartbar');
    if (stickyBar) stickyBar.classList.toggle('show', count > 0);
  }

  function postCart(action, productId, qty) {
    var body = new URLSearchParams();
    body.set('action', action);
    body.set('product_id', productId);
    body.set('qty', qty);
    return fetch(window.APP_BASE_URL + '/cart-actions.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
      body: body.toString(),
    }).then(function (r) { return r.json(); });
  }

  // ---- Unified per-product qty control: tap "+" to add, then adjust directly in cart ----
  document.querySelectorAll('.qty-control').forEach(function (wrap) {
    var productId = wrap.getAttribute('data-product-id');
    var unitPrice = parseFloat(wrap.getAttribute('data-price')) || 0;
    var cardBody = wrap.closest('.product-body');
    var lineTotalEl = cardBody ? cardBody.querySelector('[data-line-total]') : null;
    var busy = false;

    function setQty(qty) {
      wrap.setAttribute('data-qty', qty);
      wrap.classList.toggle('active', qty > 0);
      var qtyEl = wrap.querySelector('[data-qty-value]');
      if (qtyEl) qtyEl.textContent = qty > 0 ? qty : 1;
      if (lineTotalEl) {
        if (qty > 0) {
          lineTotalEl.textContent = 'Subtotal: ' + formatRupiah(unitPrice * qty);
          lineTotalEl.style.display = '';
        } else {
          lineTotalEl.style.display = 'none';
        }
      }
    }

    function sync(action, qty, nextQty) {
      if (busy) return;
      busy = true;
      postCart(action, productId, qty).then(function (res) {
        if (res.ok) {
          setQty(nextQty);
          updateCartBadge(res.cart_count);
          updateStickyBar(res.cart_count, res.cart_total);
        }
      }).finally(function () { busy = false; });
    }

    var addBtn = wrap.querySelector('[data-cart-init]');
    addBtn && addBtn.addEventListener('click', function () {
      sync('add', 1, 1);
    });

    var plusBtn = wrap.querySelector('[data-cart-plus]');
    plusBtn && plusBtn.addEventListener('click', function () {
      var next = parseInt(wrap.getAttribute('data-qty'), 10) + 1;
      sync('update', next, next);
    });

    var minusBtn = wrap.querySelector('[data-cart-minus]');
    minusBtn && minusBtn.addEventListener('click', function () {
      var next = parseInt(wrap.getAttribute('data-qty'), 10) - 1;
      if (next <= 0) {
        sync('remove', 0, 0);
      } else {
        sync('update', next, next);
      }
    });
  });

  // ---- Cart page line qty update / remove ----
  document.querySelectorAll('[data-cart-qty]').forEach(function (input) {
    input.addEventListener('change', function () {
      var id = input.getAttribute('data-cart-qty');
      var qty = Math.max(1, parseInt(input.value, 10) || 1);
      postCart('update', id, qty).then(function () { window.location.reload(); });
    });
  });
  document.querySelectorAll('[data-cart-remove]').forEach(function (link) {
    link.addEventListener('click', function (e) {
      e.preventDefault();
      var id = link.getAttribute('data-cart-remove');
      postCart('remove', id, 0).then(function () { window.location.reload(); });
    });
  });

  // ---- Radio card selection style (checkout) ----
  document.querySelectorAll('.radio-card input[type=radio]').forEach(function (input) {
    input.addEventListener('change', function () {
      var name = input.name;
      document.querySelectorAll('input[name="' + name + '"]').forEach(function (i) {
        i.closest('.radio-card').classList.toggle('selected', i.checked);
      });
    });
  });
});
