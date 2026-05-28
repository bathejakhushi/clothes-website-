// =====================================================
// Js/cart.js — with STOCK support
// =====================================================

const CART_KEY = "cart";

function getCart() {
  try { return JSON.parse(localStorage.getItem(CART_KEY)) || []; }
  catch(e) { return []; }
}

function saveCart(cart) {
  localStorage.setItem(CART_KEY, JSON.stringify(cart));
}

// ── ADD TO CART ───────────────────────────────────────
// ✅ stock param added — saves max stock per item
function addToCart(id, name, price, image, size, stock) {
  const cart = getCart();
  const existing = cart.find(item =>
    String(item.id) === String(id) && item.size === (size || "")
  );
  const maxStock = parseInt(stock) || 999;

  if (existing) {
    // ✅ Don't exceed stock
    if (existing.quantity < maxStock) {
      existing.quantity += 1;
    } else {
      showStockToast(name, maxStock);
      return;
    }
  } else {
    cart.push({
      id:       String(id),
      name:     name,
      price:    Number(price),
      image:    image || "",
      quantity: 1,
      size:     size || "",
      stock:    maxStock   // ✅ save stock limit
    });
  }
  saveCart(cart);
  showCartToast(name);
  updateAllCartCounts();
}

// ── BUY NOW ───────────────────────────────────────────
function buyNow(id, name, price, image, size, stock) {
  addToCart(id, name, price, image, size, stock);
  window.location.href = "checkout.html";
}

function buyNowItem(id, name, price, image, size, stock) {
  addToCart(id, name, price, image, size, stock);
  window.location.href = "checkout.html";
}

// ── REMOVE FROM CART ─────────────────────────────────
function removeFromCart(id) {
  const cart = getCart().filter(item => String(item.id) !== String(id));
  saveCart(cart);
  updateAllCartCounts();
  if (typeof loadCart === 'function') loadCart();
}

// ── UPDATE QUANTITY ───────────────────────────────────
function updateQty(id, qty) {
  const cart = getCart();
  const item = cart.find(i => String(i.id) === String(id));
  if (!item) return;
  const maxStock = parseInt(item.stock) || 999;
  item.quantity = Math.min(maxStock, Math.max(1, Number(qty)));
  saveCart(cart);
  updateAllCartCounts();
  if (typeof loadCart === 'function') loadCart();
}

// ── CLEAR CART ────────────────────────────────────────
function clearCart() {
  saveCart([]);
  updateAllCartCounts();
  if (typeof loadCart === 'function') loadCart();
}

// ── UPDATE CART COUNT BADGE ───────────────────────────
function updateAllCartCounts() {
  const cart = getCart();
  const total = cart.reduce((s, i) => s + (parseInt(i.quantity) || 1), 0);
  ['cart-count', 'cartCount', 'cart_count', 'ua-cart-badge'].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      el.innerText = total;
      el.style.display = total > 0 ? 'inline-block' : 'none';
    }
  });
}

// ── TOAST: added to cart ─────────────────────────────
function showCartToast(name) {
  const old = document.getElementById('cartToast');
  if (old) old.remove();
  const toast = document.createElement('div');
  toast.id = 'cartToast';
  toast.style.cssText = `position:fixed;bottom:24px;right:24px;z-index:9999;background:#111;color:#fff;padding:12px 20px;border-radius:10px;font-size:13px;font-weight:600;box-shadow:0 4px 20px rgba(0,0,0,0.3);animation:slideUp 0.3s ease;display:flex;align-items:center;gap:8px;`;
  toast.innerHTML = `✅ <span>${name}</span> added to cart!`;
  _addToastStyle();
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 2500);
}

// ── TOAST: stock limit hit ────────────────────────────
function showStockToast(name, stock) {
  const old = document.getElementById('cartToast');
  if (old) old.remove();
  const toast = document.createElement('div');
  toast.id = 'cartToast';
  toast.style.cssText = `position:fixed;bottom:24px;right:24px;z-index:9999;background:#c0392b;color:#fff;padding:12px 20px;border-radius:10px;font-size:13px;font-weight:600;box-shadow:0 4px 20px rgba(0,0,0,0.3);animation:slideUp 0.3s ease;display:flex;align-items:center;gap:8px;`;
  toast.innerHTML = `⚠️ Only ${stock} in stock for <span>${name}</span>!`;
  _addToastStyle();
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 3000);
}

function _addToastStyle() {
  if (!document.getElementById('toastStyle')) {
    const style = document.createElement('style');
    style.id = 'toastStyle';
    style.textContent = `@keyframes slideUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}`;
    document.head.appendChild(style);
  }
}

// ── AUTO-RUN ──────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
  updateAllCartCounts();
});