<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

include_once 'config/db.php';

// Get current user info
$current_user_id = $_SESSION['user_id'];
$current_role = $_SESSION['role'];

// Determine which user's profile to show
if (isset($_GET['user'])) {
    // Show the requested user's profile
    $username = $_GET['user'];
    $user_query = "SELECT * FROM USERS WHERE username = '$username'";
} else {
    // Show the current user's profile
    $user_query = "SELECT * FROM USERS WHERE user_id = '$current_user_id'";
}

$user_result = $conn->query($user_query);

if ($user_result->num_rows == 0) {
    echo "User not found!";
    echo "<p><a href='home.php'>Go home</a></p>";
    exit();
}

$user = $user_result->fetch_assoc();
$user_id = $user['user_id'];
$username = $user['username'];
$email = $user['email'];
$role = $user['role'];
$created_at = $user['created_at'];

// Check if viewing own profile
$is_own_profile = ($user_id == $current_user_id);

// Get user's posts
$posts_query = "SELECT p.*, c.community_name, c.community_id,
               (SELECT COUNT(*) FROM VOTES WHERE post_id = p.post_id AND vote_type = 1) as upvotes,
               (SELECT COUNT(*) FROM VOTES WHERE post_id = p.post_id AND vote_type = -1) as downvotes,
               (SELECT COUNT(*) FROM COMMENTS WHERE post_id = p.post_id) as comment_count
               FROM POSTS p
               JOIN COMMUNITIES c ON p.community_id = c.community_id
               WHERE p.author_user_id = '$user_id'
               ORDER BY p.created_at DESC";
$posts_result = $conn->query($posts_query);

// Get user's comments
$comments_query = "SELECT c.*, p.post_id, u.username as post_author
                  FROM COMMENTS c
                  JOIN POSTS p ON c.post_id = p.post_id
                  JOIN USERS u ON p.author_user_id = u.user_id
                  WHERE c.author_user_id = '$user_id'
                  ORDER BY c.created_at DESC";
$comments_result = $conn->query($comments_query);

// Get user's communities
$communities_query = "SELECT c.* 
                     FROM COMMUNITIES c
                     JOIN SUBSCRIBES_TO s ON c.community_id = s.community_id
                     WHERE s.user_id = '$user_id'
                     ORDER BY s.subscribed_at DESC";
