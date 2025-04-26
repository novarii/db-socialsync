<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

include_once 'config/db.php';

// Get parameters
$comment_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Check if user has permission to delete this comment
if ($role == 'regular_user') {
    // Regular users can only delete their own comments
    $check_query = "SELECT * FROM COMMENTS WHERE comment_id = '$comment_id' AND author_user_id = '$user_id'";
    $result = $conn->query($check_query);
    
    if ($result->num_rows == 0) {
        echo "You don't have permission to delete this comment!";
        echo "<p><a href='home.php'>Go home</a></p>";
        exit();
    }
}

// Get the post_id to redirect back to the post page
$post_query = "SELECT post_id FROM COMMENTS WHERE comment_id = '$comment_id'";
$post_result = $conn->query($post_query);
$post = $post_result->fetch_assoc();
$post_id = $post['post_id'];

// Delete the comment (moderators and administrators can delete any comment)
$delete_query = "DELETE FROM COMMENTS WHERE comment_id = '$comment_id'";

if ($conn->query($delete_query) === TRUE) {
    // Redirect back to the post page
    header("Location: post.php?id=" . $post_id);
} else {
    echo "Error deleting comment: " . $conn->error;
    echo "<p><a href='post.php?id=" . $post_id . "'>Go back</a></p>";
}
?>