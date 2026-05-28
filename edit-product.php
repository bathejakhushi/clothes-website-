<?php
include '../api/db.php';

$product_id = intval($_GET['id'] ?? 0);
if ($product_id <= 0) die("<p style='color:red;text-align:center;margin-top:50px;'>Invalid Product ID.</p>");

// Handle form save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price       = floatval($_POST['price']);
    $stock       = intval($_POST['stock']);
    $subcategory = trim($_POST['subcategory']);
    $sizes       = trim($_POST['sizes']);
    $status      = $stock > 0 ? 'available' : 'out_of_stock';

    // Handle new image upload
    $image_url = trim($_POST['existing_image']);
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = '../images/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $ext      = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = 'product_' . time() . '_' . rand(100,999) . '.' . $ext;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
            $image_url = 'images/uploads/' . $filename;
        }
    }

    $stmt = $conn->prepare("UPDATE products SET product_name=?, description=?, price=?, stock=?, subcategory=?, sizes=?, image_url=?, status=? WHERE product_id=?");
    $stmt->bind_param("ssdissssi", $name, $description, $price, $stock, $subcategory, $sizes, $image_url, $status, $product_id);

    if ($stmt->execute()) {
        header("Location: Adminproducts.html?msg=updated");
        exit;
    } else {
        $error = "Update failed: " . $conn->error;
    }
    $stmt->close();
}

// Fetch product
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
if (!$p) die("<p style='color:red;text-align:center;margin-top:50px;'>Product not found.</p>");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Edit Product #<?= $product_id ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.2.0/fonts/remixicon.css" rel="stylesheet"/>
  <style>
    body { background: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
    .topbar { background: #343a40; color: #fff; padding: 14px 30px; display: flex; align-items: center; justify-content: space-between; }
    .topbar .logo { font-size: 18px; font-weight: 700; color: #fff; text-decoration: none; }
    .back-btn { background: rgba(255,255,255,0.1); color: #fff; border: none; padding: 8px 18px; border-radius: 8px; font-size: 13px; cursor: pointer; text-decoration: none; }
    .back-btn:hover { background: rgba(255,255,255,0.2); color: #fff; }
    .container { max-width: 700px; margin: 40px auto; padding: 0 20px 60px; }
    .card { background: #fff; border-radius: 14px; padding: 30px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
    label { font-size: 13px; font-weight: 600; color: #555; text-transform: uppercase; letter-spacing: 0.05em; }
    .current-img { width: 100px; height: 110px; object-fit: cover; border-radius: 10px; border: 1px solid #ddd; margin-bottom: 10px; }
    .save-btn { background: #343a40; color: #fff; border: none; padding: 12px 30px; border-radius: 9px; font-size: 14px; font-weight: 700; cursor: pointer; }
    .save-btn:hover { background: #495057; }
  </style>
</head>
<body>

<div class="topbar">
  <a href="Adminproducts.html" class="logo">UrbanAura — Admin</a>
  <a href="Adminproducts.html" class="back-btn">← Back to Products</a>
</div>

<div class="container">
  <div class="card">
    <h4 class="fw-bold mb-4">✏️ Edit Product #<?= $product_id ?></h4>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="existing_image" value="<?= htmlspecialchars($p['image_url'] ?? '') ?>">

      <div class="mb-3">
        <label>Product Name</label>
        <input type="text" name="product_name" class="form-control" value="<?= htmlspecialchars($p['product_name']) ?>" required>
      </div>

      <div class="mb-3">
        <label>Description</label>
        <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($p['description'] ?? '') ?></textarea>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label>Price (₹)</label>
          <input type="number" name="price" class="form-control" value="<?= $p['price'] ?>" step="0.01" required>
        </div>
        <div class="col-md-6 mb-3">
          <label>Stock</label>
          <input type="number" name="stock" class="form-control" value="<?= $p['stock'] ?>" required>
        </div>
      </div>

      <div class="mb-3">
        <label>Subcategory</label>
        <select name="subcategory" class="form-select">
          <?php
          $subs = ['Shirts','T-Shirts','Jeans','Mens Shoes','Womens Kurtis','Womens Western','Womens Shoes','Womens Heels','Boys Wear','Girls Wear','Kids Footwear','Kids Winter'];
          foreach ($subs as $s) {
            $sel = ($p['subcategory'] === $s) ? 'selected' : '';
            echo "<option value='$s' $sel>$s</option>";
          }
          ?>
        </select>
      </div>

      <div class="mb-3">
        <label>Sizes <small class="text-muted">(comma separated e.g. S,M,L,XL)</small></label>
        <input type="text" name="sizes" class="form-control" value="<?= htmlspecialchars($p['sizes'] ?? '') ?>" placeholder="S,M,L,XL">
      </div>

      <div class="mb-4">
        <label>Product Image</label><br>
        <?php if (!empty($p['image_url'])): ?>
          <img src="../<?= htmlspecialchars($p['image_url']) ?>" class="current-img" onerror="this.style.display='none'">
          <br><small class="text-muted">Current image. Upload new to replace.</small>
        <?php endif; ?>
        <input type="file" name="image" class="form-control mt-2" accept="image/*">
      </div>

      <button type="submit" class="save-btn"><i class="ri-save-line"></i> Save Changes</button>
      <a href="Adminproducts.html" class="btn btn-secondary ms-2">Cancel</a>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>