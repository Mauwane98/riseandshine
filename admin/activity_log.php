<?php
// admin/activity_log.php (Fully Updated & Responsive)

session_start();
require_once __DIR__ . '/helpers/auth.php';
check_permission(['Admin']); // Only Admins can view the activity log

// --- Configuration ---
define('LOGS_PER_PAGE', 15);
$log_file = __DIR__ . '/data/activity_log.csv';

function get_logs($filePath) {
    if (!file_exists($filePath)) return [];
    $logs = [];
    if (($handle = fopen($filePath, 'r')) !== false) {
        fgetcsv($handle); // Skip header
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === 4) {
                $logs[] = ['timestamp' => $row[0], 'user' => $row[1], 'action' => $row[2], 'details' => $row[3]];
            }
        }
        fclose($handle);
    }
    return array_reverse($logs); // Show most recent first
}

// --- Load all logs and apply pagination ---
$all_logs = get_logs($log_file);
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$total_logs = count($all_logs);
$total_pages = ceil($total_logs / LOGS_PER_PAGE);
$offset = ($current_page - 1) * LOGS_PER_PAGE;
$paginated_logs = array_slice($all_logs, $offset, LOGS_PER_PAGE);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log | Admin Panel</title>
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
        .pagination-controls {
            display: flex; justify-content: space-between; align-items: center;
            margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border-color);
        }
        .pagination-controls a {
            padding: 8px 16px; text-decoration: none; background-color: var(--accent);
            color: var(--primary-dark); border-radius: 5px; font-weight: 700;
        }
        .pagination-controls .disabled {
            background-color: #ccc;
            cursor: not-allowed;
            pointer-events: none;
        }
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
                        <li><a href="activity_log.php" class="active">Activity Log</a></li>
                    <?php endif; ?>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header class="main-header">
                <button class="mobile-menu-button" id="mobile-menu-btn">&#9776;</button>
                <h1>Admin Activity Log</h1>
            </header>
            
            <section class="content">
                <div class="card">
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($paginated_logs)): ?>
                                    <?php foreach ($paginated_logs as $log): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($log['timestamp']) ?></td>
                                            <td><?= htmlspecialchars($log['user']) ?></td>
                                            <td><?= htmlspecialchars($log['action']) ?></td>
                                            <td><?= htmlspecialchars($log['details']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" style="text-align:center;">No activity has been logged yet.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-controls">
                        <div>
                            <a href="?page=<?= $current_page - 1 ?>" class="<?= $current_page <= 1 ? 'disabled' : '' ?>">Previous</a>
                        </div>
                        <div>
                            Page <?= $current_page ?> of <?= $total_pages > 0 ? $total_pages : 1 ?>
                        </div>
                        <div>
                            <a href="?page=<?= $current_page + 1 ?>" class="<?= $current_page >= $total_pages ? 'disabled' : '' ?>">Next</a>
                        </div>
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
