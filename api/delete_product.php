<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "clothing_store_db");
if ($conn->connect_error) { echo json_encode(["error" => "Connection failed"]); exit; }

$data = json_decode(file_get_contents("php://input"), true);
$id = intval($data['id']);

$sql = "DELETE FROM products WHERE product_id = $id";
if ($conn->query($sql)) {
    echo json_encode(["success" => true, "message" => "Product deleted from DB and frontend"]);
} else {
    echo json_encode(["error" => "Delete failed"]);
}
$conn->close();
?>