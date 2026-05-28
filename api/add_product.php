<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$host = 'localhost';
$db   = 'clothing_store_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'DB connection failed: ' . $e->getMessage()]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$required = ['name', 'category_id', 'subcategory', 'price', 'stock', 'status'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        echo json_encode(['success' => false, 'error' => "Missing field: $field"]);
        exit;
    }
}

$product_name = trim($data['name']);
$category_id  = intval($data['category_id']);
$subcategory  = trim($data['subcategory']);
$description  = trim($data['description'] ?? '');
$price        = floatval($data['price']);
$stock        = intval($data['stock']);
$status       = trim($data['status']);
$sizes        = trim($data['sizes'] ?? '');   // ✅ NEW
$image_base64 = $data['image'] ?? '';

// ── SAVE IMAGE ────────────────────────────────────────────────────
$image_url = '';
if (!empty($image_base64)) {
    if (preg_match('/^data:image\/(\w+);base64,/', $image_base64, $matches)) {
        $ext = strtolower($matches[1]);
        if ($ext === 'jpeg') $ext = 'jpg';
        $base64data = substr($image_base64, strpos($image_base64, ',') + 1);
        $imageData  = base64_decode($base64data);
        if ($imageData !== false) {
            $uploadDir = __DIR__ . '/../images/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $filename = 'product_' . time() . '_' . mt_rand(100, 999) . '.' . $ext;
            if (file_put_contents($uploadDir . $filename, $imageData) !== false) {
                $image_url = 'images/uploads/' . $filename;
            }
        }
    }
}

// ── INSERT PRODUCT ────────────────────────────────────────────────
try {
    $stmt = $pdo->prepare("
        INSERT INTO products 
            (category_id, subcategory, product_name, description, price, stock, status, image_url, sizes)
        VALUES 
            (:category_id, :subcategory, :product_name, :description, :price, :stock, :status, :image_url, :sizes)
    ");

    $stmt->execute([
        ':category_id'  => $category_id,
        ':subcategory'  => $subcategory,
        ':product_name' => $product_name,
        ':description'  => $description,
        ':price'        => $price,
        ':stock'        => $stock,
        ':status'       => $status,
        ':image_url'    => $image_url,
        ':sizes'        => $sizes     // ✅ NEW
    ]);

    $newId = $pdo->lastInsertId();

    echo json_encode([
        'success'    => true,
        'product_id' => $newId,
        'image_url'  => $image_url,
        'message'    => "Product '$product_name' added successfully!"
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>