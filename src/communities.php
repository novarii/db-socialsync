<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

include_once 'config/db.php';

// Get current user info
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Fetch all communities
$query = "SELECT c.*, 
          (SELECT COUNT(*) FROM SUBSCRIBES_TO WHERE community_id = c.community_id) as member_count,
          (SELECT COUNT(*) FROM SUBSCRIBES_TO WHERE community_id = c.community_id AND user_id = '$user_id') as is_subscribed
          FROM COMMUNITIES c
          ORDER BY member_count DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SocialSync - Communities</title>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/communities.css">
</head>
<body>
    <header class="app-header">
        <div class="logo">
            <a href="home.php">SocialSync</a>
        </div>
        
        <div class="search-bar">
            <form action="search.php" method="get">
                <input type="text" name="q" placeholder="Search...">
                <button type="submit">Search</button>
            </form>
        </div>
        
        <nav class="main-nav">
            <a href="home.php" class="nav-item">Home</a>
            <a href="profile.php" class="nav-item">Profile</a>
            <a href="communities.php" class="nav-item active">Communities</a>
            <?php if ($role == "administrator"): ?>
                <a href="admin.php" class="nav-item">Admin Panel</a>
            <?php endif; ?>
            <a href="logout.php" class="nav-item">Logout</a>
        </nav>
    </header>
    
    <main class="communities-content">
        <h1>Communities</h1>
        
        <!-- Community Creation Form -->
        <div class="create-community">
            <h2>Create a New Community</h2>
            <form action="create_community.php" method="post">
                <div class="form-group">
                    <label for="community_name">Community Name:</label>
                    <input type="text" id="community_name" name="community_name" required>
                </div>
                <button type="submit" class="btn-primary">Create Community</button>
            </form>
        </div>
        
        <!-- Communities List -->
        <div class="communities-list">
            <h2>Available Communities</h2>
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $community_id = $row['community_id'];
                    $community_name = $row['community_name'];
                    $member_count = $row['member_count'];
                    $is_subscribed = $row['is_subscribed'];
                    $created_at = $row['created_at'];
                    
                    echo '<div class="community-card">
                            <h3><a href="community.php?id=' . $community_id . '">' . $community_name . '</a></h3>
                            <p>' . $member_count . ' members</p>
                            <p>Created: ' . $created_at . '</p>';
                            
                    if ($is_subscribed > 0) {
                        echo '<a href="subscribe.php?community_id=' . $community_id . '&action=unsubscribe" class="btn-secondary">Unsubscribe</a>';
                    } else {
                        echo '<a href="subscribe.php?community_id=' . $community_id . '&action=subscribe" class="btn-primary">Subscribe</a>';
                    }
                    
                    echo '</div>';
                }
            } else {
                echo '<p>No communities available yet. Be the first to create one!</p>';
            }
            ?>
        </div>
    </main>
</body>
</html>