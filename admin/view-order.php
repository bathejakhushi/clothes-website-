<?php
session_start();
include '../api/db.php';

$order_id = intval($_GET['id'] ?? 0);
if ($order_id <= 0) {
    die("<p style='color:red;text-align:center;margin-top:50px;'>Invalid Order ID.</p>");
}

$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("<p style='color:red;text-align:center;margin-top:50px;'>Order not found.</p>");
}

$items_stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items = $items_stmt->get_result();

$order_status = $order['order_status'] ?? $order['status'] ?? 'pending';
$order_date   = $order['order_date']   ?? $order['created_at'] ?? '';

function statusColor($status) {
    return match($status) {
        'pending'   => '#f39c12',
        'confirmed' => '#3498db',
        'shipped'   => '#8e44ad',
        'delivered' => '#27ae60',
        'cancelled' => '#e74c3c',
        default     => '#999'
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Order #<?= $order_id ?> — Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.2.0/fonts/remixicon.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'DM Sans', sans-serif; background: #f4f6f9; color: #1a1a1a; min-height: 100vh; }
    .topbar { background: #1a1a2e; color: #fff; padding: 14px 30px; display: flex; align-items: center; justify-content: space-between; }
    .topbar .logo { font-family: 'Playfair Display', serif; font-size: 20px; color: #e8b86d; text-decoration: none; }
    .topbar .back-btn { background: rgba(255,255,255,0.1); color: #fff; border: none; padding: 8px 18px; border-radius: 8px; font-size: 13px; cursor: pointer; text-decoration: none; display: flex; align-items: center; gap: 6px; }
    .topbar .back-btn:hover { background: rgba(255,255,255,0.2); }
    .page-header { background: #fff; border-bottom: 1px solid #e8ecf0; padding: 24px 30px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
    .page-header h1 { font-family: 'Playfair Display', serif; font-size: 24px; color: #1a1a2e; }
    .status-badge { padding: 6px 16px; border-radius: 20px; color: #fff; font-size: 13px; font-weight: 600; text-transform: capitalize; }
    .content { max-width: 1100px; margin: 30px auto; padding: 0 20px 60px; display: grid; grid-template-columns: 1fr 340px; gap: 24px; }
    @media (max-width: 800px) { .content { grid-template-columns: 1fr; } }
    .card { background: #fff; border-radius: 14px; padding: 24px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); margin-bottom: 20px; }
    .card-title { font-size: 15px; font-weight: 700; color: #1a1a2e; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1.5px solid #f0f2f5; display: flex; align-items: center; gap: 8px; }
    .card-title i { color: #e8b86d; font-size: 17px; }
    .info-row { display: flex; justify-content: space-between; padding: 9px 0; border-bottom: 1px solid #f8f9fa; font-size: 14px; }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #888; font-weight: 500; }
    .info-value { font-weight: 600; color: #1a1a1a; text-align: right; }
    .items-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .items-table th { background: #f8f9fa; padding: 10px 14px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: #888; font-weight: 600; }
    .items-table td { padding: 12px 14px; border-bottom: 1px solid #f0f2f5; vertical-align: middle; }
    .items-table tr:last-child td { border-bottom: none; }
    .item-img { width: 48px; height: 52px; border-radius: 8px; object-fit: cover; }
    .item-name { font-weight: 600; font-size: 14px; }
    .null-badge { background: #fee; color: #c0392b; font-size: 11px; padding: 2px 7px; border-radius: 4px; font-weight: 600; }
    .id-badge { background: #e8f5e9; color: #2e7d32; font-size: 11px; padding: 2px 7px; border-radius: 4px; font-weight: 600; }
    .size-pill { display: inline-block; background: #f0ede8; border: 1px solid #ddd8d0; border-radius: 5px; padding: 2px 9px; font-size: 12px; font-weight: 700; color: #555; }
    /* ✅ Subcategory badge */
    .sub-badge { display: inline-block; background: #e8f0fe; border: 1px solid #c5d4f7; border-radius: 5px; padding: 2px 9px; font-size: 11px; font-weight: 600; color: #3a5bbf; }
    .status-form { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-top: 4px; }
    .status-select { flex: 1; padding: 10px 12px; border: 1.5px solid #e0dbd4; border-radius: 8px; font-family: 'DM Sans', sans-serif; font-size: 14px; background: #faf9f7; outline: none; cursor: pointer; }
    .update-btn { background: #1a1a2e; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; white-space: nowrap; }
    .update-btn:hover { background: #2d2d5e; }
    .delete-btn { background: #e74c3c; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; white-space: nowrap; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
    .delete-btn:hover { background: #c0392b; }
    .success-msg { background: #e8f5e9; color: #2e7d32; padding: 10px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; margin-bottom: 14px; display: flex; align-items: center; gap: 7px; }
  </style>
</head>
<body>

<div class="topbar">
  <a href="adminorders.php" class="logo">URBAN AURA — Admin</a>
  <a href="adminorders.php" class="back-btn"><i class="ri-arrow-left-line"></i> Back to Orders</a>
</div>

<div class="page-header">
  <h1><i class="ri-file-list-3-line" style="color:#e8b86d;"></i> Order #UA<?= str_pad($order_id, 6, '0', STR_PAD_LEFT) ?></h1>
  <span class="status-badge" style="background:<?= statusColor($order_status) ?>">
    <?= ucfirst(htmlspecialchars($order_status)) ?>
  </span>
</div>

<div class="content">
  <div>
    <!-- ORDER ITEMS -->
    <div class="card">
      <div class="card-title"><i class="ri-shopping-bag-line"></i> Ordered Items</div>
      <table class="items-table">
        <thead>
          <tr>
            <th>Image</th>
            <th>Product</th>
            <th>Category</th>
            <th>Product ID</th>
            <th>Size</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($item = $items->fetch_assoc()): ?>
          <tr>
            <td>
              <?php
                // Try DB image first, then fallback emoji
                $imgSrc = '';
                if (!empty($item['image_url'])) {
                  $imgSrc = '../' . htmlspecialchars($item['image_url']);
                }
              ?>
              <?php if ($imgSrc): ?>
                <img src="<?= $imgSrc ?>" class="item-img"
                     onerror="this.outerHTML='<div style=\'width:48px;height:52px;background:#f0ede8;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:22px;\'>👕</div>'">
              <?php else: ?>
                <div style="width:48px;height:52px;background:#f0ede8;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:22px;">👕</div>
              <?php endif; ?>
            </td>
            <td>
              <div class="item-name"><?= htmlspecialchars($item['product_name']) ?></div>
            </td>
            <td>
              <?php
                $sub = trim($item['subcategory'] ?? '');
                if ($sub && $sub !== 'null' && $sub !== 'undefined'):
              ?>
                <span class="sub-badge"><?= htmlspecialchars($sub) ?></span>
              <?php else: ?>
                <span style="color:#bbb;font-size:12px;">—</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if (!empty($item['product_id'])): ?>
                <span class="id-badge">#<?= $item['product_id'] ?></span>
              <?php else: ?>
                <span class="null-badge">NULL</span>
              <?php endif; ?>
            </td>
            <td>
              <?php
                $sz = trim($item['size'] ?? '');
                if ($sz && $sz !== 'undefined' && $sz !== 'null'):
              ?>
                <span class="size-pill"><?= htmlspecialchars($sz) ?></span>
              <?php else: ?>
                <span style="color:#bbb;font-size:12px;">—</span>
              <?php endif; ?>
            </td>
            <td><?= intval($item['quantity']) ?></td>
            <td><strong>₹<?= number_format($item['price'], 2) ?></strong></td>
            <td><strong>₹<?= number_format($item['price'] * $item['quantity'], 2) ?></strong></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <!-- DELIVERY DETAILS -->
    <div class="card">
      <div class="card-title"><i class="ri-map-pin-2-line"></i> Delivery Details</div>
      <div class="info-row"><span class="info-label">Customer Name</span><span class="info-value"><?= htmlspecialchars($order['customer_name']) ?></span></div>
      <div class="info-row"><span class="info-label">Phone</span><span class="info-value"><?= htmlspecialchars($order['phone'] ?? '-') ?></span></div>
      <div class="info-row"><span class="info-label">Email</span><span class="info-value"><?= htmlspecialchars($order['email'] ?? '-') ?></span></div>
      <div class="info-row"><span class="info-label">Address</span><span class="info-value"><?= htmlspecialchars($order['address'] ?? '-') ?></span></div>
      <div class="info-row"><span class="info-label">City</span><span class="info-value"><?= htmlspecialchars($order['city'] ?? '-') ?></span></div>
      <div class="info-row"><span class="info-label">PIN Code</span><span class="info-value"><?= htmlspecialchars($order['pincode'] ?? '-') ?></span></div>
    </div>
  </div>

  <div>
    <!-- ORDER SUMMARY -->
    <div class="card">
      <div class="card-title"><i class="ri-receipt-line"></i> Order Summary</div>
      <div class="info-row"><span class="info-label">Order ID</span><span class="info-value">#UA<?= str_pad($order_id, 6, '0', STR_PAD_LEFT) ?></span></div>
      <div class="info-row"><span class="info-label">Order Date</span><span class="info-value"><?= $order_date ? date('d M Y, h:i A', strtotime($order_date)) : '-' ?></span></div>
      <div class="info-row"><span class="info-label">Payment</span><span class="info-value"><?= htmlspecialchars($order['payment_method'] ?? '-') ?></span></div>
      <div class="info-row"><span class="info-label">User ID</span><span class="info-value">#<?= $order['user_id'] ?></span></div>
      <div style="border-top:2px solid #1a1a1a;margin-top:10px;padding-top:14px;display:flex;justify-content:space-between;">
        <span style="font-size:15px;font-weight:700;">Total Amount</span>
        <span style="font-size:20px;font-weight:700;font-family:'Playfair Display',serif;">₹<?= number_format($order['total_amount'], 2) ?></span>
      </div>
    </div>

    <!-- UPDATE STATUS -->
    <div class="card">
      <div class="card-title"><i class="ri-settings-3-line"></i> Update Order Status</div>
      <?php if (isset($_GET['updated'])): ?>
        <div class="success-msg"><i class="ri-check-circle-line"></i> Status updated successfully!</div>
      <?php endif; ?>
      <form method="POST" action="update-order.php">
        <input type="hidden" name="order_id" value="<?= $order_id ?>">
        <input type="hidden" name="redirect" value="view-order.php?id=<?= $order_id ?>&updated=1">
        <div class="status-form">
          <select name="status" class="status-select">
            <option value="pending"   <?= $order_status==='pending'   ? 'selected' : '' ?>>⏳ Pending</option>
            <option value="confirmed" <?= $order_status==='confirmed' ? 'selected' : '' ?>>✅ Confirmed</option>
            <option value="shipped"   <?= $order_status==='shipped'   ? 'selected' : '' ?>>🚚 Shipped</option>
            <option value="delivered" <?= $order_status==='delivered' ? 'selected' : '' ?>>📦 Delivered</option>
            <option value="cancelled" <?= $order_status==='cancelled' ? 'selected' : '' ?>>❌ Cancelled</option>
          </select>
          <button type="submit" class="update-btn"><i class="ri-save-line"></i> Update</button>
        </div>
      </form>
    </div>

    <!-- DELETE ORDER -->
    <div class="card">
      <div class="card-title"><i class="ri-delete-bin-line"></i> Danger Zone</div>
      <p style="font-size:13px;color:#888;margin-bottom:14px;">Permanently delete this order. This cannot be undone.</p>
      <a href="delete-order.php?id=<?= $order_id ?>" class="delete-btn"
         onclick="return confirm('Delete Order #<?= $order_id ?>? Cannot be undone!')">
        <i class="ri-delete-bin-line"></i> Delete This Order
      </a>
    </div>
  </div>
</div>

</body>
</html>