<?php
session_start();
include 'db.php';
header('Content-Type: application/json');

$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
if (!$user_id) { echo json_encode(['success' => false, 'message' => 'Not logged in']); exit; }

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ── GET CART ──
if ($action === 'get') {
    $cart_row = $conn->query("SELECT cart_id FROM cart WHERE user_id = $user_id LIMIT 1")->fetch_assoc();
    if (!$cart_row) { echo json_encode(['success' => true, 'items' => []]); exit; }
    $cart_id = $cart_row['cart_id'];
    $items = [];
    $res = $conn->query("SELECT ci.*, p.product_name, p.price, p.image_url 
                         FROM cart_items ci 
                         LEFT JOIN products p ON ci.product_id = p.product_id
                         WHERE ci.cart_id = $cart_id");
    while ($row = $res->fetch_assoc()) $items[] = $row;
    echo json_encode(['success' => true, 'items' => $items]);
    exit;
}

// ── ADD ITEM ──
if ($action === 'add') {
    $product_id   = intval($_POST['product_id'] ?? 0);
    $product_name = $conn->real_escape_string($_POST['product_name'] ?? '');
    $price        = floatval($_POST['price'] ?? 0);
    $image_url    = $conn->real_escape_string($_POST['image_url'] ?? '');
    $size         = $conn->real_escape_string($_POST['size'] ?? 'M');
    $quantity     = intval($_POST['quantity'] ?? 1);

    // Get or create cart for user
    $cart_row = $conn->query("SELECT cart_id FROM cart WHERE user_id = $user_id LIMIT 1")->fetch_assoc();
    if (!$cart_row) {
        $conn->query("INSERT INTO cart (user_id) VALUES ($user_id)");
        $cart_id = $conn->insert_id;
    } else {
        $cart_id = $cart_row['cart_id'];
    }

    // Check if item already in cart
    $existing = $conn->query("SELECT cart_item_id, quantity FROM cart_items 
                               WHERE cart_id = $cart_id AND product_id = $product_id AND size = '$size'")->fetch_assoc();
    if ($existing) {
        $new_qty = $existing['quantity'] + $quantity;
        $conn->query("UPDATE cart_items SET quantity = $new_qty WHERE cart_item_id = {$existing['cart_item_id']}");
    } else {
        $pid = $product_id ?: 'NULL';
        $conn->query("INSERT INTO cart_items (cart_id, product_id, product_name, price, image_url, size, quantity)
                      VALUES ($cart_id, $pid, '$product_name', $price, '$image_url', '$size', $quantity)");
    }
    echo json_encode(['success' => true, 'message' => 'Item added to cart']);
    exit;
}

// ── UPDATE QUANTITY ──
if ($action === 'update') {
    $cart_item_id = intval($_POST['cart_item_id'] ?? 0);
    $quantity     = intval($_POST['quantity'] ?? 1);
    if ($quantity <= 0) {
        $conn->query("DELETE FROM cart_items WHERE cart_item_id = $cart_item_id");
    } else {
        $conn->query("UPDATE cart_items SET quantity = $quantity WHERE cart_item_id = $cart_item_id");
    }
    echo json_encode(['success' => true]);
    exit;
}

// ── REMOVE ITEM ──
if ($action === 'remove') {
    $cart_item_id = intval($_POST['cart_item_id'] ?? 0);
    $conn->query("DELETE FROM cart_items WHERE cart_item_id = $cart_item_id");
    echo json_encode(['success' => true, 'message' => 'Item removed']);
    exit;
}

// ── CLEAR CART ──
if ($action === 'clear') {
    $cart_row = $conn->query("SELECT cart_id FROM cart WHERE user_id = $user_id LIMIT 1")->fetch_assoc();
    if ($cart_row) {
        $conn->query("DELETE FROM cart_items WHERE cart_id = {$cart_row['cart_id']}");
    }
    echo json_encode(['success' => true, 'message' => 'Cart cleared']);
    exit;
}

// ── SYNC (localStorage → DB on login) ──
if ($action === 'sync') {
    $items = json_decode(file_get_contents('php://input'), true)['items'] ?? [];
    
    // Get or create cart
    $cart_row = $conn->query("SELECT cart_id FROM cart WHERE user_id = $user_id LIMIT 1")->fetch_assoc();
    if (!$cart_row) {
        $conn->query("INSERT INTO cart (user_id) VALUES ($user_id)");
        $cart_id = $conn->insert_id;
    } else {
        $cart_id = $cart_row['cart_id'];
    }

    foreach ($items as $item) {
        $product_name = $conn->real_escape_string($item['name'] ?? '');
        $price        = floatval($item['price'] ?? 0);
        $image_url    = $conn->real_escape_string($item['img'] ?? $item['image'] ?? '');
        $size         = $conn->real_escape_string($item['size'] ?? 'M');
        $quantity     = intval($item['qty'] ?? $item['quantity'] ?? 1);
        $pid_raw      = $item['id'] ?? '';
        $product_id   = strpos($pid_raw, 'db_') === 0 ? intval(str_replace('db_', '', $pid_raw)) : 'NULL';

        // Check existing
        $check = "SELECT cart_item_id, quantity FROM cart_items WHERE cart_id=$cart_id AND product_name='$product_name' AND size='$size'";
        $existing = $conn->query($check)->fetch_assoc();
        if ($existing) {
            $new_qty = $existing['quantity'] + $quantity;
            $conn->query("UPDATE cart_items SET quantity=$new_qty WHERE cart_item_id={$existing['cart_item_id']}");
        } else {
            $conn->query("INSERT INTO cart_items (cart_id, product_id, product_name, price, image_url, size, quantity)
                          VALUES ($cart_id, $product_id, '$product_name', $price, '$image_url', '$size', $quantity)");
        }
    }
    echo json_encode(['success' => true, 'message' => 'Cart synced to DB']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
$conn->close();
?>