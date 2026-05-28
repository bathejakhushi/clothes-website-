<?php
session_start();
$conn = new mysqli("localhost","root","","clothing_store_db");
$msg=''; $type='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $name   = $conn->real_escape_string(trim($_POST['name']??''));
    $email  = $conn->real_escape_string(trim($_POST['email']??''));
    $phone  = $conn->real_escape_string(trim($_POST['phone']??''));
    $role   = $conn->real_escape_string($_POST['role']??'customer');
    $pass   = $conn->real_escape_string(trim($_POST['password']??''));
    $active = isset($_POST['is_active']) ? 1 : 0;
    if(!$name || !$email || !$pass){
        $msg='Name, Email and Password are required!'; $type='error';
    } else {
        $chk = $conn->query("SELECT user_id FROM users WHERE email='$email'");
        if($chk->num_rows > 0){
            $msg='Email already exists!'; $type='error';
        } else {
            $sql = "INSERT INTO users (name,email,password,phone,role,is_active,created_at) VALUES ('$name','$email','$pass','$phone','$role',$active,NOW())";
            if($conn->query($sql)){ $msg='User added successfully!'; $type='success'; }
            else { $msg='Error: '.$conn->error; $type='error'; }
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add User</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{background:linear-gradient(135deg,#f8f9fa,#e9ecef);font-family:'Segoe UI',sans-serif;display:flex;justify-content:center;align-items:center;min-height:100vh;}
    .form-card{background:#fff;border-radius:12px;box-shadow:0 8px 20px rgba(0,0,0,0.2);padding:35px;width:450px;}
    h3{font-weight:bold;text-align:center;margin-bottom:25px;color:#343a40;}
    label{font-weight:600;margin-bottom:4px;display:block;}
  </style>
</head>
<body>
<div class="form-card">
  <h3>Add New User</h3>
  <?php if(!empty($msg)) echo "<div class='alert alert-".($type=='success'?'success':'danger')."'>$msg</div>"; ?>
  <form method="POST" autocomplete="off">
    <div class="mb-3">
      <label>Full Name *</label>
      <input type="text" name="name" class="form-control" placeholder="Full Name" required autocomplete="off">
    </div>
    <div class="mb-3">
      <label>Email Address *</label>
      <input type="email" name="email" class="form-control" placeholder="Email" required autocomplete="off">
    </div>
    <div class="mb-3">
      <label>Mobile Number</label>
      <input type="tel" name="phone" class="form-control" placeholder="Phone Number" autocomplete="off">
    </div>
    <div class="mb-3">
      <label>Password *</label>
      <input type="text" name="password" class="form-control" placeholder="Password" required autocomplete="new-password">
    </div>
    <div class="mb-3">
      <label>Role *</label>
      <select name="role" class="form-select">
        <option value="customer">Customer</option>
        <option value="admin">Admin</option>
      </select>
    </div>
    <div class="mb-3 form-check">
      <input type="checkbox" name="is_active" class="form-check-input" id="ac" checked>
      <label class="form-check-label fw-bold" for="ac">Active User</label>
    </div>
    <div class="d-flex justify-content-between mt-2">
      <button type="submit" class="btn btn-success px-4">Save User</button>
      <button type="button" class="btn btn-secondary" onclick="location.href='users.php'">Back</button>
    </div>
  </form>
</div>
</body>
</html>