<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "clothing_store_db");
if ($conn->connect_error) { echo json_encode(["error" => "Connection failed"]); exit; }

$data = json_decode(file_get_contents("php://input"), true);
$product_id = intval($data['product_id']);
$action     = $data['action']; // 'decrease' or 'increase'

if ($action === 'decrease') {
    // Order placed → stock goes down, mark out of stock if 0
    $sql = "UPDATE products 
            SET stock = GREATEST(stock - 1, 0),
                status = CASE WHEN stock - 1 <= 0 THEN 'out_of_stock' ELSE 'available' END
            WHERE product_id = $product_id";
} elseif ($action === 'increase') {
    // Order deleted → stock goes back up, mark available
    $sql = "UPDATE products 
            SET stock = stock + 1,
                status = 'available'
            WHERE product_id = $product_id";
} else {
    echo json_encode(["error" => "Invalid action"]); exit;
}

if ($conn->query($sql)) {
    // Return updated stock
    $r = $conn->query("SELECT stock, status FROM products WHERE product_id = $product_id");
    $row = $r->fetch_assoc();
    echo json_encode(["success" => true, "stock" => $row['stock'], "status" => $row['status']]);
} else {
    echo json_encode(["error" => "Update failed"]);
}
$conn->close();
?>