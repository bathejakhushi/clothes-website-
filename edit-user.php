<?php
session_start();
include '../api/db.php';

$user_id = intval($_GET['id'] ?? 0);
if ($user_id <= 0) {
    die("<p style='color:red;text-align:center;margin-top:50px;'>Invalid User ID.</p>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=? WHERE user_id=?");
    $stmt->bind_param("sssi", $name, $email, $phone, $user_id);
    if ($stmt->execute()) {
        header("Location: view-user.php?id=$user_id&updated=1");
        exit;
    } else {
        $error = "Update failed: " . $conn->error;
    }
    $stmt->close();
}

$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("<p style='color:red;text-align:center;margin-top:50px;'>User not found.</p>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Edit User #<?= $user_id ?> — Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.2.0/fonts/remixicon.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'DM Sans', sans-serif; background: #f4f6f9; color: #1a1a1a; min-height: 100vh; }
    .topbar { background: #1a1a2e; color: #fff; padding: 14px 30px; display: flex; align-items: center; justify-content: space-between; }
    .topbar .logo { font-family: 'Playfair Display', serif; font-size: 20px; color: #e8b86d; text-decoration: none; }
    .topbar .back-btn { background: rgba(255,255,255,0.1); color: #fff; border: none; padding: 8px 18px; border-radius: 8px; font-size: 13px; cursor: pointer; text-decoration: none; display: flex; align-items: center; gap: 6px; }
    .topbar .back-btn:hover { background: rgba(255,255,255,0.2); }
    .page-header { background: #fff; border-bottom: 1px solid #e8ecf0; padding: 24px 30px; }
    .page-header h1 { font-family: 'Playfair Display', serif; font-size: 24px; color: #1a1a2e; }
    .container { max-width: 600px; margin: 40px auto; padding: 0 20px 60px; }
    .card { background: #fff; border-radius: 14px; padding: 30px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
    .card-title { font-size: 16px; font-weight: 700; color: #1a1a2e; margin-bottom: 24px; padding-bottom: 14px; border-bottom: 1.5px solid #f0f2f5; display: flex; align-items: center; gap: 8px; }
    .card-title i { color: #e8b86d; font-size: 18px; }
    .form-group { margin-bottom: 20px; }
    label { display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 7px; text-transform: uppercase; letter-spacing: 0.05em; }
    input[type="text"], input[type="email"] { width: 100%; padding: 11px 14px; border: 1.5px solid #e0dbd4; border-radius: 9px; font-family: 'DM Sans', sans-serif; font-size: 14px; background: #faf9f7; outline: none; transition: border .2s; }
    input:focus { border-color: #1a1a2e; background: #fff; }
    .btn-row { display: flex; gap: 12px; margin-top: 28px; }
    .save-btn { background: #1a1a2e; color: #fff; border: none; padding: 12px 28px; border-radius: 9px; font-size: 14px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 7px; }
    .save-btn:hover { background: #2d2d5e; }
    .cancel-btn { background: #f0f2f5; color: #555; border: none; padding: 12px 28px; border-radius: 9px; font-size: 14px; font-weight: 700; cursor: pointer; text-decoration: none; display: flex; align-items: center; gap: 7px; }
    .cancel-btn:hover { background: #e0e2e5; }
    .error-msg { background: #fee; color: #c0392b; padding: 12px 16px; border-radius: 9px; font-size: 13px; font-weight: 600; margin-bottom: 20px; }
    .id-note { font-size: 12px; color: #999; margin-top: 5px; }
  </style>
</head>
<body>

<div class="topbar">
  <a href="users.php" class="logo">URBAN AURA — Admin</a>
  <a href="view-user.php?id=<?= $user_id ?>" class="back-btn"><i class="ri-arrow-left-line"></i> Back to User</a>
</div>

<div class="page-header">
  <h1><i class="ri-edit-line" style="color:#e8b86d;margin-right:8px;"></i> Edit User #<?= $user_id ?></h1>
</div>

<div class="container">
  <div class="card">
    <div class="card-title"><i class="ri-user-settings-line"></i> Edit User Details</div>

    <?php if (!empty($error)): ?>
      <div class="error-msg"><i class="ri-error-warning-line"></i> <?= $error ?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
      <div class="form-group">
        <label>User ID</label>
        <input type="text" value="#<?= $user_id ?>" disabled style="background:#f0f2f5;color:#999;" autocomplete="off">
        <p class="id-note">User ID cannot be changed</p>
      </div>
      <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required placeholder="Enter full name" autocomplete="off">
      </div>
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required placeholder="Enter email" autocomplete="off">
      </div>
      <div class="form-group">
        <label>Phone Number</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="Enter phone number" autocomplete="off">
      </div>
      <div class="btn-row">
        <button type="submit" class="save-btn"><i class="ri-save-line"></i> Save Changes</button>
        <a href="view-user.php?id=<?= $user_id ?>" class="cancel-btn"><i class="ri-close-line"></i> Cancel</a>
      </div>
    </form>
  </div>
</div>

</body>
</html>