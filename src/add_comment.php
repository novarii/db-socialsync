<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

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
$post_id = $_POST['post_id'];
$comment_content = $_POST['comment_content'];
$author_user_id = $_SESSION['user_id'];
$comment_id = generate_uuid();

// Insert comment
$query = "INSERT INTO COMMENTS (comment_id, author_user_id, post_id, comment_content, created_at) 
          VALUES ('$comment_id', '$author_user_id', '$post_id', '$comment_content', NOW())";

if ($conn->query($query) === TRUE) {
    header("Location: post.php?id=" . $post_id);
} else {
    echo "Error: " . $conn->error;
    echo "<p><a href='post.php?id=" . $post_id . "'>Go back</a></p>";
}
?>