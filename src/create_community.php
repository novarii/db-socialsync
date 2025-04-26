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
$community_name = $_POST['community_name'];
$creator_user_id = $_SESSION['user_id'];
$community_id = generate_uuid();

// Check if community name already exists
$check_query = "SELECT * FROM COMMUNITIES WHERE community_name = '$community_name'";
$result = $conn->query($check_query);

if ($result->num_rows > 0) {
    echo "A community with this name already exists!";
    echo "<p><a href='communities.php'>Go back</a></p>";
} else {
    // Insert new community
    $insert_query = "INSERT INTO COMMUNITIES (community_id, community_name, creator_user_id, created_at) 
                     VALUES ('$community_id', '$community_name', '$creator_user_id', NOW())";
    
    if ($conn->query($insert_query) === TRUE) {
        // Auto-subscribe creator to the community
        $subscription_query = "INSERT INTO SUBSCRIBES_TO (user_id, community_id, subscribed_at) 
                              VALUES ('$creator_user_id', '$community_id', NOW())";
        $conn->query($subscription_query);
        
        // Redirect to the new community page
        header("Location: community.php?id=" . $community_id);
    } else {
        echo "Error: " . $conn->error;
        echo "<p><a href='communities.php'>Go back</a></p>";
    }
}
?>