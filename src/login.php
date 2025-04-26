<?php
session_start();
include_once 'config/db.php';

// Get form data
$email = $_POST['email'];
$password = $_POST['password'];

// Get user from database
$query = "SELECT * FROM USERS WHERE email = '$email' AND password = '$password'";
$result = $conn->query($query);

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
    
    // Create session
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    
    // Redirect to home page
    header("Location: home.php");
} else {
    echo "Invalid email or password";
    echo "<p><a href='index.html'>Try again</a></p>";
}
?>