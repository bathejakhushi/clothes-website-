<?php
session_start();
include 'db.php';
header('Content-Type: application/json');

$email    = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

$stmt = $conn->prepare("SELECT admin_id, name, email, password FROM admins WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid admin credentials.']);
    exit;
}

$admin = $result->fetch_assoc();

// Check plain text password (your DB has plain text: admin123)
if ($password === $admin['password'] || password_verify($password, $admin['password'])) {
    $_SESSION['admin_id']    = $admin['admin_id'];
    $_SESSION['admin_name']  = $admin['name'];
    $_SESSION['admin_email'] = $admin['email'];
    echo json_encode(['success' => true, 'message' => 'Admin login successful!', 'name' => $admin['name']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid admin credentials.']);
}

$stmt->close();
$conn->close();
?>