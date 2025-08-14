<?php
// admin/index.php (Fully Updated & Responsive)

session_start();
require_once __DIR__ . '/helpers/auth.php';
check_permission(['Admin', 'Moderator']);

// --- Helper function to read CSV files ---
function read_csv_data($filePath, $limit = 0) {
    if (!file_exists($filePath)) return [];
    $data = [];
    if (($handle = fopen($filePath, 'r')) !== false) {
        $header = fgetcsv($handle);
        if ($header === false) { fclose($handle); return []; }
        while (($row = fgetcsv($handle)) !== false) {
             if (is_array($row) && count($row) === count($header)) {
                $data[] = array_combine($header, $row);
            }
        }
        fclose($handle);
    }
    
    $data = array_reverse($data);

    if ($limit > 0) {
        return array_slice($data, 0, $limit);
    }
    return $data;
}

// --- Paths to data files ---
$data_dir = __DIR__ . '/data/';
$registrations_file = $data_dir . 'registrations.csv';
$messages_file = $data_dir . 'messages.csv';
$log_file = $data_dir . 'activity_log.csv';

// --- Load data for dashboard widgets ---
$all_registrations = read_csv_data($registrations_file);
$all_messages = read_csv_data($messages_file);
$recent_activity = read_csv_data($log_file, 5);

$total_members = count($all_registrations);
$pending_members = count(array_filter($all_registrations, fn($reg) => isset($reg['Status']) && strtolower($reg['Status']) === 'pending'));
$total_messages = count($all_messages);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Admin Panel</title>
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
        .status-pending, .status-approved, .status-suspended, .status-declined { padding: 5px 10px; border-radius: 20px; color: #fff; font-weight: 600; font-size: 0.8rem; text-transform: uppercase; text-align: center; display: inline-block; }
        .status-pending { background-color: var(--status-pending); }
        .status-approved { background-color: var(--status-approved); }
        .status-suspended { background-color: var(--status-suspended); }
        .status-declined { background-color: var(--status-declined); }
        .dashboard-grid { display: grid; grid-template-columns: 1fr; gap: 30px; }
        @media (min-width: 1200px) { .dashboard-grid { grid-template-columns: 2fr 1fr; } }
        .widget-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; margin-bottom: 30px; }
        .widget { background: #fff; padding: 25px; border-radius: 8px; box-shadow: var(--shadow); }
        .widget h4 { margin: 0 0 10px; font-size: 1.1rem; color: #555; }
        .widget .stat { font-size: 2.5rem; font-weight: 700; margin: 0; color: var(--primary-dark); }
        .widget .stat.highlight { color: var(--accent); }
        .widget .widget-link { display: inline-block; margin-top: 15px; font-weight: 600; color: var(--accent); text-decoration: none; }
        .activity-item { display: flex; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--border-color); flex-wrap: wrap; }
        .activity-item:last-child { border-bottom: none; }
        .activity-item .action { font-weight: 600; flex-basis: 150px; }
        .activity-item .details { color: #555; flex-grow: 1; }
        .activity-item .timestamp { font-size: 0.85rem; color: #777; flex-basis: 150px; text-align: right; }
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
                    <li><a href="index.php" class="active">Dashboard</a></li>
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
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header class="main-header">
                <button class="mobile-menu-button" id="mobile-menu-btn">&#9776;</button>
                <h1>Dashboard</h1>
            </header>
            
            <section class="content">
                <div class="widget-container">
                    <div class="widget">
                        <h4>Total Members</h4>
                        <p class="stat"><?= $total_members ?></p>
                        <a href="members.php" class="widget-link">View All</a>
                    </div>
                    <div class="widget">
                        <h4>Pending Approvals</h4>
                        <p class="stat highlight"><?= $pending_members ?></p>
                        <a href="members.php?filter=pending" class="widget-link">Review Now</a>
                    </div>
                    <div class="widget">
                        <h4>Total Messages</h4>
                        <p class="stat"><?= $total_messages ?></p>
                        <a href="messages.php" class="widget-link">Read Messages</a>
                    </div>
                </div>

                <div class="dashboard-grid">
                    <div class="card">
                        <h2>Recent Registrations</h2>
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($all_registrations)): ?>
                                        <?php foreach (array_slice($all_registrations, 0, 5) as $reg): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($reg['Full Name'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($reg['Email'] ?? '') ?></td>
                                                <td><?= htmlspecialchars(date('Y-m-d', strtotime($reg['Timestamp'] ?? ''))) ?></td>
                                                <td><span class="status-<?= strtolower(htmlspecialchars($reg['Status'] ?? '')) ?>"><?= htmlspecialchars($reg['Status'] ?? '') ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="4" style="text-align:center;">No registrations yet.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card">
                        <h2>Recent Activity</h2>
                        <?php if (!empty($recent_activity)): ?>
                            <?php foreach ($recent_activity as $log): ?>
                                <div class="activity-item">
                                    <div class="action"><?= htmlspecialchars($log['Action']) ?></div>
                                    <div class="details"><?= htmlspecialchars($log['User']) ?></div>
                                    <div class="timestamp"><?= htmlspecialchars(date('M d, g:i a', strtotime($log['Timestamp']))) ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="text-align:center;">No activity recorded yet.</p>
                        <?php endif; ?>
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
