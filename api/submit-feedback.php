<?php
include 'db.php';   // use your existing db.php connection
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data safely
    $name    = $conn->real_escape_string($_POST['name']);
    $email   = $conn->real_escape_string($_POST['email']);
    $rating  = intval($_POST['rating']);
    $message = $conn->real_escape_string($_POST['message']);

    // If user is logged in, use their ID; otherwise NULL
    $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 'NULL';

    // Insert into feedback table
    $sql = "INSERT INTO feedback (user_id, name, email, rating, message, created_at) 
            VALUES ($user_id, '$name', '$email', '$rating', '$message', NOW())";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Thank you for your feedback!'); window.location.href='../feedback.html';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
$conn->close();
?>