<?php
// admin/users.php (Fully Updated & Responsive)

session_start();
require_once __DIR__ . '/helpers/auth.php';
check_permission(['Admin']); // Only Admins can manage users

require_once __DIR__ . '/helpers/log_activity.php';

$users_file = __DIR__ . '/data/users.csv';
$message = '';
$error = '';
$user_to_edit = null;

// --- Helper functions for user management ---
function get_users($filePath) {
    if (!file_exists($filePath)) return [];
    $users = [];
    if (($handle = fopen($filePath, 'r')) !== false) {
        fgetcsv($handle); // Skip header
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= 3) {
                $users[] = ['username' => $row[0], 'password_hash' => $row[1], 'role' => $row[2]];
            }
        }
        fclose($handle);
    }
    return $users;
}

function save_users($filePath, $users) {
    if (($handle = fopen($filePath, 'w')) !== false) {
        fputcsv($handle, ['Username', 'Password Hash', 'Role']);
        foreach ($users as $user) {
            fputcsv($handle, [$user['username'], $user['password_hash'], $user['role']]);
        }
        fclose($handle);
    }
}

// --- Handle Form Submissions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $all_users = get_users($users_file);

    if ($action === 'save_user') {
        $username = trim($_POST['username']);
        $role = $_POST['role'];
        $password = $_POST['password'];
        $original_username = $_POST['original_username'] ?? $username;

        if (empty($username) || empty($role)) {
            $error = "Username and role are required.";
        } else {
            $is_editing = false;
            foreach ($all_users as &$user) {
                if ($user['username'] === $original_username) {
                    $is_editing = true;
                    $user['username'] = $username;
                    $user['role'] = $role;
                    if (!empty($password)) {
                        if (strlen($password) < 8) {
                            $error = "Password must be at least 8 characters long.";
                        } else {
                           $user['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
                        }
                    }
                    break;
                }
            }
            unset($user);

            if (!$is_editing) {
                 if (empty($password) || strlen($password) < 8) {
                    $error = "A new user must have a password of at least 8 characters.";
                } else {
                    $all_users[] = ['username' => $username, 'password_hash' => password_hash($password, PASSWORD_DEFAULT), 'role' => $role];
                }
            }
            
            if (empty($error)) {
                save_users($users_file, $all_users);
                $message = "User saved successfully!";
                log_action('User Saved', "Saved user: '$username'");
            }
        }
    }

    if ($action === 'delete_user') {
        $username_to_delete = $_POST['username'];
        if ($username_to_delete === $_SESSION['admin_logged_in_user']) {
            $error = "You cannot delete your own account.";
        } else {
            $users_to_keep = array_filter($all_users, fn($user) => $user['username'] !== $username_to_delete);
            save_users($users_file, $users_to_keep);
            $message = "User deleted successfully!";
            log_action('User Deleted', "Deleted user: '$username_to_delete'");
        }
    }
    
    $all_users = get_users($users_file);
} else {
    $all_users = get_users($users_file);
}

