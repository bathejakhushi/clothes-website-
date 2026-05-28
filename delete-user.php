<?php
include '../api/db.php';

if (!isset($_GET['id'])) {
    echo "<script>alert('Invalid request.'); window.location.href='users.php';</script>";
    exit;
}

$user_id = intval($_GET['id']);

// Delete user's orders' payments first
$conn->query("DELETE p FROM payments p 
              INNER JOIN orders o ON p.order_id = o.order_id 
              WHERE o.user_id = $user_id");

// Delete user's order items
$conn->query("DELETE oi FROM order_items oi 
              INNER JOIN orders o ON oi.order_id = o.order_id 
              WHERE o.user_id = $user_id");

// Delete user's orders
$conn->query("DELETE FROM orders WHERE user_id = $user_id");

// Delete user's cart items
$conn->query("DELETE FROM cart_items WHERE user_id = $user_id");

// Finally delete the user
if ($conn->query("DELETE FROM users WHERE user_id = $user_id") === TRUE) {
    echo "<script>alert('User #$user_id deleted successfully!'); window.location.href='users.php';</script>";
} else {
    echo "<script>alert('Error deleting user: " . $conn->error . "'); window.location.href='users.php';</script>";
}
$conn->close();
?>