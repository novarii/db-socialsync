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
$role = $_SESSION['role'];

// Get search query
$query = isset($_GET['q']) ? $_GET['q'] : '';

// Search results variables
$users_result = null;
$communities_result = null;
$posts_result = null;

if (!empty($query)) {
    // Search users
    $users_query = "SELECT * FROM USERS 
                   WHERE username LIKE '%$query%' OR email LIKE '%$query%'
                   LIMIT 10";
    $users_result = $conn->query($users_query);
    
    // Search communities
    $communities_query = "SELECT * FROM COMMUNITIES 
                         WHERE community_name LIKE '%$query%'
                         LIMIT 10";
    $communities_result = $conn->query($communities_query);
    
    // Search posts
    $posts_query = "SELECT p.*, u.username, c.community_name, c.community_id,
                   (SELECT COUNT(*) FROM VOTES WHERE post_id = p.post_id AND vote_type = 1) as upvotes,
                   (SELECT COUNT(*) FROM VOTES WHERE post_id = p.post_id AND vote_type = -1) as downvotes
                   FROM POSTS p
                   JOIN USERS u ON p.author_user_id = u.user_id
                   JOIN COMMUNITIES c ON p.community_id = c.community_id
                   WHERE p.post_content LIKE '%$query%'
                   ORDER BY p.created_at DESC
                   LIMIT 20";
    $posts_result = $conn->query($posts_query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SocialSync - Search</title>
    <link rel="stylesheet" href="styles/main.css">
</head>
<body>
    <header class="app-header">
        <div class="logo">
            <a href="home.php">SocialSync</a>
        </div>
        
        <div class="search-bar">
            <form action="search.php" method="get">
                <input type="text" name="q" placeholder="Search..." value="<?php echo htmlspecialchars($query); ?>">
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
    
    <main class="search-content">
        <h1>Search Results for "<?php echo htmlspecialchars($query); ?>"</h1>
        
        <?php if (empty($query)): ?>
            <p>Enter a search term to find users, communities, and posts.</p>
        <?php else: ?>
            <!-- Users Results -->
            <section class="search-section">
                <h2>Users</h2>
                <?php
                if ($users_result && $users_result->num_rows > 0) {
                    echo '<div class="users-results">';
                    
                    while($user = $users_result->fetch_assoc()) {
                        $username = $user['username'];
                        $user_role = $user['role'];
                        
                        echo '<div class="user-card">
                                <h3><a href="profile.php?user=' . $username . '">' . $username . '</a></h3>
                                <p>Role: <span class="role-badge ' . $user_role . '">' . $user_role . '</span></p>
                              </div>';
                    }
                    
                    echo '</div>';
                } else {
                    echo '<p>No users found matching "' . htmlspecialchars($query) . '".</p>';
                }
                ?>
            </section>
            
            <!-- Communities Results -->
            <section class="search-section">
                <h2>Communities</h2>
                <?php
                if ($communities_result && $communities_result->num_rows > 0) {
                    echo '<div class="communities-results">';
                    
                    while($community = $communities_result->fetch_assoc()) {
                        $community_id = $community['community_id'];
                        $community_name = $community['community_name'];
                        
                        echo '<div class="community-card">
                                <h3><a href="community.php?id=' . $community_id . '">' . $community_name . '</a></h3>
                              </div>';
                    }
                    
                    echo '</div>';
                } else {
                    echo '<p>No communities found matching "' . htmlspecialchars($query) . '".</p>';
                }
                ?>
            </section>
            
            <!-- Posts Results -->
            <section class="search-section">
                <h2>Posts</h2>
                <?php
                if ($posts_result && $posts_result->num_rows > 0) {
                    while($post = $posts_result->fetch_assoc()) {
                        $post_id = $post['post_id'];
                        $author = $post['username'];
                        $content = $post['post_content'];
                        $community = $post['community_name'];
                        $community_id = $post['community_id'];
                        $post_date = $post['created_at'];
                        $upvotes = $post['upvotes'];
                        $downvotes = $post['downvotes'];
                        
                        // Highlight the search term in the content
                        $highlighted_content = preg_replace('/(' . preg_quote($query, '/') . ')/i', '<span class="highlight">$1</span>', $content);
                        
                        echo '<div class="post">
                                <div class="post-header">
                                    <p>Posted by <a href="profile.php?user=' . $author . '">' . $author . '</a> in <a href="community.php?id=' . $community_id . '">' . $community . '</a></p>
                                    <p>' . $post_date . '</p>
                                </div>
                                
                                <div class="post-content">
                                    <p>' . $highlighted_content . '</p>
                                </div>
                                
                                <div class="post-actions">
                                    <a href="vote.php?post_id=' . $post_id . '&vote=up">Upvote (' . $upvotes . ')</a>
                                    <a href="vote.php?post_id=' . $post_id . '&vote=down">Downvote (' . $downvotes . ')</a>
                                    <a href="post.php?id=' . $post_id . '">View Post</a>
                                </div>
                              </div>';
                    }
                } else {
                    echo '<p>No posts found matching "' . htmlspecialchars($query) . '".</p>';
                }
                ?>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>