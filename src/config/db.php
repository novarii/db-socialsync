<?php
// Database configuration
$db_host = "localhost";
$db_user = "ebozoglu";
$db_pass = "Xsz-JMcw";
$db_name = "ebozoglu_1";

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>