<?php
session_start();
include_once 'config/db.php';

// Function to generate a UUID v4
function generate_uuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

// Get form data
$username = $_POST['username'];
$email = $_POST['email'];
$password = $_POST['password'];
$role = 'regular_user'; // Default role for new users

// Generate a UUID for the user
$user_id = generate_uuid();

// Check if username or email already exists
$check_query = "SELECT * FROM USERS WHERE username = '$username' OR email = '$email'";
$result = $conn->query($check_query);

if ($result->num_rows > 0) {
    // User already exists
    echo "Username or email already exists!";
    echo "<p><a href='signup.html'>Go back</a></p>";
} else {
    // Add password column to users table if not using separate table
    $insert_query = "INSERT INTO USERS (user_id, username, email, password, role, created_at) 
                     VALUES ('$user_id', '$username', '$email', '$password', '$role', NOW())";
    
    if ($conn->query($insert_query) === TRUE) {
        echo "Registration successful! Please <a href='index.html'>log in</a>.";
    } else {
        echo "Error: " . $conn->error;
        echo "<p><a href='signup.html'>Go back</a></p>";
    }
}
?>