$communities_result = $conn->query($communities_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SocialSync - <?php echo $username; ?>'s Profile</title>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/profile.css">
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
            <a href="profile.php" class="nav-item active">Profile</a>
            <a href="communities.php" class="nav-item">Communities</a>
            <?php if ($current_role == "administrator"): ?>
                <a href="admin.php" class="nav-item">Admin Panel</a>
            <?php endif; ?>
            <a href="logout.php" class="nav-item">Logout</a>
        </nav>
    </header>
    
    <main class="profile-content">
        <div class="profile-header">
            <h1><?php echo $username; ?>'s Profile</h1>
            
            <div class="profile-meta">
                <?php if ($is_own_profile || $current_role == "administrator"): ?>
                    <p>Email: <?php echo $email; ?></p>
                <?php endif; ?>
                
                <p>Role: <span class="role-badge <?php echo $role; ?>"><?php echo $role; ?></span></p>
                <p>Member since: <?php echo $created_at; ?></p>
            </div>
            
            <?php if ($current_role == "administrator" && !$is_own_profile): ?>
                <div class="admin-actions">
                    <form action="update_role.php" method="post">
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                        <select name="role">
                            <option value="regular_user" <?php if($role == 'regular_user') echo 'selected'; ?>>Regular User</option>
                            <option value="moderator" <?php if($role == 'moderator') echo 'selected'; ?>>Moderator</option>
                            <option value="administrator" <?php if($role == 'administrator') echo 'selected'; ?>>Administrator</option>
                        </select>
                        <button type="submit" class="btn-secondary">Update Role</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="profile-tabs">
            <div class="tab-navigation">
                <button class="tab-button active" data-tab="posts">Posts</button>
                <button class="tab-button" data-tab="comments">Comments</button>
                <button class="tab-button" data-tab="communities">Communities</button>
            </div>
            
            <!-- Posts Tab -->
            <div class="tab-content active" id="posts-tab">
                <h2>Posts</h2>
                
                <?php
                if ($posts_result->num_rows > 0) {
                    while($post = $posts_result->fetch_assoc()) {
                        $post_id = $post['post_id'];
                        $content = $post['post_content'];
                        $community = $post['community_name'];
                        $community_id = $post['community_id'];
                        $post_date = $post['created_at'];
                        $upvotes = $post['upvotes'];
                        $downvotes = $post['downvotes'];
                        $comment_count = $post['comment_count'];
                        
                        echo '<div class="post">
                                <div class="post-header">
                                    <p>Posted in <a href="community.php?id=' . $community_id . '">' . $community . '</a></p>
                                    <p>' . $post_date . '</p>
                                </div>
                                
                                <div class="post-content">
                                    <p>' . $content . '</p>
                                </div>
                                
                                <div class="post-actions">
                                    <a href="vote.php?post_id=' . $post_id . '&vote=up">Upvote (' . $upvotes . ')</a>
                                    <a href="vote.php?post_id=' . $post_id . '&vote=down">Downvote (' . $downvotes . ')</a>
                                    <a href="post.php?id=' . $post_id . '">Comments (' . $comment_count . ')</a>';
                                    
                                    // Show delete option for own posts or if admin/moderator
                                    if ($is_own_profile || $current_role == "moderator" || $current_role == "administrator") {
                                        echo '<a href="delete_post.php?id=' . $post_id . '" onclick="return confirm(\'Are you sure?\')">Delete</a>';
                                    }
                                    
                                echo '</div>
                              </div>';
                    }
                } else {
                    echo '<p>No posts yet.</p>';
                }
                ?>
            </div>
            
            <!-- Comments Tab -->
            <div class="tab-content" id="comments-tab">
                <h2>Comments</h2>
                
                <?php
                if ($comments_result->num_rows > 0) {
                    while($comment = $comments_result->fetch_assoc()) {
                        $comment_id = $comment['comment_id'];
                        $post_id = $comment['post_id'];
                        $post_author = $comment['post_author'];
                        $content = $comment['comment_content'];
                        $comment_date = $comment['created_at'];
                        
                        echo '<div class="comment">
                                <div class="comment-header">
                                    <p>Commented on <a href="post.php?id=' . $post_id . '">post by ' . $post_author . '</a></p>
                                    <p>' . $comment_date . '</p>
                                </div>
                                
                                <div class="comment-content">
                                    <p>' . $content . '</p>
                                </div>
                                
                                <div class="comment-actions">';
                                    
                                    // Show delete option for own comments or if admin/moderator
                                    if ($is_own_profile || $current_role == "moderator" || $current_role == "administrator") {
                                        echo '<a href="delete_comment.php?id=' . $comment_id . '" onclick="return confirm(\'Are you sure?\')">Delete</a>';
                                    }
                                    
                                echo '</div>
                              </div>';
                    }
                } else {
                    echo '<p>No comments yet.</p>';
                }
                ?>
            </div>
            
            <!-- Communities Tab -->
            <div class="tab-content" id="communities-tab">
                <h2>Communities</h2>
                
                <?php
                if ($communities_result->num_rows > 0) {
                    echo '<div class="communities-grid">';
                    
                    while($community = $communities_result->fetch_assoc()) {
                        $community_id = $community['community_id'];
                        $community_name = $community['community_name'];
                        $created_at = $community['created_at'];
                        
                        echo '<div class="community-card">
                                <h3><a href="community.php?id=' . $community_id . '">' . $community_name . '</a></h3>
                                <p>Created: ' . $created_at . '</p>
                              </div>';
                    }
                    
                    echo '</div>';
                } else {
                    echo '<p>Not subscribed to any communities yet.</p>';
                    echo '<p><a href="communities.php" class="btn-primary">Browse Communities</a></p>';
                }
                ?>
            </div>
        </div>
    </main>
    
    <script>
        // Simple tab switching functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons and tabs
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    // Show the corresponding tab content
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId + '-tab').classList.add('active');
                });
            });
        });
    </script>
</body>
</html>