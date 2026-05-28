<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Categories</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body { background: linear-gradient(135deg, #f8f9fa, #e9ecef); font-family: 'Segoe UI', sans-serif; }
    .sidebar { width:220px; background:#343a40; color:#fff; min-height:100vh; position:fixed; }
    .sidebar h4 { text-align:center; padding:20px; border-bottom:1px solid #495057; }
    .sidebar a { color:#fff; display:block; padding:12px; text-decoration:none; }
    .sidebar a:hover, .sidebar a.active { background:#495057; }
    .content { margin-left:220px; padding:30px; }
    .card { border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .table-dark th { background-color: #343a40 !important; }
    .btn-sm { transition: transform 0.2s; }
    .btn-sm:hover { transform: scale(1.1); }
  </style>
</head>
<body>
<div class="d-flex">

  <div class="sidebar">
    <h4>UrbanAura Fashion</h4>
    <a href="users.php">👥 Users</a>
    <a href="Adminproducts.html">👗 Products</a>
    <a href="adminorders.php">📦 Orders</a>
    <a href="categories.php" class="active">🗂️ Categories</a>
    <hr>
    <a href="#" onclick="localStorage.removeItem('isAdmin');window.location.href='../login.html'">🚪 Logout</a>
  </div>

  <div class="content w-100">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="fw-bold mb-0">🗂️ Manage Categories</h3>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">+ Add Category</button>
    </div>

    <?php
    $conn = new mysqli("localhost", "root", "", "clothing_store_db");
    if ($conn->connect_error) die("Connection failed");

    $msg = $_GET['msg'] ?? '';
    if ($msg === 'added')   echo "<div class='alert alert-success'>✅ Category added!</div>";
    if ($msg === 'deleted') echo "<div class='alert alert-danger'>🗑️ Category deleted!</div>";
    if ($msg === 'updated') echo "<div class='alert alert-info'>✏️ Category updated!</div>";

    $result = $conn->query("SELECT * FROM categories ORDER BY category_id");
    ?>

    <div class="card p-3">
      <input type="text" id="searchInput" class="form-control mb-3" placeholder="🔍 Search categories..." onkeyup="searchTable()" style="max-width:350px;">
      <table class="table table-striped table-hover" id="catTable">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Category Name</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['category_id'] ?></td>
            <td><strong><?= htmlspecialchars($row['category_name']) ?></strong></td>
            <td>
              <span class="badge <?= $row['status']==='active' ? 'bg-success' : 'bg-secondary' ?>">
                <?= ucfirst($row['status']) ?>
              </span>
            </td>
            <td>
              <button class="btn btn-sm btn-warning"
                onclick="openEdit(<?= $row['category_id'] ?>, '<?= htmlspecialchars($row['category_name'], ENT_QUOTES) ?>', '<?= $row['status'] ?>')">
                <i class="fa fa-edit"></i>
              </button>
              <button class="btn btn-sm btn-danger"
                onclick="deleteCategory(<?= $row['category_id'] ?>, '<?= htmlspecialchars($row['category_name'], ENT_QUOTES) ?>')">
                <i class="fa fa-trash"></i>
              </button>
            </td>
          </tr>
          <?php endwhile; $conn->close(); ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ADD MODAL -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="category-action.php">
        <input type="hidden" name="action" value="add">
        <div class="modal-header">
          <h5 class="modal-title">➕ Add New Category</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-bold">Category Name</label>
            <input type="text" name="category_name" class="form-control" placeholder="e.g. Men, Women, Kids..." required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Status</label>
            <select name="status" class="form-select">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Add Category</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="category-action.php">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="category_id" id="editId">
        <div class="modal-header">
          <h5 class="modal-title">✏️ Edit Category</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-bold">Category Name</label>
            <input type="text" name="category_name" id="editName" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Status</label>
            <select name="status" id="editStatus" class="form-select">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function searchTable() {
  const input = document.getElementById("searchInput").value.toLowerCase();
  document.querySelectorAll("#catTable tbody tr").forEach(row => {
    row.style.display = row.innerText.toLowerCase().includes(input) ? "" : "none";
  });
}
function openEdit(id, name, status) {
  document.getElementById('editId').value = id;
  document.getElementById('editName').value = name;
  document.getElementById('editStatus').value = status;
  new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
}
function deleteCategory(id, name) {
  if (!confirm('Delete category "' + name + '"?')) return;
  window.location.href = 'category-action.php?action=delete&id=' + id;
}
</script>
</body>
</html>