if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['username'])) {
    foreach ($all_users as $user) {
        if ($user['username'] === $_GET['username']) {
            $user_to_edit = $user;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management | Admin Panel</title>
    <style>
        :root {
            --primary-dark: #0d1321; --secondary-dark: #1d2d44; --accent: #fca311;
            --text-light: #e5e5e5; --bg-main: #f4f7fc; --text-dark: #333;
            --border-color: #e1e1e1; --shadow: 0 2px 8px rgba(0,0,0,0.1);
            --status-pending: #e67e22; --status-approved: #2ecc71;
            --status-suspended: #f39c12; --status-declined: #e74c3c;
        }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; background-color: var(--bg-main); color: var(--text-dark); font-size: 16px; }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background-color: var(--primary-dark); color: var(--text-light); padding: 20px; display: flex; flex-direction: column; transition: transform 0.3s ease-in-out; }
        .sidebar h3 { color: var(--accent); text-align: center; margin-bottom: 30px; font-size: 1.5rem; }
        .sidebar nav ul { list-style: none; padding: 0; margin: 0; }
        .sidebar nav a { display: block; color: var(--text-light); text-decoration: none; padding: 12px 15px; border-radius: 6px; margin-bottom: 8px; font-weight: 600; transition: background-color 0.3s, color 0.3s; }
        .sidebar nav a:hover, .sidebar nav a.active { background-color: var(--accent); color: var(--primary-dark); }
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-x: hidden; }
        .main-header { background: #fff; padding: 15px 30px; border-bottom: 1px solid var(--border-color); box-shadow: var(--shadow); display: flex; align-items: center; gap: 20px; }
        .main-header h1 { margin: 0; font-size: 1.8rem; flex-grow: 1; }
        .content { padding: 30px; flex: 1; }
        .mobile-menu-button { display: none; background: none; border: none; font-size: 2rem; color: var(--primary-dark); cursor: pointer; }
        .card { background: #fff; padding: 25px; border-radius: 8px; box-shadow: var(--shadow); }
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 600px; }
        table th, table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--border-color); }
        table thead th { background-color: #f9f9f9; font-weight: 700; }
        .form-group { display: flex; flex-direction: column; margin-bottom: 15px; }
        .form-group label { font-weight: 600; margin-bottom: 5px; }
        .form-group input, .form-group select {
            width: 100%; padding: 10px; border: 1px solid var(--border-color);
            border-radius: 5px; font-family: inherit; font-size: 1rem;
        }
        .btn-submit { padding: 10px 20px; background-color: var(--accent); color: var(--primary-dark); border: none; border-radius: 5px; font-weight: 700; cursor: pointer; margin-top: 10px; }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 5px; color: #fff; font-weight: 600; }
        .message.success { background-color: var(--status-approved); }
        .message.error { background-color: var(--status-declined); }
        .action-buttons a, .action-buttons button { text-decoration: none; display: inline-block; padding: 5px 10px; border-radius: 5px; color: #fff; font-weight: 600; font-size: 0.9rem; margin-right: 5px; border: none; cursor: pointer; }
        .edit-btn { background-color: #3498db; }
        .delete-btn { background-color: var(--status-declined); }
        @media (max-width: 992px) {
            .sidebar { position: fixed; top: 0; left: 0; height: 100%; z-index: 1000; transform: translateX(-100%); }
            .sidebar.is-open { transform: translateX(0); }
            .mobile-menu-button { display: block; }
            .main-content { width: 100%; }
            .content { padding: 20px; }
            .main-header h1 { font-size: 1.5rem; }
        }
        @media (max-width: 576px) { .content { padding: 15px; } .card { padding: 15px; } }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <aside class="sidebar">
            <h3>Rise & Shine Admin</h3>
            <nav>
                <ul>
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="members.php">Members</a></li>
                    <li><a href="events.php">Events</a></li>
                    <li><a href="gallery.php">Gallery</a></li>
                    <li><a href="messages.php">Messages</a></li>
                    <li><a href="notifications.php">Notifications</a></li>
                    <li><a href="email_templates.php">Email Templates</a></li>
                    <?php if (isset($_SESSION['admin_user_role']) && $_SESSION['admin_user_role'] === 'Admin'): ?>
                        <li><a href="reports.php">Reports</a></li>
                        <li><a href="users.php" class="active">Users</a></li>
                        <li><a href="activity_log.php">Activity Log</a></li>
                    <?php endif; ?>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header class="main-header">
                <button class="mobile-menu-button" id="mobile-menu-btn">&#9776;</button>
                <h1>User Management</h1>
            </header>
            
            <section class="content">
                <div class="card" style="margin-bottom: 30px;">
                    <h2><?= $user_to_edit ? 'Edit User' : 'Add New User' ?></h2>
                     <?php if ($message): ?><div class="message success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
                     <?php if ($error): ?><div class="message error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

                    <form action="users.php" method="POST">
                        <input type="hidden" name="action" value="save_user">
                        <?php if ($user_to_edit): ?>
                            <input type="hidden" name="original_username" value="<?= htmlspecialchars($user_to_edit['username']) ?>">
                        <?php endif; ?>
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" required value="<?= htmlspecialchars($user_to_edit['username'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" <?= !$user_to_edit ? 'required' : '' ?> placeholder="<?= $user_to_edit ? 'Leave blank to keep current password' : '' ?>">
                        </div>
                         <div class="form-group">
                            <label for="role">Role</label>
                            <select id="role" name="role" required>
                                <option value="Admin" <?= ($user_to_edit['role'] ?? '') === 'Admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="Moderator" <?= ($user_to_edit['role'] ?? '') === 'Moderator' ? 'selected' : '' ?>>Moderator</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-submit"><?= $user_to_edit ? 'Update User' : 'Add User' ?></button>
                    </form>
                </div>

                <div class="card">
                    <h2>Existing Users</h2>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($all_users as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['username']) ?></td>
                                        <td><?= htmlspecialchars($user['role']) ?></td>
                                        <td class="action-buttons">
                                            <a href="users.php?action=edit&username=<?= urlencode($user['username']) ?>" class="edit-btn">Edit</a>
                                            <?php if ($user['username'] !== $_SESSION['admin_logged_in_user']): ?>
                                            <form action="users.php" method="POST" onsubmit="return confirm('Are you sure?');" style="display:inline;">
                                                <input type="hidden" name="action" value="delete_user">
                                                <input type="hidden" name="username" value="<?= htmlspecialchars($user['username']) ?>">
                                                <button type="submit" class="delete-btn">Delete</button>
                                            </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <script>
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('is-open');
        });
    </script>
</body>
</html>
