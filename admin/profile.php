<?php
// admin/profile.php (Fully Updated & Responsive)

session_start();
require_once __DIR__ . '/helpers/auth.php';
check_permission(['Admin', 'Moderator']); // All logged-in users can manage their own profile
require_once __DIR__ . '/helpers/log_activity.php';

$users_file = __DIR__ . '/data/users.csv';
$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $current_username = $_SESSION['admin_logged_in_user'];

    $all_users = get_users($users_file);
    $current_user_data = null;
    $user_index = -1;

    foreach ($all_users as $index => $user) {
        if ($user['username'] === $current_username) {
            $current_user_data = $user;
            $user_index = $index;
            break;
        }
    }

    // 1. Verify current password
    if (!$current_user_data || !password_verify($current_password, $current_user_data['password_hash'])) {
        $error = "Your current password is not correct.";
    }
    // 2. Check if new passwords match
    elseif ($new_password !== $confirm_password) {
        $error = "The new passwords do not match.";
    }
    // 3. Check for minimum password length
    elseif (strlen($new_password) < 8) {
        $error = "The new password must be at least 8 characters long.";
    } else {
        // All checks passed, update the password
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $all_users[$user_index]['password_hash'] = $new_hash;

        if (save_users($users_file, $all_users)) {
            $message = "Password updated successfully!";
            log_action('Profile Updated', "User '$current_username' updated their password.");
        } else {
            $error = "Error: Could not write to the users file. Please check permissions.";
        }
    }
}

// Helper functions (needed for this page to work with the password change logic)
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
        return true;
    }
    return false;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Profile | Admin Panel</title>
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
        .form-group { display: flex; flex-direction: column; margin-bottom: 15px; }
        .form-group label { font-weight: 600; margin-bottom: 5px; }
        .form-group input {
            width: 100%; padding: 10px; border: 1px solid var(--border-color);
            border-radius: 5px; font-family: inherit; font-size: 1rem;
            max-width: 400px; box-sizing: border-box;
        }
        .btn-submit {
            padding: 10px 20px; background-color: var(--accent); color: var(--primary-dark);
            border: none; border-radius: 5px; font-weight: 700; cursor: pointer;
            margin-top: 10px;
        }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 5px; color: #fff; font-weight: 600; }
        .message.success { background-color: var(--status-approved); }
        .message.error { background-color: var(--status-declined); }
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
                        <li><a href="users.php">Users</a></li>
                        <li><a href="activity_log.php">Activity Log</a></li>
                    <?php endif; ?>
                    <li><a href="profile.php" class="active">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header class="main-header">
                <button class="mobile-menu-button" id="mobile-menu-btn">&#9776;</button>
                <h1>Profile Management</h1>
            </header>
            
            <section class="content">
                <div class="card">
                    <h2>Change Your Password</h2>

                    <?php if ($message): ?>
                        <div class="message success"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="message error"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form action="profile.php" method="POST">
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password (min. 8 characters)</label>
                            <input type="password" id="new_password" name="new_password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn-submit">Update Password</button>
                    </form>
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
