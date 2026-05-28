<?php
session_start();
include '../api/db.php';

$user_id = intval($_GET['id'] ?? 0);
if ($user_id <= 0) {
    die("<p style='color:red;text-align:center;margin-top:50px;'>Invalid User ID.</p>");
}

$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("<p style='color:red;text-align:center;margin-top:50px;'>User not found.</p>");
}

// Get user's orders
$orders_stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$orders_stmt->bind_param("i", $user_id);
$orders_stmt->execute();
$orders = $orders_stmt->get_result();

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
  <title>User #<?= $user_id ?> — Admin</title>
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
    .edit-btn { background: #1a1a2e; color: #fff; padding: 9px 20px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 6px; }
    .edit-btn:hover { background: #2d2d5e; color: #fff; }
    .content { max-width: 1000px; margin: 30px auto; padding: 0 20px 60px; display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    @media (max-width: 700px) { .content { grid-template-columns: 1fr; } }
    .card { background: #fff; border-radius: 14px; padding: 24px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); margin-bottom: 20px; }
    .card-title { font-size: 15px; font-weight: 700; color: #1a1a2e; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1.5px solid #f0f2f5; display: flex; align-items: center; gap: 8px; }
    .card-title i { color: #e8b86d; font-size: 17px; }
    .info-row { display: flex; justify-content: space-between; padding: 9px 0; border-bottom: 1px solid #f8f9fa; font-size: 14px; }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #888; font-weight: 500; }
    .info-value { font-weight: 600; color: #1a1a1a; text-align: right; }
    .orders-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .orders-table th { background: #f8f9fa; padding: 10px 14px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: #888; font-weight: 600; }
    .orders-table td { padding: 12px 14px; border-bottom: 1px solid #f0f2f5; vertical-align: middle; }
    .orders-table tr:last-child td { border-bottom: none; }
    .status-badge { padding: 4px 12px; border-radius: 20px; color: #fff; font-size: 11px; font-weight: 600; text-transform: capitalize; }
    .view-link { color: #1a1a2e; font-weight: 600; font-size: 12px; text-decoration: none; }
    .view-link:hover { color: #e8b86d; }
    .avatar { width: 64px; height: 64px; border-radius: 50%; background: #1a1a2e; color: #e8b86d; font-size: 26px; font-weight: 700; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; }
    .full-section { grid-column: 1 / -1; }
  </style>
</head>
<body>

<div class="topbar">
  <a href="users.php" class="logo">URBAN AURA — Admin</a>
  <a href="users.php" class="back-btn"><i class="ri-arrow-left-line"></i> Back to Users</a>
</div>

<div class="page-header">
  <h1><i class="ri-user-line" style="color:#e8b86d;"></i> User #<?= $user_id ?> — <?= htmlspecialchars($user['name']) ?></h1>
  <a href="edit-user.php?id=<?= $user_id ?>" class="edit-btn"><i class="ri-edit-line"></i> Edit User</a>
</div>

<div class="content">

  <!-- USER INFO -->
  <div class="card">
    <div class="card-title"><i class="ri-user-3-line"></i> User Details</div>
    <div class="avatar"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
    <div class="info-row"><span class="info-label">User ID</span><span class="info-value">#<?= $user['user_id'] ?></span></div>
    <div class="info-row"><span class="info-label">Name</span><span class="info-value"><?= htmlspecialchars($user['name']) ?></span></div>
    <div class="info-row"><span class="info-label">Email</span><span class="info-value"><?= htmlspecialchars($user['email']) ?></span></div>
    <div class="info-row"><span class="info-label">Phone</span><span class="info-value"><?= htmlspecialchars($user['phone'] ?? '-') ?></span></div>
    <div class="info-row"><span class="info-label">Joined</span><span class="info-value"><?= isset($user['created_at']) ? date('d M Y', strtotime($user['created_at'])) : '-' ?></span></div>
  </div>

  <!-- QUICK STATS -->
  <div class="card">
    <div class="card-title"><i class="ri-bar-chart-line"></i> Quick Stats</div>
    <?php
      $total_orders = $conn->query("SELECT COUNT(*) as c FROM orders WHERE user_id = $user_id")->fetch_assoc()['c'];
      $total_spent  = $conn->query("SELECT SUM(total_amount) as s FROM orders WHERE user_id = $user_id")->fetch_assoc()['s'] ?? 0;
    ?>
    <div class="info-row"><span class="info-label">Total Orders</span><span class="info-value"><?= $total_orders ?></span></div>
    <div class="info-row"><span class="info-label">Total Spent</span><span class="info-value">₹<?= number_format($total_spent, 2) ?></span></div>
  </div>

  <!-- ORDER HISTORY -->
  <div class="card full-section">
    <div class="card-title"><i class="ri-shopping-bag-line"></i> Order History</div>
    <?php if ($orders->num_rows === 0): ?>
      <p style="color:#999;font-size:14px;">No orders placed yet.</p>
    <?php else: ?>
    <table class="orders-table">
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Date</th>
          <th>Amount</th>
          <th>Payment</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($order = $orders->fetch_assoc()): 
          $status = $order['order_status'] ?? $order['status'] ?? 'pending';
        ?>
        <tr>
          <td><strong>#UA<?= str_pad($order['order_id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
          <td><?= isset($order['order_date']) ? date('d M Y', strtotime($order['order_date'])) : '-' ?></td>
          <td><strong>₹<?= number_format($order['total_amount'], 2) ?></strong></td>
          <td><?= htmlspecialchars($order['payment_method'] ?? '-') ?></td>
          <td><span class="status-badge" style="background:<?= statusColor($status) ?>"><?= ucfirst($status) ?></span></td>
          <td><a href="view-order.php?id=<?= $order['order_id'] ?>" class="view-link">View →</a></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

</div>
</body>
</html>