<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Users</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body{background:linear-gradient(135deg,#f8f9fa,#e9ecef);font-family:'Segoe UI',sans-serif;}
    h3{font-weight:bold;color:#343a40;margin-bottom:20px;}
    .search-bar{max-width:400px;margin-bottom:20px;}
    .table{border-radius:8px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.1);background:#fff;}
    .table-dark th{background-color:#343a40!important;color:#fff;}
    .badge-customer{background:#28a745;color:#fff;border-radius:20px;padding:3px 12px;font-size:11px;font-weight:700;}
    .badge-admin{background:#6a11cb;color:#fff;border-radius:20px;padding:3px 12px;font-size:11px;font-weight:700;}
    .active-badge{background:#28a745;color:#fff;border-radius:20px;padding:3px 10px;font-size:11px;font-weight:700;}
    .inactive-badge{background:#dc3545;color:#fff;border-radius:20px;padding:3px 10px;font-size:11px;font-weight:700;}
    .pwd-full{font-family:monospace;font-size:10px;color:#333;word-break:break-all;max-width:250px;}
    .btn-primary{background:linear-gradient(45deg,#6a11cb,#2575fc);border:none;}
    .btn-secondary{background:#6c757d;border:none;}
    .no-users{text-align:center;color:#aaa;padding:30px;font-style:italic;}
  </style>
</head>
<body class="p-4">
<?php
$conn = new mysqli("localhost","root","","clothing_store_db");
if($conn->connect_error){ die("Connection failed: ".$conn->connect_error); }
if(isset($_GET['delete'])){ $id=intval($_GET['delete']); $conn->query("DELETE FROM users WHERE user_id=$id"); header("Location: users.php"); exit; }
if(isset($_GET['toggle'])){ $id=intval($_GET['toggle']); $conn->query("UPDATE users SET is_active=IF(is_active=1,0,1) WHERE user_id=$id"); header("Location: users.php"); exit; }
?>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Manage Users</h3>
    <div>
      <button class="btn btn-primary shadow-sm me-2" onclick="window.location.href='add-user.php'">+ Add User</button>
      <button class="btn btn-secondary shadow-sm" onclick="window.location.href='dashboard.html'">Back</button>
    </div>
  </div>
  <div class="search-bar">
    <input type="text" id="searchInput" class="form-control shadow-sm" placeholder="Search users..." onkeyup="searchTable()">
  </div>
  <div class="table-responsive">
  <table class="table table-striped table-hover" id="usersTable">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Full Name</th>
        <th>Email</th>
        <th>Password</th>
        <th>Mobile</th>
        <th>Role</th>
        <th>Status</th>
        <th>Created At</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
<?php
$result = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
if(!$result || $result->num_rows === 0){
    echo "<tr><td colspan='9' class='no-users'>No users found.</td></tr>";
} else {
    while($row = $result->fetch_assoc()){
        $uid     = $row['user_id'];
        $name    = htmlspecialchars($row['name']);
        $email   = htmlspecialchars($row['email']);
        $pwd = htmlspecialchars($row['password'] ?? '');
        $phone   = htmlspecialchars($row['phone'] ?? '-');
        $role    = $row['role'] ?? 'customer';
        $active  = intval($row['is_active'] ?? 1);
        $created = $row['created_at'];

        $rb = ($role === 'admin')
            ? "<span class='badge-admin'>Admin</span>"
            : "<span class='badge-customer'>Customer</span>";

        $ab = $active
            ? "<span class='active-badge'>Active</span>"
            : "<span class='inactive-badge'>Inactive</span>";

        echo "<tr>";
        echo "<td>" . $uid . "</td>";
        echo "<td>" . $name . "</td>";
        echo "<td>" . $email . "</td>";
        echo "<td class='pwd-full'>" . $pwd . "</td>";
        echo "<td>" . $phone . "</td>";
        echo "<td>" . $rb . "</td>";
        echo "<td>" . $ab . "</td>";
        echo "<td>" . $created . "</td>";
        echo "<td>
            <a href='edit-user.php?id=" . $uid . "' class='btn btn-sm btn-warning'><i class='fa fa-pen'></i></a>
            <a href='users.php?toggle=" . $uid . "' class='btn btn-sm btn-info' onclick='return confirm(\"Toggle status?\")'><i class='fa fa-toggle-on'></i></a>
            <a href='users.php?delete=" . $uid . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Delete user?\")'><i class='fa fa-trash'></i></a>
        </td>";
        echo "</tr>";
    }
}
$conn->close();
?>
    </tbody>
  </table>
  </div>
  <script>
  function searchTable(){
    var input = document.getElementById("searchInput").value.toLowerCase();
    document.querySelectorAll("#usersTable tbody tr").forEach(function(row){
      row.style.display = row.innerText.toLowerCase().includes(input) ? "" : "none";
    });
  }
  </script>
</body>
</html>