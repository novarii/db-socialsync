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

// Get post info
$post_query = "SELECT p.*, u.username, c.community_name, c.community_id,
              (SELECT COUNT(*) FROM VOTES WHERE post_id = p.post_id AND vote_type = 1) as upvotes,
              (SELECT COUNT(*) FROM VOTES WHERE post_id = p.post_id AND vote_type = -1) as downvotes
              FROM POSTS p
              JOIN USERS u ON p.author_user_id = u.user_id
              JOIN COMMUNITIES c ON p.community_id = c.community_id
              WHERE p.post_id = '$post_id'";
$post_result = $conn->query($post_query);

if ($post_result->num_rows == 0) {
    echo "Post not found!";
    echo "<p><a href='home.php'>Go home</a></p>";
    exit();
}

$post = $post_result->fetch_assoc();
$post_content = $post['post_content'];
$author = $post['username'];
$community_name = $post['community_name'];
$community_id = $post['community_id'];
$created_at = $post['created_at'];
$upvotes = $post['upvotes'];
$downvotes = $post['downvotes'];

// Get comments for this post
$comments_query = "SELECT c.*, u.username,
                  (SELECT COUNT(*) FROM VOTES WHERE comment_id = c.comment_id AND vote_type = 1) as upvotes,
                  (SELECT COUNT(*) FROM VOTES WHERE comment_id = c.comment_id AND vote_type = -1) as downvotes
                  FROM COMMENTS c
                  JOIN USERS u ON c.author_user_id = u.user_id
                  WHERE c.post_id = '$post_id'
                  ORDER BY c.created_at ASC";
$comments_result = $conn->query($comments_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SocialSync - Post</title>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/post.css">
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
            <a href="communities.php" class="nav-item">Communities</a>
            <?php if ($role == "administrator"): ?>
                <a href="admin.php" class="nav-item">Admin Panel</a>
            <?php endif; ?>
            <a href="logout.php" class="nav-item">Logout</a>
        </nav>
    </header>
    
    <main class="post-content">
        <div class="post-container">
            <div class="post-header">
                <p class="post-community">Posted in <a href="community.php?id=<?php echo $community_id; ?>"><?php echo $community_name; ?></a></p>
                <div class="post-header-2">
                    <h1 class="post-title">Post by <?php echo $author; ?></h1>
                    <p class="post-meta">Posted on <?php echo $created_at; ?></p>
                </div>
            </div>
            
            <div class="post-body">
                <p><?php echo $post_content; ?></p>
            </div>
            
            <div class="post-actions">
                <a href="vote.php?post_id=<?php echo $post_id; ?>&vote=up">Upvote (<?php echo $upvotes; ?>)</a>
                <a href="vote.php?post_id=<?php echo $post_id; ?>&vote=down">Downvote (<?php echo $downvotes; ?>)</a>
                
                <?php if ($role == "moderator" || $role == "administrator"): ?>
                    <a href="delete_post.php?id=<?php echo $post_id; ?>" onclick="return confirm('Are you sure?')">Delete Post</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="comments-section">
            <h2>Comments (<?php echo $comments_result->num_rows; ?>)</h2>
            
            <?php
            if ($comments_result->num_rows > 0) {
                while($comment = $comments_result->fetch_assoc()) {
                    $comment_id = $comment['comment_id'];
                    $comment_author = $comment['username'];
                    $comment_content = $comment['comment_content'];
                    $comment_date = $comment['created_at'];
                    $comment_upvotes = $comment['upvotes'];
                    $comment_downvotes = $comment['downvotes'];
                    
                    echo '<div class="comment">
                            <div class="comment-header">
                                <h3>' . $comment_author . '</h3>
                                <p>' . $comment_date . '</p>
                            </div>
                            
                            <div class="comment-body">
                                <p>' . $comment_content . '</p>
                            </div>
                            
                            <div class="comment-actions">
                                <a href="vote.php?comment_id=' . $comment_id . '&vote=up">Upvote (' . $comment_upvotes . ')</a>
                                <a href="vote.php?comment_id=' . $comment_id . '&vote=down">Downvote (' . $comment_downvotes . ')</a>';
                                
                                // Show moderation options for moderators and admins
                                if ($role == "moderator" || $role == "administrator") {
                                    echo '<a href="delete_comment.php?id=' . $comment_id . '" onclick="return confirm(\'Are you sure?\')">Delete</a>';
                                }
                                
                            echo '</div>
                          </div>';
                }
            } else {
                echo '<p>No comments yet. Be the first to comment!</p>';
            }
            ?>
        </div>
        
        <!-- Comment Form -->
        <div class="comment-form">
            <h2>Add a Comment</h2>
            <form action="add_comment.php" method="post">
                <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                <textarea name="comment_content" placeholder="Write your comment here..." required></textarea>
                <button type="submit" class="btn-primary">Comment</button>
            </form>
        </div>
        
    </main>
</body>
</html>