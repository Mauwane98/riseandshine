<?php
session_start();
require_once 'helpers/auth.php';
require_login();
// Optionally, add a role check here to ensure only a 'superadmin' can access this page
// require_role('superadmin'); 
require_once 'helpers/log_activity.php';

$users_file = 'data/users.csv';

function getUsers() {
    global $users_file;
    $users = [];
    if (file_exists($users_file) && ($handle = fopen($users_file, "r")) !== FALSE) {
        fgetcsv($handle); // Skip header
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if(count($data) >= 2) {
                $users[$data[0]] = ['username' => $data[0], 'password_hash' => $data[1]];
            }
        }
        fclose($handle);
    }
    return $users;
}

// Handle adding a new user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_username'])) {
    $new_username = trim($_POST['new_username']);
    $new_password = $_POST['new_password'];

    if (!empty($new_username) && !empty($new_password)) {
        $users = getUsers();
        if (isset($users[$new_username])) {
            $_SESSION['error'] = 1;
            $_SESSION['message'] = 'Username already exists.';
        } else {
            $users[$new_username] = [
                'username' => $new_username,
                'password_hash' => password_hash($new_password, PASSWORD_DEFAULT)
            ];
            
            $handle = fopen($users_file, 'w');
            fputcsv($handle, ['username', 'password_hash']);
            foreach ($users as $user) {
                fputcsv($handle, $user);
            }
            fclose($handle);

            log_activity($_SESSION['username'] . " created new admin user: " . $new_username);
            $_SESSION['message'] = 'Admin user created successfully!';
        }
    } else {
        $_SESSION['error'] = 1;
        $_SESSION['message'] = 'Username and password cannot be empty.';
    }
    header('Location: users.php');
    exit;
}

// Handle deletion
if (isset($_GET['delete'])) {
    $user_to_delete = $_GET['delete'];
    // Prevent deleting the currently logged-in user
    if ($user_to_delete === $_SESSION['username']) {
        $_SESSION['error'] = 1;
        $_SESSION['message'] = 'You cannot delete your own account.';
    } else {
        $users = getUsers();
        if (isset($users[$user_to_delete])) {
            unset($users[$user_to_delete]);
            
            $handle = fopen($users_file, 'w');
            fputcsv($handle, ['username', 'password_hash']);
            foreach ($users as $user) {
                fputcsv($handle, $user);
            }
            fclose($handle);

            log_activity($_SESSION['username'] . " deleted admin user: " . $user_to_delete);
            $_SESSION['message'] = 'Admin user deleted successfully!';
        }
    }
    header('Location: users.php');
    exit;
}

$allUsers = getUsers();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admin Users - Admin</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <aside class="admin-sidebar">
            <h3>Admin Panel</h3>
            <nav>
                <ul>
                    <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="members.php"><i class="fas fa-users"></i> Members</a></li>
                    <li><a href="events.php"><i class="fas fa-calendar-alt"></i> Events</a></li>
                    <li><a href="gallery.php"><i class="fas fa-images"></i> Gallery</a></li>
                    <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                    <li class="active"><a href="users.php"><i class="fas fa-user-shield"></i> Admin Users</a></li>
                    <li><a href="../index.php" target="_blank"><i class="fas fa-globe"></i> View Public Site</a></li>
                </ul>
            </nav>
        </aside>
        <main class="admin-content">
            <header class="admin-header">
                <h2>Manage Admin Users</h2>
                <div class="admin-user">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </header>

            <?php if (isset($_SESSION['message'])): ?>
            <div class="message <?php echo isset($_SESSION['error']) ? 'error' : 'success'; ?>"><?php echo $_SESSION['message']; unset($_SESSION['message']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <section class="admin-form-container">
                <h3>Add New Admin User</h3>
                <form action="users.php" method="post" class="styled-form">
                    <div class="form-group">
                        <label for="new_username">Username</label>
                        <input type="text" id="new_username" name="new_username" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">Password</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn">Create User</button>
                    </div>
                </form>
            </section>

            <section class="admin-table-container">
                <h3>Existing Users</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($allUsers)): ?>
                            <tr><td colspan="2">No admin users found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($allUsers as $user): ?>
                            <tr>
                                <td><?php echo $user['username']; ?></td>
                                <td class="action-links">
                                    <?php if ($user['username'] !== $_SESSION['username']): ?>
                                    <a href="?delete=<?php echo $user['username']; ?>" onclick="return confirm('Are you sure you want to delete this user? This cannot be undone.');" class="action-delete" title="Delete"><i class="fas fa-trash"></i></a>
                                    <?php else: ?>
                                    (Your Account)
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>
