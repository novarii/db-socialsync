<?php
session_start();

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'administrator') {
    header("Location: home.php");
    exit();
}

include_once 'config/db.php';

// Get users
$users_query = "SELECT * FROM USERS";
$users_result = $conn->query($users_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SocialSync - Admin Panel</title>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/admin.css">
</head>
<body>
    <header class="app-header">
        <div class="logo">
            <a href="home.php">SocialSync</a>
        </div>
        <nav class="main-nav">
            <a href="home.php" class="nav-item">Home</a>
            <a href="admin.php" class="nav-item active">Admin Panel</a>
            <a href="logout.php" class="nav-item">Logout</a>
        </nav>
    </header>
    
    <main class="admin-content">
        <h1>Administration Dashboard</h1>
        
        <section class="admin-section">
            <h2>User Management</h2>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = $users_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $user['user_id']; ?></td>
                        <td><?php echo $user['username']; ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td>
                            <form action="update_role.php" method="post">
                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                <select name="role">
                                    <option value="regular_user" <?php if($user['role'] == 'regular_user') echo 'selected'; ?>>Regular User</option>
                                    <option value="moderator" <?php if($user['role'] == 'moderator') echo 'selected'; ?>>Moderator</option>
                                    <option value="administrator" <?php if($user['role'] == 'administrator') echo 'selected'; ?>>Administrator</option>
                                </select>
                                <button type="submit">Update</button>
                            </form>
                        </td>
                        <td><?php echo $user['created_at']; ?></td>
                        <td>
                            <a href="delete_user.php?id=<?php echo $user['user_id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>