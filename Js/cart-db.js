// ============================================================
// cart-db.js — Cart with DB sync for logged-in users
// Place in: clothes(website)/Js/cart-db.js
// Add to every page: <script src="Js/cart-db.js"></script>
// ============================================================

const CART_API = 'http://localhost/clothes(website)/api/cart-sync.php';

// ── Check if user is logged in ──
async function isLoggedIn() {
  try {
    const res  = await fetch('http://localhost/clothes(website)/api/check-login.php', { credentials: 'include' });
    const data = await res.json();
    return data.logged_in;
  } catch(e) { return false; }
}

// ── Get cart (DB if logged in, localStorage if guest) ──
async function getCartItems() {
  const loggedIn = await isLoggedIn();
  if (loggedIn) {
    try {
      const fd = new FormData(); fd.append('action', 'get');
      const res  = await fetch(CART_API, { method: 'POST', credentials: 'include', body: fd });
      const data = await res.json();
      if (data.success) {
        // Convert DB format to localStorage format
        return data.items.map(i => ({
          id:       'db_' + i.product_id,
          name:     i.product_name,
          price:    parseFloat(i.price),
          img:      i.image_url,
          qty:      parseInt(i.quantity),
          size:     i.size,
          cart_item_id: i.cart_item_id
        }));
      }
    } catch(e) {}
  }
  // Fallback to localStorage
  try { return JSON.parse(localStorage.getItem('cart')) || []; } catch(e) { return []; }
}

// ── Add to cart ──
async function addToCartDB(name, price, image, productId, size, quantity) {
  size     = size     || 'M';
  quantity = quantity || 1;

  const loggedIn = await isLoggedIn();

  if (loggedIn) {
    const fd = new FormData();
    fd.append('action',       'add');
    fd.append('product_id',   productId || '');
    fd.append('product_name', name);
    fd.append('price',        price);
    fd.append('image_url',    image || '');
    fd.append('size',         size);
    fd.append('quantity',     quantity);
    try {
      const res  = await fetch(CART_API, { method: 'POST', credentials: 'include', body: fd });
      const data = await res.json();
      if (data.success) { updateCartBadge(); return true; }
    } catch(e) {}
  }

  // Fallback localStorage
  let cart = [];
  try { cart = JSON.parse(localStorage.getItem('cart')) || []; } catch(e) {}
  const existing = cart.find(i => i.name === name && i.size === size);
  if (existing) { existing.qty = (existing.qty || 1) + quantity; }
  else { cart.push({ id: productId || ('item-' + Date.now()), name, price, img: image, qty: quantity, size }); }
  localStorage.setItem('cart', JSON.stringify(cart));
  updateCartBadge();
  return true;
}

// ── Remove item ──
async function removeFromCartDB(cartItemId, itemName) {
  const loggedIn = await isLoggedIn();
  if (loggedIn && cartItemId) {
    const fd = new FormData();
    fd.append('action', 'remove');
    fd.append('cart_item_id', cartItemId);
    await fetch(CART_API, { method: 'POST', credentials: 'include', body: fd });
  } else {
    // localStorage remove
    let cart = [];
    try { cart = JSON.parse(localStorage.getItem('cart')) || []; } catch(e) {}
    cart = cart.filter(i => i.name !== itemName);
    localStorage.setItem('cart', JSON.stringify(cart));
  }
  updateCartBadge();
}

// ── Clear cart ──
async function clearCartDB() {
  const loggedIn = await isLoggedIn();
  if (loggedIn) {
    const fd = new FormData(); fd.append('action', 'clear');
    await fetch(CART_API, { method: 'POST', credentials: 'include', body: fd });
  }
  localStorage.removeItem('cart');
  updateCartBadge();
}

// ── Sync localStorage → DB on login ──
async function syncCartToDB() {
  const loggedIn = await isLoggedIn();
  if (!loggedIn) return;
  let localCart = [];
  try { localCart = JSON.parse(localStorage.getItem('cart')) || []; } catch(e) {}
  if (localCart.length === 0) return;

  await fetch(CART_API, {
    method: 'POST',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'sync', items: localCart })
  });
  localStorage.removeItem('cart'); // clear localStorage after sync
  updateCartBadge();
}

// ── Update cart badge count ──
async function updateCartBadge() {
  const items = await getCartItems();
  const total = items.reduce((s, i) => s + (parseInt(i.qty || i.quantity) || 1), 0);
  const badge = document.getElementById('cart-count');
  if (badge) {
    badge.innerText = total;
    badge.style.display = total > 0 ? 'inline-block' : 'none';
  }
}

// ── Run on page load ──
document.addEventListener('DOMContentLoaded', async function() {
  await syncCartToDB(); // sync any localStorage items to DB
  await updateCartBadge();
});