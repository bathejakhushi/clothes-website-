<?php
session_start();
header('Content-Type: application/json');
include 'db.php';

$email    = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$role     = trim($_POST['role'] ?? 'customer');

if(!$email || !$password){
    echo json_encode(['success'=>false,'message'=>'Email and password required']); exit;
}

$email = $conn->real_escape_string($email);
$role  = $conn->real_escape_string($role);

$result = $conn->query("SELECT * FROM users WHERE email='$email' AND role='$role' AND is_active=1 LIMIT 1");

if($result && $result->num_rows > 0){
    $user = $result->fetch_assoc();
    $stored = $user['password'];

    // Check plain text first, then hashed (for old users who registered themselves)
    $match = false;
    if($password === $stored){
        $match = true; // plain text match
    } elseif(strlen($stored) > 30 && password_verify($password, $stored)){
        $match = true; // hashed match (old registered users)
    }

    if($match){
        $_SESSION['user_id']   = $user['user_id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        echo json_encode(['success'=>true,'name'=>$user['name'],'role'=>$user['role']]);
    } else {
        echo json_encode(['success'=>false,'message'=>'Incorrect password']);
    }
} else {
    echo json_encode(['success'=>false,'message'=>'Account not found']);
}
$conn->close();
?>