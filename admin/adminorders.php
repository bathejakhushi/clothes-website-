<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Orders</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body { background: linear-gradient(135deg, #f8f9fa, #e9ecef); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    h3 { font-weight: bold; color: #343a40; margin-bottom: 20px; }
    .search-bar { max-width: 400px; margin-bottom: 20px; }
    .table { border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .table-dark th { background-color: #343a40 !important; color: #fff; }
    .badge { font-size: 0.85rem; padding: 6px 10px; border-radius: 6px; }
    .btn-secondary { background: #6c757d; border: none; }
    .btn-secondary:hover { background: #5a6268; }
    .action-btns button, .action-btns a { margin-right: 5px; transition: transform 0.2s; }
    .action-btns button:hover, .action-btns a:hover { transform: scale(1.1); }
  </style>
</head>
<body class="p-4">
  <h3>Manage Orders 📦</h3>
  <div class="search-bar">
    <input type="text" id="searchInput" class="form-control shadow-sm" placeholder="🔍 Search orders..." onkeyup="searchTable()">
  </div>
  <table class="table table-striped table-hover" id="ordersTable">
    <thead class="table-dark">
      <tr>
        <th>Order ID</th>
        <th>User ID</th>
        <th>Total Amount</th>
        <th>Order Status</th>
        <th>Order Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $conn = new mysqli("localhost", "root", "", "clothing_store_db");
      if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
      $result = $conn->query("SELECT * FROM orders ORDER BY order_date DESC");
      while($row = $result->fetch_assoc()) {
        $badgeClass = ($row['order_status'] == 'Pending') ? 'bg-warning' :
                      (($row['order_status'] == 'Delivered') ? 'bg-success' : 'bg-secondary');
        $oid = $row['order_id'];
        echo "<tr>
                <td>{$row['order_id']}</td>
                <td>{$row['user_id']}</td>
                <td>₹{$row['total_amount']}</td>
                <td><span class='badge $badgeClass'>{$row['order_status']}</span></td>
                <td>{$row['order_date']}</td>
                <td class='action-btns'>
                  <a href='view-order.php?id=$oid' class='btn btn-sm btn-info'><i class='fa-solid fa-eye'></i></a>
                  <button class='btn btn-sm btn-danger' onclick='deleteOrder($oid)'><i class='fa-solid fa-trash'></i></button>
                </td>
              </tr>";
      }
      $conn->close();
      ?>
    </tbody>
  </table>
  <button class="btn btn-secondary shadow-sm" onclick="window.location.href='dashboard.html'">⬅ Back</button>

<script>
function searchTable() {
  const input = document.getElementById("searchInput").value.toLowerCase();
  const rows = document.querySelectorAll("#ordersTable tbody tr");
  rows.forEach(row => {
    const text = row.innerText.toLowerCase();
    row.style.display = text.includes(input) ? "" : "none";
  });
}
function deleteOrder(id) {
  if (!confirm('Delete Order #' + id + '?\nStock will be restored automatically!')) return;
  fetch('../api/delete-order.php?id=' + id)
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        alert('✅ ' + data.message);
        location.reload();
      } else {
        alert('❌ Error: ' + data.message);
      }
    })
    .catch(err => {
      alert('❌ Network error: ' + err);
    });
}
</script>
</body>
</html>