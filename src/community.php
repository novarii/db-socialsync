<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

include_once 'config/db.php';

// Get parameters
$community_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Get community info
$community_query = "SELECT c.*, u.username as creator_name,
                   (SELECT COUNT(*) FROM SUBSCRIBES_TO WHERE community_id = c.community_id) as member_count,
                   (SELECT COUNT(*) FROM SUBSCRIBES_TO WHERE community_id = c.community_id AND user_id = '$user_id') as is_subscribed
                   FROM COMMUNITIES c
                   LEFT JOIN USERS u ON c.creator_user_id = u.user_id
                   WHERE c.community_id = '$community_id'";
$community_result = $conn->query($community_query);

if ($community_result->num_rows == 0) {
    echo "Community not found!";
    echo "<p><a href='home.php'>Go home</a></p>";
    exit();
}

$community = $community_result->fetch_assoc();
$community_name = $community['community_name'];
$creator_name = $community['creator_name'];
$member_count = $community['member_count'];
$is_subscribed = $community['is_subscribed'];
$created_at = $community['created_at'];

// Get community posts
$posts_query = "SELECT p.*, u.username,
               (SELECT COUNT(*) FROM VOTES WHERE post_id = p.post_id AND vote_type = 1) as upvotes,
               (SELECT COUNT(*) FROM VOTES WHERE post_id = p.post_id AND vote_type = -1) as downvotes,
               (SELECT COUNT(*) FROM COMMENTS WHERE post_id = p.post_id) as comment_count
               FROM POSTS p
               JOIN USERS u ON p.author_user_id = u.user_id
               WHERE p.community_id = '$community_id'
               ORDER BY p.created_at DESC";
$posts_result = $conn->query($posts_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SocialSync - <?php echo $community_name; ?></title>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/community.css">
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
    
    <main class="community-content">
        <div class="community-header">
            <h1><?php echo $community_name; ?></h1>
            <div class="community-meta">
                <p>Created by: <?php echo $creator_name; ?></p>
                <p>Members: <?php echo $member_count; ?></p>
                <p>Since: <?php echo $created_at; ?></p>
            </div>
            
            <?php if ($is_subscribed > 0): ?>
                <a href="subscribe.php?community_id=<?php echo $community_id; ?>&action=unsubscribe" class="btn-secondary">Unsubscribe</a>
            <?php else: ?>
                <a href="subscribe.php?community_id=<?php echo $community_id; ?>&action=subscribe" class="btn-primary">Subscribe</a>
            <?php endif; ?>
        </div>
        
        <?php if ($is_subscribed > 0): ?>
        <!-- Post Creation Form -->
        <div class="create-post">
            <h2>Create a Post</h2>
            <form action="create_post.php" method="post">
                <input type="hidden" name="community_id" value="<?php echo $community_id; ?>">
                <textarea name="post_content" placeholder="Write your post here..." required></textarea>
                <button type="submit" class="btn-primary">Post</button>
            </form>
        </div>
        <?php endif; ?>
        
        <!-- Community Posts -->
        <div class="community-posts">
            <h2>Posts</h2>
            <?php
            if ($posts_result->num_rows > 0) {
                while($post = $posts_result->fetch_assoc()) {
                    $post_id = $post['post_id'];
                    $author = $post['username'];
                    $content = $post['post_content'];
                    $post_date = $post['created_at'];
                    $upvotes = $post['upvotes'];
                    $downvotes = $post['downvotes'];
                    $comment_count = $post['comment_count'];
                    
                    echo '<div class="post">
                            <div class="post-header">
                                <h3>Posted by ' . $author . '</h3>
                                <p>' . $post_date . '</p>
                            </div>
                            
                            <div class="post-content">
                                <p>' . $content . '</p>
                            </div>
                            
                            <div class="post-actions">
                                <a href="vote.php?post_id=' . $post_id . '&vote=up">Upvote (' . $upvotes . ')</a>
                                <a href="vote.php?post_id=' . $post_id . '&vote=down">Downvote (' . $downvotes . ')</a>
                                <a href="post.php?id=' . $post_id . '">Comments (' . $comment_count . ')</a>';
                                
                                // Show moderation options for moderators and admins
                                if ($role == "moderator" || $role == "administrator") {
                                    echo '<a href="delete_post.php?id=' . $post_id . '" onclick="return confirm(\'Are you sure?\')">Delete</a>';
                                }
                                
                            echo '</div>
                          </div>';
                }
            } else {
                echo '<p>No posts in this community yet. Be the first to post!</p>';
            }
            ?>
        </div>
    </main>
</body>
</html>