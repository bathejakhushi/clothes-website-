<?php
session_start();
include 'db.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');

// ── Stats ──
$total_products = $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'];
$total_orders   = $conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'];
$total_users    = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$rev_row        = $conn->query("SELECT SUM(total_amount) as r FROM orders")->fetch_assoc();
$revenue        = $rev_row['r'] ? number_format((float)$rev_row['r'], 0) : '0';

// ── Recent 5 orders ──
$recent_orders = [];
$res = $conn->query("SELECT order_id, customer_name, total_amount, order_status, order_date FROM orders ORDER BY order_date DESC LIMIT 5");
while ($row = $res->fetch_assoc()) $recent_orders[] = $row;

// ── Low stock (≤ 20) ──
$low_stock = [];
$res2 = $conn->query("SELECT product_name, stock FROM products WHERE stock <= 20 ORDER BY stock ASC LIMIT 6");
while ($row = $res2->fetch_assoc()) $low_stock[] = $row;

// ── Monthly sales (last 6 months) ──
$monthly_sales = [];
$res3 = $conn->query("
    SELECT DATE_FORMAT(order_date,'%b %Y') as month, SUM(total_amount) as total
    FROM orders
    WHERE order_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(order_date,'%Y-%m')
    ORDER BY MIN(order_date) ASC
");
while ($row = $res3->fetch_assoc()) $monthly_sales[] = $row;

$conn->close();

echo json_encode([
    'total_products' => $total_products,
    'total_orders'   => $total_orders,
    'total_users'    => $total_users,
    'revenue'        => $revenue,
    'recent_orders'  => $recent_orders,
    'low_stock'      => $low_stock,
    'monthly_sales'  => $monthly_sales
]);
?>