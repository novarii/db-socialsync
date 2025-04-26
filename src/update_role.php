<?php
session_start();

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'administrator') {
    header("Location: home.php");
    exit();
}

include_once 'config/db.php';

// Get form data
$user_id = $_POST['user_id'];
$role = $_POST['role'];

// Update user role
$query = "UPDATE USERS SET role = '$role' WHERE user_id = '$user_id'";

if ($conn->query($query) === TRUE) {
    header("Location: admin.php");
} else {
    echo "Error updating role: " . $conn->error;
    echo "<p><a href='admin.php'>Go back</a></p>";
}
?>