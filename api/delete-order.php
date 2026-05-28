<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include 'db.php';

if (isset($_GET['id'])) {
    $order_id = intval($_GET['id']);

    // STEP 1 — Restore stock BEFORE deleting order items
    $items = $conn->query("SELECT product_id, quantity FROM order_items WHERE order_id = $order_id");
    if ($items && $items->num_rows > 0) {
        while ($item = $items->fetch_assoc()) {
            $product_id = intval($item['product_id']);
            $quantity   = intval($item['quantity']);
            if ($product_id > 0) {
                $conn->query("UPDATE products SET stock = stock + $quantity WHERE product_id = $product_id");
            }
        }
    }

    // STEP 2 — Delete payments first (foreign key)
    $conn->query("DELETE FROM payments WHERE order_id = $order_id");

    // STEP 3 — Delete order items
    $conn->query("DELETE FROM order_items WHERE order_id = $order_id");

    // STEP 4 — Delete the order
    if ($conn->query("DELETE FROM orders WHERE order_id = $order_id") === TRUE) {
        echo json_encode([
            "success" => true,
            "message" => "Order #$order_id deleted & stock restored!"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Error: " . $conn->error
        ]);
    }

} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request. No order ID provided."
    ]);
}

$conn->close();
?>