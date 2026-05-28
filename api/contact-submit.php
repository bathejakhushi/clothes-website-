<?php
session_start();
include 'db.php';
header('Content-Type: application/json');

$name    = trim($_POST['name']    ?? '');
$email   = trim($_POST['email']   ?? '');
$message = trim($_POST['message'] ?? '');

if (!$name || !$email || !$message) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

// Get user_id from session if logged in
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
$uid_val = $user_id ? $user_id : 'NULL';

$name    = $conn->real_escape_string($name);
$email   = $conn->real_escape_string($email);
$message = $conn->real_escape_string($message);

if ($conn->query("INSERT INTO contact_us (user_id, name, email, message) VALUES ($uid_val, '$name', '$email', '$message')")) {
    echo json_encode(['success' => true, 'message' => 'Message sent successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
}
$conn->close();
?>