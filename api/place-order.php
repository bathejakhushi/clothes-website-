<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
include 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) { echo json_encode(['success' => false, 'message' => 'No data received']); exit; }

$user_id        = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 'NULL';
$customer_name  = $conn->real_escape_string($data['customer_name'] ?? 'Guest');
$phone          = $conn->real_escape_string($data['phone'] ?? '');
$address        = $conn->real_escape_string($data['address'] ?? '');
$city           = $conn->real_escape_string($data['city'] ?? '');
$pincode        = $conn->real_escape_string($data['pincode'] ?? '');
$email          = $conn->real_escape_string($data['email'] ?? '');
$payment_method = $conn->real_escape_string($data['payment_method'] ?? 'COD');
$total_amount   = floatval($data['total_amount'] ?? 0);
$cart_items     = $data['cart_items'] ?? [];

if (empty($cart_items)) { echo json_encode(['success' => false, 'message' => 'Cart is empty']); exit; }
if ($total_amount <= 0)  { echo json_encode(['success' => false, 'message' => 'Invalid total amount']); exit; }

// Insert order
$sql = "INSERT INTO orders (user_id, customer_name, phone, address, city, pincode, email, payment_method, total_amount, order_status, order_date)
        VALUES ($user_id, '$customer_name', '$phone', '$address', '$city', '$pincode', '$email', '$payment_method', '$total_amount', 'pending', NOW())";

if ($conn->query($sql) === TRUE) {
    $order_id = $conn->insert_id;

    foreach ($cart_items as $item) {
        $product_name = $conn->real_escape_string($item['name'] ?? 'Unknown');
        $quantity     = intval($item['qty'] ?? $item['quantity'] ?? 1);
        $price        = floatval($item['price'] ?? 0);
        $size         = $conn->real_escape_string($item['size'] ?? '');
        $subcategory  = $conn->real_escape_string($item['subcategory'] ?? '');
        $raw_id       = $item['id'] ?? '';

        $numeric_product_id = null;

        if (strpos($raw_id, 'db_') === 0) {
            $numeric_product_id = intval(str_replace('db_', '', $raw_id));
        } elseif (is_numeric($raw_id)) {
            $numeric_product_id = intval($raw_id);
        } else {
            $escaped_name = $conn->real_escape_string($product_name);
            $res = $conn->query("SELECT product_id, subcategory FROM products WHERE product_name = '$escaped_name' LIMIT 1");
            if ($res && $row = $res->fetch_assoc()) {
                $numeric_product_id = intval($row['product_id']);
                if (empty($subcategory)) {
                    $subcategory = $conn->real_escape_string($row['subcategory'] ?? '');
                }
            }
        }

        // If subcategory still empty, fetch from products table using product_id
        if (empty($subcategory) && $numeric_product_id) {
            $res2 = $conn->query("SELECT subcategory FROM products WHERE product_id = $numeric_product_id LIMIT 1");
            if ($res2 && $row2 = $res2->fetch_assoc()) {
                $subcategory = $conn->real_escape_string($row2['subcategory'] ?? '');
            }
        }

        $pid_val = $numeric_product_id ? $numeric_product_id : 'NULL';

        $conn->query("INSERT INTO order_items (order_id, product_id, product_name, quantity, price, size, subcategory)
                      VALUES ('$order_id', $pid_val, '$product_name', '$quantity', '$price', '$size', '$subcategory')");

        if ($numeric_product_id) {
            $conn->query("UPDATE products SET stock = GREATEST(0, stock - $quantity) WHERE product_id = $numeric_product_id");
        }
    }

    $conn->query("INSERT INTO payments (order_id, payment_method, payment_status, amount)
                  VALUES ('$order_id', '$payment_method', 'pending', '$total_amount')");

    if ($user_id !== 'NULL') {
        $cart_row = $conn->query("SELECT cart_id FROM cart WHERE user_id = $user_id LIMIT 1")->fetch_assoc();
        if ($cart_row) {
            $conn->query("DELETE FROM cart_items WHERE cart_id = {$cart_row['cart_id']}");
        }
    }

    echo json_encode(['success' => true, 'order_id' => $order_id, 'message' => 'Order placed successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $conn->error]);
}
$conn->close();
?>