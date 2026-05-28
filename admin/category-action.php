<?php
include '../api/db.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'add') {
    $name   = trim($_POST['category_name']);
    $status = trim($_POST['status'] ?? 'active');
    $stmt = $conn->prepare("INSERT INTO categories (category_name, status) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $status);
    $stmt->execute();
    header("Location: categories.php?msg=added");

} elseif ($action === 'edit') {
    $id     = intval($_POST['category_id']);
    $name   = trim($_POST['category_name']);
    $status = trim($_POST['status'] ?? 'active');
    $stmt = $conn->prepare("UPDATE categories SET category_name=?, status=? WHERE category_id=?");
    $stmt->bind_param("ssi", $name, $status, $id);
    $stmt->execute();
    header("Location: categories.php?msg=updated");

} elseif ($action === 'delete') {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM categories WHERE category_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: categories.php?msg=deleted");

} else {
    header("Location: categories.php");
}

$conn->close();
exit;
?>