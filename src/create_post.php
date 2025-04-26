<?php
session_start();
include_once 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

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
$post_content = $_POST['post_content'];
$community_id = $_POST['community_id'];
$author_user_id = $_SESSION['user_id'];
$post_id = generate_uuid();

// Insert post
$query = "INSERT INTO POSTS (post_id, author_user_id, community_id, post_content, created_at) 
          VALUES ('$post_id', '$author_user_id', '$community_id', '$post_content', NOW())";

if ($conn->query($query) === TRUE) {
    header("Location: home.php");
} else {
    echo "Error: " . $conn->error;
    echo "<p><a href='home.php'>Go back</a></p>";
}
?>