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

// Get parameters
$user_id = $_SESSION['user_id'];
$vote = $_GET['vote'];
$vote_id = generate_uuid();

// Determine vote type
$vote_type = ($vote == 'up') ? 1 : -1;

// Check if voting on a post or comment
if (isset($_GET['post_id'])) {
    $post_id = $_GET['post_id'];
    $comment_id = null;
    
    // Check if user already voted on this post
    $check_query = "SELECT * FROM VOTES WHERE user_id = '$user_id' AND post_id = '$post_id'";
    $result = $conn->query($check_query);
    
    if ($result->num_rows > 0) {
        // Update existing vote
        $update_query = "UPDATE VOTES SET vote_type = '$vote_type' WHERE user_id = '$user_id' AND post_id = '$post_id'";
        $conn->query($update_query);
    } else {
        // Insert new vote
        $insert_query = "INSERT INTO VOTES (vote_id, user_id, post_id, vote_type, created_at) 
                         VALUES ('$vote_id', '$user_id', '$post_id', '$vote_type', NOW())";
        $conn->query($insert_query);
    }
} else if (isset($_GET['comment_id'])) {
    $comment_id = $_GET['comment_id'];
    $post_id = null;
    
    // Check if user already voted on this comment
    $check_query = "SELECT * FROM VOTES WHERE user_id = '$user_id' AND comment_id = '$comment_id'";
    $result = $conn->query($check_query);
    
    if ($result->num_rows > 0) {
        // Update existing vote
        $update_query = "UPDATE VOTES SET vote_type = '$vote_type' WHERE user_id = '$user_id' AND comment_id = '$comment_id'";
        $conn->query($update_query);
    } else {
        // Insert new vote
        $insert_query = "INSERT INTO VOTES (vote_id, user_id, comment_id, vote_type, created_at) 
                         VALUES ('$vote_id', '$user_id', '$comment_id', '$vote_type', NOW())";
        $conn->query($insert_query);
    }
}

// Redirect back to previous page
header("Location: " . $_SERVER['HTTP_REFERER']);
?>