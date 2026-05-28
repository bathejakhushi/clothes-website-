<?php
include '../api/db.php';

$order_id = intval($_POST['order_id'] ?? 0);
$status   = trim($_POST['status'] ?? '');
$redirect = $_POST['redirect'] ?? 'adminorders.php';

$allowed = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];

if ($order_id <= 0 || !in_array($status, $allowed)) {
    echo "<script>alert('Invalid request.'); window.history.back();</script>";
    exit;
}

$stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
$stmt->bind_param("si", $status, $order_id);

if ($stmt->execute()) {
    header("Location: " . $redirect);
} else {
    echo "<script>alert('Update failed: " . $conn->error . "'); window.history.back();</script>";
}

$stmt->close();
$conn->close();
?>