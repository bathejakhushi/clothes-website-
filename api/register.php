<?php
session_start();
header('Content-Type: application/json');
include 'db.php';

$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$phone    = trim($_POST['phone'] ?? '');

if(!$name || !$email || !$password){
    echo json_encode(['success'=>false,'message'=>'Name, email and password required']); exit;
}

$email    = $conn->real_escape_string($email);
$name     = $conn->real_escape_string($name);
$phone    = $conn->real_escape_string($phone);
$password = $conn->real_escape_string($password);

$check = $conn->query("SELECT user_id FROM users WHERE email='$email' LIMIT 1");
if($check && $check->num_rows > 0){
    echo json_encode(['success'=>false,'message'=>'Email already registered']); exit;
}

$sql = "INSERT INTO users (name,email,password,phone,role,is_active,created_at)
        VALUES ('$name','$email','$password','$phone','customer',1,NOW())";

if($conn->query($sql)){
    $_SESSION['user_id']   = $conn->insert_id;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_role'] = 'customer';
    echo json_encode(['success'=>true,'name'=>$name]);
} else {
    echo json_encode(['success'=>false,'message'=>'Registration failed: '.$conn->error]);
}
$conn->close();
?>