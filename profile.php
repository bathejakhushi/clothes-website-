<?php
session_start();
include 'api/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

$user_id = $_SESSION['user_id'];

$user = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$user->bind_param("i", $user_id);
$user->execute();
$u = $user->get_result()->fetch_assoc();

$orders = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$orders->bind_param("i", $user_id);
$orders->execute();
$order_result = $orders->get_result();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Profile — Urban Aura</title>
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.2.0/fonts/remixicon.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <style>
    body { font-family: 'DM Sans', sans-serif; background: #f8f6f3; color: #1a1a1a; }
    .cart-icon { position: relative; font-size: 22px; color: #000; text-decoration: none; }
    .cart-badge { position: absolute; top: -8px; right: -10px; background: #c0392b; color: #fff; font-size: 11px; font-weight: 700; padding: 2px 6px; border-radius: 50%; display: none; }
    .profile-hero { background: linear-gradient(135deg, #1a1a1a, #2d2d2d); padding: 72px 40px 44px; text-align: center; }
    .profile-hero h1 { font-family: 'Playfair Display', serif; color: #fff; font-size: 36px; margin: 0; }
    .profile-hero p { color: #aaa; margin-top: 8px; font-size: 13px; }
    .profile-layout { max-width: 900px; margin: 40px auto; padding: 0 24px 80px; }

    /* ✅ FIXED: Column layout, centered */
    .user-card {
      background: #fff;
      border-radius: 16px;
      padding: 32px 28px;
      box-shadow: 0 2px 12px rgba(0,0,0,0.07);
      margin-bottom: 24px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 12px;
      text-align: center;
    }
    .avatar { width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #1a1a1a, #555); display: flex; align-items: center; justify-content: center; font-size: 32px; color: #fff; font-weight: 700; flex-shrink: 0; }
    .user-info h3 { font-size: 22px; font-weight: 700; margin: 0 0 4px; }
    .user-info p { color: #888; font-size: 13px; margin: 2px 0; }

    /* ✅ FIXED: Logout button centered, no margin-left auto */
    .logout-btn {
      margin-top: 8px;
      background: #1a1a1a;
      color: #fff;
      border: none;
      padding: 10px 28px;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
    }
    .logout-btn:hover { background: #c0392b; color: #fff; }

    .section-title { font-family: 'Playfair Display', serif; font-size: 22px; font-weight: 700; margin-bottom: 16px; }
    .order-card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); margin-bottom: 14px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; }
    .order-id { font-size: 13px; font-weight: 700; color: #555; }
    .order-meta { font-size: 12px; color: #999; margin-top: 3px; }
    .order-amount { font-family: 'Playfair Display', serif; font-size: 22px; font-weight: 700; }
    .badge { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .badge-pending   { background: #fff3cd; color: #856404; }
    .badge-shipped   { background: #cff4fc; color: #055160; }
    .badge-delivered { background: #d1e7dd; color: #0a3622; }
    .badge-cancelled { background: #f8d7da; color: #842029; }
    .no-orders { text-align: center; color: #bbb; padding: 40px; font-style: italic; background: #fff; border-radius: 12px; }
  </style>
</head>
<body>
<div class="header__bar"></div>
<nav class="section__container nav__container">
  <a href="index.html" class="nav__logo">URBAN AURA FASHION</a>
  <ul class="nav__links">
    <li><a href="index.html">Home</a></li>
    <li><a href="products.html">Products</a></li>
    <li><a href="about.html">About</a></li>
    <li><a href="contact.html">Contact</a></li>
    <li><a href="logout.php" style="color:#c0392b;">Logout</a></li>
  </ul>
  <div class="nav__icons">
    <a href="cart.html" class="cart-icon">
      <i class="ri-shopping-bag-2-line"></i>
      <span id="cart-count" class="cart-badge"></span>
    </a>
  </div>
</nav>

<div class="profile-hero">
  <h1>My Profile</h1>
  <p>Manage your account and view your orders</p>
</div>

<div class="profile-layout">

  <div class="user-card">
    <div class="avatar"><?= strtoupper(substr($u['name'], 0, 1)) ?></div>
    <div class="user-info">
      <h3><?= htmlspecialchars($u['name']) ?></h3>
      <p><i class="ri-mail-line"></i> <?= htmlspecialchars($u['email']) ?></p>
      <?php if (!empty($u['phone'] ?? null)): ?><p><i class="ri-phone-line"></i> <?= htmlspecialchars($u['phone']) ?></p><?php endif; ?>
      <?php if (!empty($u['address'] ?? null)): ?><p><i class="ri-map-pin-line"></i> <?= htmlspecialchars($u['address']) ?></p><?php endif; ?>
      <p style="color:#bbb;font-size:11px;">Member since <?= date('d M Y', strtotime($u['created_at'])) ?></p>
    </div>
    <a href="logout.php" class="logout-btn"><i class="ri-logout-box-line"></i> Logout</a>
  </div>

  <div class="section-title">My Orders</div>

  <?php if ($order_result->num_rows === 0): ?>
    <div class="no-orders">
      <i class="ri-shopping-bag-line" style="font-size:40px;display:block;margin-bottom:10px;"></i>
      No orders yet! <a href="products.html" style="color:#1a1a1a;font-weight:700;">Start shopping →</a>
    </div>
  <?php else: while ($o = $order_result->fetch_assoc()): ?>
    <div class="order-card">
      <div>
        <div class="order-id">Order #UA<?= str_pad($o['order_id'], 6, '0', STR_PAD_LEFT) ?></div>
        <div class="order-meta"><?= date('d M Y, h:i A', strtotime($o['order_date'])) ?> · <?= $o['payment_method'] ?></div>
        <div class="order-meta"><?= htmlspecialchars($o['address']) ?>, <?= htmlspecialchars($o['city']) ?></div>
      </div>
      <div style="text-align:right;">
        <div class="order-amount">₹<?= number_format($o['total_amount'], 0) ?></div>
        <span class="badge badge-<?= strtolower($o['order_status']) ?>"><?= $o['order_status'] ?></span>
      </div>
    </div>
  <?php endwhile; endif; ?>

</div>

<script>
  try {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    let t = cart.reduce((s, i) => s + (parseInt(i.quantity || i.qty) || 1), 0);
    let b = document.getElementById('cart-count');
    if (t > 0) { b.innerText = t; b.style.display = 'inline-block'; }
  } catch(e) {}
</script>
<script src="Js/cart.js"></script>
</body>
</html>