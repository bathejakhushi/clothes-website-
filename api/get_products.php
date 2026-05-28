<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "clothing_store_db");
if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed"]);
    exit;
}

// If subcategory is passed → filter (for frontend pages)
// If nothing is passed → return ALL products (for admin panel)
if (!empty($_GET['subcategory'])) {
    $sub = $conn->real_escape_string($_GET['subcategory']);
    $sql = "SELECT * FROM products WHERE subcategory = '$sub' ORDER BY product_id ASC";
} else {
    $sql = "SELECT * FROM products ORDER BY subcategory, product_id ASC";
}

$result = $conn->query($sql);
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

echo json_encode($products);
$conn->close();
?>