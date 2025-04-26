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

// Function to calculate time ago
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

// Fetch posts for the feed
$query = "SELECT p.*, u.username, u.role as user_role, c.community_name, c.community_id,
          (SELECT COUNT(*) FROM VOTES WHERE post_id = p.post_id AND vote_type = 1) as upvotes,
          (SELECT COUNT(*) FROM VOTES WHERE post_id = p.post_id AND vote_type = -1) as downvotes,
          (SELECT COUNT(*) FROM COMMENTS WHERE post_id = p.post_id) as comment_count
          FROM POSTS p
          JOIN USERS u ON p.author_user_id = u.user_id
          JOIN COMMUNITIES c ON p.community_id = c.community_id
          ORDER BY p.created_at DESC
          LIMIT 20";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SocialSync - Home</title>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
            <a href="home.php" class="nav-item active">Home</a>
            <a href="profile.php" class="nav-item">Profile</a>
            <a href="communities.php" class="nav-item">Communities</a>
            <?php if ($role == "administrator"): ?>
                <a href="admin.php" class="nav-item">Admin Panel</a>
            <?php endif; ?>
            <a href="logout.php" class="nav-item">Logout</a>
        </nav>
    </header>
    
    <div class="home-layout">
        <main class="main-content">
            <!-- Post Creation -->
            <div class="create-post">
                <form action="create_post.php" method="post">
                    <div style="display: flex; align-items: center;">
                        <div class="user-avatar">
                            <?php echo substr($username, 0, 1); ?>
                        </div>
                        <textarea name="post_content" placeholder="Create a post..."></textarea>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <select name="community_id" required>
                            <option value="">Select a community</option>
                            <?php
                            // Fetch available communities
                            $communities_query = "SELECT * FROM COMMUNITIES";
                            $communities_result = $conn->query($communities_query);
                            
                            while($community = $communities_result->fetch_assoc()) {
                                echo '<option value="' . $community['community_id'] . '">' . $community['community_name'] . '</option>';
                            }
                            ?>
                        </select>
                        <button type="submit">Post</button>
                    </div>
                </form>
            </div>
            
            <!-- Feed -->
            <div class="posts-feed">
                <?php
                if ($result->num_rows > 0) {
                    // Output data of each row
                    while($row = $result->fetch_assoc()) {
                        $post_id = $row['post_id'];
                        $post_content = $row['post_content'];
                        $author = $row['username'];
                        $user_role = $row['user_role'];
                        $community = $row['community_name'];
                        $community_id = $row['community_id'];
                        $created_at = $row['created_at'];
                        $upvotes = $row['upvotes'];
                        $downvotes = $row['downvotes'];
                        $comment_count = $row['comment_count'];
                        
                        // Calculate time ago
                        $time_ago = time_elapsed_string($created_at);
                        
                        // Get the first letter of username for avatar
                        $avatar_letter = substr($author, 0, 1);
                        
                        echo '<div class="post">
                                <div class="post-header">
                                    <div style="display: flex; align-items: center;">
                                        <div class="user-avatar">' . $avatar_letter . '</div>
                                        <div>
                                            <h3>' . $author . ' ' . ($user_role == 'administrator' ? '<span class="verified-badge"><i class="fas fa-check-circle"></i></span>' : '') . '</h3>
                                            <p>' . $community . ' · ' . $time_ago . '</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="post-content">
                                    <h3>' . substr($post_content, 0, 50) . (strlen($post_content) > 50 ? '...' : '') . '</h3>
                                    <p>' . $post_content . '</p>
                                </div>
                                
                                <div class="post-actions">
                                    <a href="vote.php?post_id=' . $post_id . '&vote=up" class="upvote-btn">
                                        <i class="fas fa-arrow-up"></i> ' . $upvotes . '
                                    </a>
                                    <a href="vote.php?post_id=' . $post_id . '&vote=down" class="downvote-btn">
                                        <i class="fas fa-arrow-down"></i> ' . $downvotes . '
                                    </a>
                                    <a href="post.php?id=' . $post_id . '">
                                        <i class="fas fa-comment"></i> ' . $comment_count . '
                                    </a>
                                    <a href="#" class="save-btn" onclick="return false;">
                                        <i class="far fa-bookmark"></i> Save
                                    </a>';
                                    
                                    // Show moderation options for moderators and admins
                                    if ($role == "moderator" || $role == "administrator") {
                                        echo '<a href="delete_post.php?id=' . $post_id . '" onclick="return confirm(\'Are you sure?\')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>';
                                    }
                                    
                                echo '</div>
                              </div>';
                    }
                } else {
                    echo '<div class="empty-state">
                            <i class="fas fa-comment-slash fa-3x"></i>
                            <p>No posts yet! Be the first to create a post.</p>
                          </div>';
                }
                ?>
            </div>
        </main>
        
        <aside class="sidebar">
            <div class="user-info">
                <h3>Welcome, <?php echo $username; ?></h3>
                <p>Role: <span class="role-badge <?php echo $role; ?>"><?php echo $role; ?></span></p>
            </div>
            
            <div class="communities-list">
                <h3>Communities</h3>
                <ul>
                    <?php
                    $communities_query = "SELECT c.* 
                                         FROM COMMUNITIES c
                                         JOIN SUBSCRIBES_TO s ON c.community_id = s.community_id
                                         WHERE s.user_id = '$user_id'
                                         ORDER BY c.community_name ASC
                                         LIMIT 10";
                    $communities_result = $conn->query($communities_query);
                    
                    if ($communities_result->num_rows > 0) {
                        while($community = $communities_result->fetch_assoc()) {
                            echo '<li><a href="community.php?id=' . $community['community_id'] . '">' . $community['community_name'] . '</a></li>';
                        }
                    } else {
                        echo '<li>You haven\'t joined any communities yet.</li>';
                    }
                    ?>
                </ul>
                <a href="communities.php" class="btn-primary" style="display: block; text-align: center; margin-top: 12px; color: white;">Browse Communities</a>
            </div>
        </aside>
    </div>
</body>
</html>