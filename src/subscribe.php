<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

include_once 'config/db.php';

// Get parameters
$community_id = $_GET['community_id'];
$action = $_GET['action'];
$user_id = $_SESSION['user_id'];

if ($action == 'subscribe') {
    // Check if already subscribed
    $check_query = "SELECT * FROM SUBSCRIBES_TO WHERE user_id = '$user_id' AND community_id = '$community_id'";
    $result = $conn->query($check_query);
    
    if ($result->num_rows == 0) {
        // Insert subscription
        $insert_query = "INSERT INTO SUBSCRIBES_TO (user_id, community_id, subscribed_at) 
                         VALUES ('$user_id', '$community_id', NOW())";
        $conn->query($insert_query);
    }
} else if ($action == 'unsubscribe') {
    // Delete subscription
    $delete_query = "DELETE FROM SUBSCRIBES_TO WHERE user_id = '$user_id' AND community_id = '$community_id'";
    $conn->query($delete_query);
}

// Redirect back to previous page
header("Location: " . $_SERVER['HTTP_REFERER']);
?>