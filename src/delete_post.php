<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

include_once 'config/db.php';

// Get parameters
$post_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Check if user has permission to delete this post
if ($role == 'regular_user') {
    // Regular users can only delete their own posts
    $check_query = "SELECT * FROM POSTS WHERE post_id = '$post_id' AND author_user_id = '$user_id'";
    $result = $conn->query($check_query);
    
    if ($result->num_rows == 0) {
        echo "You don't have permission to delete this post!";
        echo "<p><a href='home.php'>Go home</a></p>";
        exit();
    }
}

// Delete the post (moderators and administrators can delete any post)
$delete_query = "DELETE FROM POSTS WHERE post_id = '$post_id'";

if ($conn->query($delete_query) === TRUE) {
    // Redirect back to the previous page or home
    if (isset($_SERVER['HTTP_REFERER'])) {
        header("Location: " . $_SERVER['HTTP_REFERER']);
    } else {
        header("Location: home.php");
    }
} else {
    echo "Error deleting post: " . $conn->error;
    echo "<p><a href='home.php'>Go home</a></p>";
}
?>